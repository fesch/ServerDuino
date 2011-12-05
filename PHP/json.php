<?php
header('Content-type: application/json');
$datasets = array();
$jschart;
$jschart['datasets'] = array();
$datapoint1;
$datapoint1['id'] = "Temperature (Â°C)";
$datapoint1['type'] = "line";
$datapoint1['data'] = array();



//$jschart['datasets'][] = $datapoint1;
$json_array;

$legends = array("Temperature","Pressure","Humidity","Light");
foreach($legends as $k => $legend) {
	
	$datasets[$k] = array(
		"id"=>$legend,
		"type"=>"line",
		"data"=>array(),
	);
	
	for($i = 0; $i<20; $i++){
		$datasets[$k]["data"][] = array('unit'=>rand(0,100),"value"=>rand(0,100));
	}
	
}

$jschart["datasets"] = $datasets;

$json_array['JSChart'] = $jschart;
echo json_encode($json_array);