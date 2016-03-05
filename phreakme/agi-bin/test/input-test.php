#!/usr/bin/php 
<?php 
require('phpagi.php');
$agi = new AGI();
$agi->answer();

$agi->text2wav('Hello. Enter three digit Number');
$num="3";    
$result = $agi->get_data("beep", 10000, 3);
$agi->text2wav("you entered");
$agi->say_digits($result['result']);

$agi->stream_file('privacy-thankyou');
$agi->stream_file('goodbye');
$agi->hangup();
?>
