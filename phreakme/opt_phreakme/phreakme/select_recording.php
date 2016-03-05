<?php

$current_recording = file_get_contents('/opt/phreakme/recording_selection.txt', NULL, NULL, NULL, 1);

echo $current_recording;


?>
