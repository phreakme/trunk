<?php
$recordings_dir = "/opt/phreakme/recordings/";
$steps = file("/opt/phreakme/multistep.txt");
print_r($steps);

// may need to change this to a for, to get an index of steps 
foreach ($steps as $step) {
	$step_array = explode(",", $step);
	print_r($step_array);
	$agi->exec("Playback", $recordings_dir . basename($selected_rec, ".wav"));
	// record response

	// change 4000, 10 and add variable for type, silence or beep
	$response = $agi->get_data("silence/1", 4000, 10);

	file_put_contents("/opt/phreakme/responses.txt", date("Y-m-d H:i:s") . "," . $response['result'] . "," . $response['data'] . ',' . __FILE__ . "\n", FILE_APPEND);
}

?>
