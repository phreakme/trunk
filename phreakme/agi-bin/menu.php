#!/usr/bin/php
<?php
set_time_limit(30);
require('phpagi.php');
$agi = new AGI();
$agi->answer();

include('config.php');

$play_menu = false; 

// break out into own ACL/auth file
$callid = $agi->parse_callerid();
$agi->conlog($callid);
//file_put_contents('/root/callid.txt', $callid);
$allowedArr = array('ip', '+12222222222'); // numbers that are allowed to dial in
$bypassAllowed = array('ip');
if (!in_array($callid['username'],$allowedArr)) {
	$agi->hangup();
}
else {
	if (!in_array($callid['username'], $bypassAllowed)) {
	$agi->text2wav("passcode");
	$choice = menu_get_input("5");
	// Default Pass of 11111
	if ($choice != 11111) {
		$agi->hangup();
	}
	}
		
}

menu_main();

$agi->hangup();

function menu_main() {
global $agi, $play_menu;
	$agi->text2wav("freak me main menu");
	
	if ($play_menu) {
		$agi->text2wav("to toggle menu press star");
		$agi->text2wav("press one for setup");
		$agi->text2wav("press two to exploit");
		$agi->text2wav("press three for reporting");
		$agi->text2wav("press four for help");
		$agi->text2wav("press pound to hear this menu again");	
	}
	else {
		$agi->text2wav("menu off, to turn on press star");
	}
	
	get_prompt();	
}

function get_prompt() {
	global $agi, $play_menu;

	$valid=true;
	
	while ($valid) {
	$choice = menu_get_input("1");
	$valid = true;
	if ($choice == 1) {
		menu_setup();
		break;
	}
	elseif ($choice == 2) {
		menu_exploit();
		break;
	}
	elseif ($choice == 3) {
		menu_reporting();
		break;
	}
	elseif ($choice == 4) {
		$agi->text2wav('Help');
		$agi->text2wav('after the beep press pound to hear the main menu');
		$agi->text2wav('First set up your recorded prompt. Then Set up targets, do a dry run, then exploit');
		$agi->text2wav("press star to toggle menus");
		break;
	}
	elseif ($choice == "#") {
		$agi->text2wav("main menu");
		break;
	}
	elseif ($choice == "*") {
		if ($play_menu) {
			$agi->text2wav("menu is now off");
			$play_menu = false;
			break;
		}
		else {
			$agi->text2wav("menu is now on");
			$play_menu = true;
			break;
		}
	}
	else {
		$valid=false;
	}
	}
	menu_main();
}

function menu_setup() {
	global $agi, $play_menu, $recordings_dir, $conf;

	$targets = file('/opt/phreakme/targets.txt');
	
	$agi->text2wav('setup menu');
	$agi->text2wav(count($targets) . ' targets');
	$agi->text2wav('after the beep to return to main menu, press 0');
	if ($play_menu) {
		$agi->text2wav('press 1 to select recording');
		$agi->text2wav('press 2 to list targets');
		$agi->text2wav('press 3 to add target');
		$agi->text2wav('press 4 to remove target');
		$agi->text2wav('press 5 to set caller I D');
		$agi->text2wav('press 9 to clear targets');
	}
	$choice = menu_get_input();

	switch ($choice) {
		// select recording
		case 1:

			// the current recording number is : X
			$current_recording = file_get_contents('/opt/phreakme/recording_selection.txt', NULL, NULL, NULL, 1);
			$agi->text2wav('the current recording is' . $current_recording);			

			$agi->text2wav('select recording');
			
			// loop over recordings, and select one.
			// this should be list recordings
			$recordings_list = scandir($recordings_dir);
			//$agi->text2wav("there are ". count($recordings_list) . " recordings");
			$rec_list = array();
			for ($i = 0; $i < count($recordings_list); $i++) {
			if (!is_dir($recordings_list[$i])) {
				array_push($rec_list, $recordings_list[$i]);
			}
			}
			if (count($rec_list) <= 9) {	
			$agi->text2wav("There are " . count($rec_list) . " Recordings");
			$count = 1;
			foreach ($rec_list as $i) {
				$agi->text2wav("Recording " . $count);
				$agi->exec("Playback", $recordings_dir . basename($i, ".wav"));
				$count++;
			}
			// end list recordings

			// select the recording you wish to use
			if (count($rec_list) <= 9) {
				$agi->text2wav("Select the recording you wish to use");
				$recording_selection = menu_get_input();
				
				file_put_contents("/opt/phreakme/recording_selection.txt", $recording_selection);
				$recording_selection = $recording_selection - 1;
				file_put_contents("/opt/phreakme/recording_selection_name.txt", $rec_list[$recording_selection]);
			}
			}
			else {
				$agi->text2wav("Sorry, there are more than 10 recordings. Please delete or move some recordings to contine");
			}
		break;
		// list targets
		case 2:
			menu_list_targets();	
		break;
		// add target
		case 3:
			$agi->text2wav('Add a target');
			$agi->text2wav('add 10 digit phone number');
			$input = menu_get_input("10");
				
			file_put_contents("/opt/phreakme/targets.txt", $input . "\n",FILE_APPEND);

			
		break;
		// remove target
		case 4:
			$agi->text2wav('remove a target');

			$agi->text2wav('enter the 10 digit number you want to remove');
			$remove_target = menu_get_input("10");
			// remove target X
			$file_array = file('/opt/phreakme/targets.txt');
			unlink('/opt/phreakme/targets.txt');

			$key = array_search(intval($remove_target), $file_array);			
			unset($file_array[$key]);
			$new_fileArray = array_values($file_array);

			foreach ($new_fileArray as $x) {
        			file_put_contents('/opt/phreakme/targets.txt', $x, FILE_APPEND);
			}
			
		break;
		// set callerid
		case 5:
			$agi->text2wav('Caller i d menu');
			//$agi->text2wav('to set C ID press 2');
			//$agi->text2wav('to return press any other key');	
			//$cid_choice = menu_get_input("1");
		
			// list CID	
			//if ($cid_choice == 1) {
				$agi->text2wav('the current caller i d is');
				$cid_play = file_get_contents("/opt/phreakme/callerid.txt");
				$agi->text2wav($cid_play);
			//}
			
			$agi->text2wav('to change C I D press 2');
			$agi->text2wav('to return press any other key');	
			$cid_choice = menu_get_input("1");	
			if ($cid_choice == 2) {
				$agi->text2wav('enter 10 digit u s number');
				$caller_id = menu_get_input("10");
				file_put_contents("/opt/phreakme/callerid.txt", $caller_id);	
			}
			else {
				return;
			}
		// end set callerid
		break;
		// clear targets
		case 9:
			$agi->text2wav('clear targets');

			$agi->text2wav('are you sure... Press 1 for yes, any other key to return');
			$confirm = menu_get_input();

			if ($confirm == 1) {
				$agi->text2wav('clearing targets');
				unlink('/opt/phreakme/targets.txt');
				file_put_contents('/opt/phreakme/targets.txt', "");
				
			} 
			else {
				menu_setup();
			}
		break;
		case 0:
			menu_main();
		break;
	}
	menu_setup();
	break;
}

function menu_list_targets() {
	global $agi;
	$targets = file('/opt/phreakme/targets.txt');
        $agi->text2wav('list targets');
        $agi->text2wav('there are ' . count($targets) . ' targets');
        $count = 1;
        foreach ($targets as $x) {
           $agi->text2wav('target ' . $count . ' ' . $x);
           $count++;
        }
}

function menu_exploit() {
global $agi, $play_menu, $recordings_dir, $config;
$agi->text2wav('exploit menu');

if ($play_menu) {
	$agi->text2wav('To Hear recorded prompt, Press 1');
	$agi->text2wav('To dial a local extension press 2');
	$agi->text2wav('To dial a test number press 3');
	$agi->text2wav('to dial targets press 4');
	$agi->text2wav('to return to main menu, press 0');
}
$choice = menu_get_input();

switch ($choice) {

	case 0:
		menu_main();		
	break;
	case 1:
		$agi->text2wav("after the beep, you will here the prompt");
		$agi->exec("Playback", "beep");
		// actually get selected recording
		$selected_rec = file_get_contents("/opt/phreakme/recording_selection_name.txt");
		$agi->exec("Playback", $config['recordings_dir'] . basename($selected_rec, ".wav"));
	break;
	case 2:
		$agi->text2wav("Enter local extension");
		$agi->text2wav("we will then hangup, and test the prompt against that extension");
		$resp = $agi->get_data('beep', 3000, 20);
		$keys = $resp['result'];
		$agi->text2wav("you entered $keys");

		$agi->text2wav("Hanging Up");
		
		$callfile = "Channel: Local/$keys@context\n";
		$callfile .= "Application: AGI\n";
		$callfile .= "Data: phreakme-outbound.agi\n";
		$callfile .= "CallerID: asterisk\n";
		$callfile .= "MaxRetries: 5\n";
		$callfile .= "RetryTime: 60\n";
		file_put_contents("/opt/phreakme/skel/callfile.call", $callfile);
		
		// sometimes the local dialing doesn't work until re-register
		// was messing around with touch, to try to get +X seconds working, changed maxretries and
		// retry time, and it appears to work
		//exec("touch -c -t 201506210050 /opt/phreakme/skel/callfile.call");
		rename("/opt/phreakme/skel/callfile.call", "/opt/phreakme/skel/outgoing/callfile-new.call");
		//copy("/opt/phreakme/skel/callfile.call", "/opt/phreakme/skel/outgoing/out.call");
		
		$agi->hangup();
	break;
	// test 10 digit number
	case 3:
                $agi->text2wav("Enter a 10 digit you s phone number");
                //$agi->text2wav("we will then hangup, and test against that extension");
                $resp = $agi->get_data('beep', 3000, 20);
                $keys = $resp['result'];
                //$agi->text2wav("you entered $keys");
		
		$agi->text2wav("To specify caller id, press 2");
		$agi->text2wav("press any other key to use global id");
		$cidprompt = $agi->get_data('beep', 3000, 1);

		if ($cidprompt['result'] == 2) {
			$agi->text2wav("enter 10 digit you s phone number");
			$cid_resp = $agi->get_data('beep', 3000, 10);
			$use_callerid = "<" . intval($cid_resp['result']) . ">";
		}
		// global caller id
		else {
			$use_callerid = $config['cid'];
		}
		
                $agi->text2wav("Hanging Up");

		// copy outbound agi to agi with outbound # ext

		$callfile = "Channel: sip/".$config['trunk']."/1".$keys."\n";
		$callfile .= "Application: AGI\n";
		
		$callfile .= "Data: phreakme-outbound.agi\n";
                $callfile .= "CallerID: ".$use_callerid."\n";
		$callfile .= "MaxRetries: 5\n";
		$callfile .= "RetryTime: 30\n";
		$callfile .= "Archive: Yes\n";

		$date_stamp = date("Y-m-d H:i:s");
		$call_file_name = $keys . "-" . $date_stamp . ".call";
                file_put_contents("/opt/phreakme/skel/generated/" . $call_file_name, $callfile);
                rename("/opt/phreakme/skel/generated/" . $call_file_name, "/opt/phreakme/skel/outgoing/" . $call_file_name);

                $agi->hangup();

	break;
	// exploit all targets
	case 4:
		$targets = file_get_contents($config['phreak_root'] . "/targets.txt");
		$target_count = count($targets) + 1;
		$agi->text2wav("Are you sure you wish to exploit ". $target_count ." targets?");
		$agi->text2wav("press 1 for yes, any other key to return");
		$confirm = menu_get_input();

		// if return (return)
		if ($confirm != 1) {
		 break;
		}
		else {	
		// otherwise exploit

			// say processing
			$agi->text2wav("Exploiting. Please await responses");
			
			/*** generate target autodial files ***/
			
			// loop over targets file and get #'s
			$targ = file($config['phreak_root'] . "/targets.txt", FILE_IGNORE_NEW_LINES);
			//var_dump($targ);
 			$date_stamp = date("Y-m-d H:i:s");
                	//$call_file_name = $keys . "-" . $date_stamp . ".call";
		
			$trunk = $config['trunk']; 	// this will come from config
			$cid = $config['cidnum'];		// this will come from config
			$targetsArray = array();
			// loop over targets
			for ($i = 0; $i < count($targ); $i++) {
				
			  // setup template vars for iteration
				$dialed = $targ[$i];		// target number
		 	  // get template contents
			  $template = file($config['phreak_root'] . "/skel/" . "exploit-skel.template");
        	
			  // create filename same as single num test	
			  $filename = $targ[$i] . "_" . $date_stamp . ".call";
			  $agi = 'genoutbound_' . $targ[$i] . "_" . $date_stamp . ".agi";
			  // replace template variables {$trunk} {$num} {$cid}
			  // probably really bad, but in a rush, so deal.
			  $generated = str_replace('{$trunk}', $trunk, $template);
			  $generated = str_replace('{$num}', $dialed, $generated);
			  $generated = str_replace('{$agi}', $agi, $generated);
			  $generated = str_replace('{$cid}', $cid, $generated);
		
			  // copy agi script to known generated
			  // agi-bin/genoutbound-$filename.agi
			  copy('/usr/share/asterisk/agi-bin/phreakme-outbound.agi', '/usr/share/asterisk/agi-bin/' . $agi);
			  chmod('/usr/share/asterisk/agi-bin/' . $agi, 0700);
			  // write file 
      			  file_put_contents($config['phreak_root'] . "/skel/generated/" . $filename, $generated);
			  rename($config['phreak_root'] . "/skel/generated/" . $filename, $config['phreak_root'] . "/skel/outgoing/". $filename);
			}

			/*** end generate target autodial files ***/
			
			$agi->text2wav("hanging up");
                        $agi->hangup();
		}
	break;
}
menu_exploit();

break;
}

function menu_reporting() {
global $agi, $config;
$agi->text2wav("Reporting");
// list targets
$agi->text2wav("There are X Targets");

$responses = file($config['phreak_root'] . '/responses.txt');

$responseCount = count($responses);
$agi->text2wav("There are " . ($responseCount - 1) . " responses");

// start at 1 (ignore header)
for($i = 1; $i < $responseCount; $i++) {
	$agi->text2wav("Response " . $i);
        $response = explode(',', $responses[$i]);
        $dialedArray = explode('_', $response[3]);
        $agi->text2wav("Created: " . $response[0]);
        $agi->text2wav("Dialed: " . $dialedArray[1]);
	$agi->text2wav("Response: " . $dialedArray[2]);
        $agi->text2wav("Placed: " . substr($dialedArray[2], 0, -5));

}

}

function menu_get_input($len=1) {
	global $agi;
	$result = $agi->get_data("beep", 10000, $len);
	return $result['result'];
}

?>
