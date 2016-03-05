<?php
$recordings_dir = "/opt/phreakme/recordings/";
                        // loop over recordings, and select one.
                        $recordings_list = scandir($recordings_dir);
                        echo "there are ". count($recordings_list) . " recordings\n";
                        $rec_list = array();
			for ($i = 0; $i < count($recordings_list); $i++) { 
			if (!is_dir($recordings_list[$i])) {
				array_push($rec_list, $recordings_list[$i]);
                                //echo "Recording " . $i . " Playback", $recordings_dir . basename($recordings_list[$i], ".wav");
                        }
                        }
			$count = 1;
			foreach ($rec_list as $x) {
				echo "Recording: " . $count . " " . $x . "\n";
				$count++;
			}	
                        // play recording
                        //$agi->exec("Playback", $recordings_dir . "1");
print_r($rec_list);
?>
