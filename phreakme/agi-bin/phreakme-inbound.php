#!/usr/bin/php
<?php
set_time_limit(30);
require('phpagi.php');
$agi = new AGI();
$agi->answer();

// lookup CID

// make sure is in targets

// if it is, play recording
$agi->text2wav("Goodbye");

// otherwise don't answer

$agi->hangup();
?>
