<?
$responses = file('/opt/phreakme/responses.txt');

print_r($responses);
$responseCount = count($responses);
echo "Total Responses: " . ($responseCount - 1) . "\n";
// start at 1 (ignore header)
for($i = 1; $i < $responseCount; $i++) {

	$response = explode(',', $responses[$i]);
	$dialedArray = explode('_', $response[3]);
	echo "Created Time: " . $response[0] . "\n";
	echo "Dialed: " . $dialedArray[1] . "\n";
	echo "Placed: " . substr($dialedArray[2], 0, -5) . "\n";
}

?>
