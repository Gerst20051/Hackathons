<?php
function print_json($data, $die = true){
	header('Content-Type: application/json; charset=utf8');
	print_r(trim(json_encode($data, JSON_PRETTY_PRINT)));
	if ($die === true) die();
}

function print_jsonn($data, $die = true){
	print_r(trim(json_encode($data, JSON_PRETTY_PRINT)));
	if ($die === true) die();
}

function getURL($url){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

function getJSON($url){
	$response = getURL($url);
	if (strlen($response)) {
		$data = json_decode($response);
		if (is_array($data) || is_object($data)) {
			return $data;
		}
	}
}

function removeTrimWhitespace($array){
	foreach ($array as $key=>$value) {
		if (is_string($value)) {
			$array[$key] = trim(preg_replace('/\s+/', ' ', $value));
		}
	}
	return $array;
}

?>

