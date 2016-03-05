<?php
$report = shell_exec("curl http://localhost/service/report");
$reportArr = json_decode($report);
?>
<h1>Report</h1>
<table>

<tr><td>Created</td><td>Dialed</td><td>Input</td><td>Response</td><td>Placed</td></tr>
<?php
foreach ($reportArr as $row) {
//print_r($row);
		echo "<tr>";
	echo "<td>".$row->Created."</td><td>".$row->Dialed."</td><td>".$row->Input."</td><td>".$row->InputResponse."</td><td>".$row->placed."</td>";
	echo "</tr>";

}

?>
<table>
