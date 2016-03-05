<?php
// Setup Stuff For Template. Template Below
$callerid = shell_exec("curl http://localhost/service/setup/globalcid");
$globalcid = $callerid;

$recordings = shell_exec("curl http://localhost/service/setup/recordings");

$targets = shell_exec("curl http://localhost/service/setup/targets");
	
// Begin Template
?>
<script>


$(document).ready(function() {
// set caller id
	$("#cidSubmit").click(function(event) {
               post =  "/service/setup/globalcid/" + $("#globalcid").val();
	       $.post(post, function(data) {$(".result").html(data); });
               event.preventDefault();
	});

// select recording
        // Recording Select
        $("#recSubmit").click(function(event) {

        post = "/service/setup/recordings/" + $('input[name=rec]:checked').val()
;
        $.post(post, function(data) {$(".result").html(data);});
        event.preventDefault();
        });


// Target Add
	$("#targetAdd").click(function(event) {
	if ($("#target").val()) {
	post = "/service/setup/targets/" + $("#target").val();
	$.post(post, function(data) {$(".result").html(data);});
if ($(".result")) {
	location.reload();
}	
else {
alert("Something went horribly wrong");
}
	}	
	event.preventDefault();	
	});

});

function targetDel(target) {
        $.ajax({
	url: '/service/setup/targets/' + target,
	type: 'delete',
	success: function(response) { location.reload(); }
	});

}
</script>
<h1>Setup</h1>
<h3>Asterisk Setup will be here</h3>
<h2>CallerID</h2>
Global Caller ID: <?=$globalcid;?><br>
Set Global Caller ID:<br/>
<form method="post">
<input type="text" id="globalcid" name="" value="<?=$globalcid;?>"/><br/>
<input type="submit" id="cidSubmit"/>
</form>

<hr>
<h2>Recordings</h2>
<form>
<?php
// JSON HACK, remove when recordings is in JSON format
$recArray = explode("\n", $recordings);
$recordings_json = "{";
foreach($recArray as $row) {
        $rowArr = explode(" ",$row);
        #print_r($rowArr);
        $recordings_json .= '"'.$rowArr[0].'":"'.$rowArr[1].'",';
}
$recordings_json = rtrim($recordings_json, ',');
$recordings_json .= "}";
// End JSON HACK
$recordings = json_decode($recordings_json);
$count = 0;

$OptionArray = Array();
foreach ($recordings AS $key=>$value) {
        array_push($OptionArray, $key .":". $value."");
//echo '<option value="' . $key . '">' . $value ."</option>\n";
$count++;
}
$count = count($OptionArray);
$selected = explode(":",$OptionArray[$count-1]);
$selected = $selected[0];
$selOffset = $selected - 1;
for ($i = 0; $i < $count - 1; $i++) {
        if ($i == $selOffset) { $sel=true; }
	else { $sel=false; }
        $value = explode(":", $OptionArray[$i]);
        $key = str_replace("[", "", $value[0]);
        $key = str_replace("]", "", $key);
        echo '<input type="radio" name="rec" value="' . $key . '" ' .($sel ? 'checked' : '') . '>' . $value[1] ."<br/>\n";
}
?>
<input id="recSubmit" type="submit"><br/>
</form>

Add Recording (Bonus)<br/>
Delete Recording (Bonus)<br/>
<hr>
<h2>Targets</h2>
<?php
$targetArr = json_decode($targets);
foreach ($targetArr As $row) {
echo  $row . ' <button type="button" onclick="javascript:targetDel('.$row.')" id="targetDel" value="'.$row.'">Delete</button><br/>'."\n";
}
?>
<b>Add Target</b>:
<input type="text" id="target" name="target"><br/><input type="submit" id="targetAdd" value="Add"> 
