<?php
function print_json($data, $die = true){
	header('Content-Type: application/json; charset=utf8');
	print_r(trim(json_encode($data /*, JSON_PRETTY_PRINT*/)));
	if ($die === true) die();
}

function print_jsonn($data, $die = true){
	print_r(trim(prettyPrint(json_encode($data /*, JSON_PRETTY_PRINT*/))));
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

function prettyPrint($json){
	$result = '';
	$level = 0;
	$prev_char = '';
	$in_quotes = false;
	$ends_line_level = NULL;
	$json_length = strlen($json);
	for ($i = 0; $i < $json_length; $i++) {
		$char = $json[$i];
		$new_line_level = NULL;
		$post = "";
		if ($ends_line_level !== NULL) {
			$new_line_level = $ends_line_level;
			$ends_line_level = NULL;
		}

		if ($char === '"' && $prev_char != '\\') {
			$in_quotes = !$in_quotes;
		} else if (!$in_quotes) {
			switch ($char) {
			case '}':
			case ']':
				$level--;
				$ends_line_level = NULL;
				$new_line_level = $level;
				break;

			case '{':
			case '[':
				$level++;
			case ',':
				$ends_line_level = $level;
				break;

			case ':':
				$post = " ";
				break;

			case " ":
			case "\t":
			case "\n":
			case "\r":
				$char = "";
				$ends_line_level = $new_line_level;
				$new_line_level = NULL;
				break;
			}
		}

		if ($new_line_level !== NULL) {
			$result .= "\n" . str_repeat("    ", $new_line_level);
		}

		$result .= $char . $post;
		$prev_char = $char;
	}
	return $result;
}
?>

