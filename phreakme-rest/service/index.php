<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

require '/usr/share/asterisk/agi-bin/config.php';
global $config;
/***** 
Setup
*****/

// **** Caller ID ****

// *** list callerid

$app->get('/setup/globalcid', function() {
	global $config;
	
	echo htmlentities(getCallerId());
});


// ** set global CID
$app->post('/setup/globalcid/:callerid', function($callerid) {
	global $config;

	// url decode handled by slim framework

	// should make sure that characters are alpha numeric, strip control chars

	
	// write to file.
	$success = file_put_contents($config['phreak_root'] . "/callerid.txt", $callerid);

	if ($success) {
		echo $callerid;
	}
	else {
		echo "FALSE";
	}
});

// **** Recordings ****

// *** List Recordings
$app->get('/setup/recordings/', function () {
	global $config;
	$current_recording = file_get_contents($config['phreak_root'] . '/recording_selection.txt', NULL, NULL, NULL, 1);
	$recordings_dir = $config['recordings_dir'];	
	$recordings_list = scandir($recordings_dir);
        $rec_list = array();
        for ($i = 0; $i < count($recordings_list); $i++) {
        if (!is_dir($recordings_list[$i])) {
            array_push($rec_list, $recordings_list[$i]);
        }
        }
	//echo "There are " . count($rec_list) . " Recordings" . "\n";
                        $count = 1;
                        foreach ($rec_list as $i) {
                                echo "[$count]" . " " . $recordings_dir . $i . "\n";
                                $count++;
                        }
	$recording_selection = $current_recording - 1;
	echo $current_recording . " " . $rec_list[$recording_selection];	
});

// ** Select Recording **
$app->post('/setup/recordings/:selection', function ($selection) {
	global $config;

	$current_recording = file_get_contents('/opt/phreakme/recording_selection.txt', NULL, NULL, NULL, 1);
	// loop over recordings, and select one.
	$recordings_list = scandir($config['recordings_dir']);
	//$agi->text2wav("there are ". count($recordings_list) . " recordings");
	$rec_list = array();
	for ($i = 0; $i < count($recordings_list); $i++) {
		if (!is_dir($recordings_list[$i])) {
			array_push($rec_list, $recordings_list[$i]);
		}
	}			
			
	//if (count($rec_list) <= 9) {	
		//$agi->text2wav("There are " . count($rec_list) . " Recordings");
		$count = 1;
		foreach ($rec_list as $i) {
			echo "Recording: ". $count . "<br>\n";
			//$agi->text2wav("Recording " . $count);
			//$agi->exec("Playback", $recordings_dir . basename($i, ".wav"));
			echo $config['recordings_dir'] . basename($i, ".wav") . "<br>\n";
			$count++;
		}
	
	//}
	// end list recordings

// this will select the recording
//echo "current recording: " . $selection;

	//if (count($rec_list) <= 9) {
	//	$agi->text2wav("Select the recording you wish to use");
	//	$recording_selection = menu_get_input();
	$recording_selection = $selection;
	echo "<hr>selection : ". $selection . "<hr>";			
		file_put_contents("/opt/phreakme/recording_selection.txt", $recording_selection);
		$recording_selection = $recording_selection - 1;
		file_put_contents("/opt/phreakme/recording_selection_name.txt", $rec_list[$recording_selection]);
	//}


});
// ** end select recording **

// Add Recording (Bonus)
// Delete Recording (Bonus)

// **** End Recordings ****

// **** TARGETS ****
// List Targets
$app->get('/setup/targets/', function () {
	global $config;
	//echo "Targets";
	$file = file($config['phreak_targets']);
	$file_array = array();

	foreach ($file AS $row) {
		array_push($file_array, trim($row, "\n"));
	}
		
	header("Content-Type: application/json");
	echo json_encode($file_array);
	exit;
});


// *** target check
$app->get('/setup/targets/:target', function ($target) {
	global $config;
	$file_array = file($config['phreak_targets']);
	if (in_array($target."\n", $file_array)) {
		echo json_encode(true);
	}
	else {
		echo json_encode(false);
	}
	
});

// *** Add Target
$app->post('/setup/targets/:target', function ($target) {

	global $config;
	$input = intval($target);

	$file_array = file($config['phreak_targets']);	

	// target exist check
	if (!in_array($input."\n", $file_array) && strlen($input) == 10) {
	file_put_contents($config['phreak_targets'], $input . "\n",FILE_APPEND);
		echo "true";
	}
	else {
		echo "false";
	}
});

// *** Remove Target
$app->delete('/setup/targets/:target', function ($target) {

	global $config;
	$input = intval($target);
	$file_array = file($config['phreak_targets']);
	print_r($file_array);
        //unlink('/opt/phreakme/targets.txt');

        $key = in_array($input, $file_array);

	if ($key) {
	
        unset($file_array[array_search($input, $file_array)]);
        $new_fileArray = array_values($file_array);
	
	$handle = fopen($config['phreak_targets'], "w+");
	fclose($handle);       
	
	print_r($new_fileArray); 
	foreach ($new_fileArray as $x) {
          file_put_contents($config['phreak_targets'], $x, FILE_APPEND);
        }
	}
	else {
		echo "Not in array";
	}

});


//**** end targets ****

/**
end setup
**/

/**
exploit
**/
$app->get('/exploit/:target(/:cid)', function ($target,$cid=false) {
	global $config; 

	$target = intval($target);

	// caller id specified?
	if (!$cid) { 
		$use_cid = getCallerId();
	}
	else {
		$use_cid = $cid;
	}

	echo $target . " caller id : " . $use_cid;

	/*** generate target autodial files ***/
		
 	$date_stamp = date("Y-m-d H:i:s");
	$trunk = $config['trunk']; 

	// get template contents
	$template = file($config['phreak_root'] . "/skel/" . "exploit-skel.template");
        	
	// create filename same as single num test	
	$filename = $target . "_" . $date_stamp . ".call";
	
	// Set generated AGI Filename
	$agi = 'genoutbound_' . $target . "_" . $date_stamp . ".agi";
	
	// replace template variables {$trunk} {$num} {$cid}
	// probably really bad, but in a rush, so deal.
	$generated = str_replace('{$trunk}', $trunk, $template);
	$generated = str_replace('{$num}', $target, $generated);
	$generated = str_replace('{$agi}', $agi, $generated);
	$generated = str_replace('{$cid}', $use_cid, $generated);

	// Yea, I know using a count in a for loop is inefficient, but this is DEBUG man!
	echo "\n <hr>\n";
	for ($i = 0; $i < count($generated); $i++) {
	echo htmlentities($generated[$i])."<br/>";
	}	
	
	// ** not sure what this is/was doing, keeping just in case.
	// copy phreakme-outbound.agi script to genoutbound_ for reporting - this is important
	copy('/usr/share/asterisk/agi-bin/phreakme-outbound.agi', '/usr/share/asterisk/agi-bin/' . $agi);
	// don't think the chmod is needed if permissions setup correctly
	chmod('/usr/share/asterisk/agi-bin/' . $agi, 0777);

	// write generated file 
    file_put_contents($config['phreak_root'] . "/skel/generated/" . $filename, $generated);
	sleep(2);
	// move generated call file to outgoing to call
	rename($config['phreak_root'] . "/skel/generated/" . $filename, $config['phreak_root'] . "/skel/outgoing/". $filename);
	
/*
// doubt this is needed anymore
file_put_contents("/opt/phreakme/skel/generated/" . $call_file_name, $callfile);
sleep(5);
rename("/opt/phreakme/skel/generated/" . $call_file_name, "/opt/phreakme/skel/outgoing/" . $call_file_name);
*/
});


/**
end exploit
**/

$app->get('/report/', function () {
global $config;
//$agi->text2wav("Reporting");
// list targets
//$agi->text2wav("There are X Targets");

$responses = file($config['phreak_root'] . '/responses.txt');

$responseCount = count($responses);
//echo "There are " . ($responseCount - 1) . " responses" . "\n";

$outputArr = array();
$outputPush = array();
// start at 1 (ignore header)
for($i = 1; $i < $responseCount; $i++) {
    // echo "Response " . $i . "\n";
    $response = explode(',', $responses[$i]);
   
    $dialedArray = explode('_', $response[3]);
    
    //echo "Created: " . $response[0]. "\n";
    //echo "Dialed: " . $dialedArray[1] . "\n";
    //echo "Input: " . $response[1] . "\n";
    //echo "Input-Resp: " . $response[2] . "\n";
    //echo "Placed: " . substr($dialedArray[2], 0, -5) . "\n";
    $outputArr['Created'] = $response[0];
    $outputArr['Dialed'] = $dialedArray[1];
    $outputArr['Input'] = $response[1];
    $outputArr['InputResponse'] = $response[2];
    $outputArr['placed'] = substr($dialedArray[2], 0, -5);
	
    array_push($outputPush, $outputArr);	
}

echo json_encode($outputPush); 

});

$app->run();


// Grabs caller id from text file. Does not do any sanitization
function getCallerId() {
        global $config;

        $default_cid = file_get_contents($config['phreak_root'] . "/callerid.txt");
	return $default_cid;
}

?>
