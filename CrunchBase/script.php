<?php

/*

Project Specifics:

1. We want to build a simple system that collects a list and relevant data of newly funded companies over the past week/2 weeks/month.
	The data will most likely be fed through the Crunchbase API, unless you think there is a better alternative.
2. Someone at Uprising manually goes through this list and selects the companies that fit our filters.
	The system then collects relevant data (investors, location, valuations, employee numbers, etc) from Crunchbase.
3. The system also keeps track of what proportion of companies fit our filters (a simple ratio of newly funded companies
	that fit our filters vs. those that don't).
4. Finally, the system regularly pulls new data for companies already inputted into the spreadsheet
	(i.e. the ones that already have been vetted and designated as meeting our filters).
	This can take the form of receiving additional funding, an exit (acquisition/IPO), and so on.
*/

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'phpdom.php';
require_once 'functions.inc.php';

$aC = array(
	'url'=>'http://www.crunchbase.com/funding-rounds?'
);

$queryOptions = array(
	'all',
	'seed',
	'angel',
	'a',
	'b',
	'c',
	'd',
	'e',
	'f',
	'g',
	'convertible',
	'unattributed',
	'crowd',
	'partial',
	'debt_round',
	'grant',
	'private_equity',
	'post_ipo_equity',
	'post_ipo_debt'
);

$query = array(
	'page'=>1,
	'q'=>''
);

$namespaces = array(
	'company',
	'person',
	'financial-organization'
);

function getEntityURL($namespace, $permalink){
	return 'http://api.crunchbase.com/v/1/' . $namespace . '/' . $permalink . '.js';
}

function crawlQuery(){
	global $aC, $query;
	$page = getURL($aC['url'] . http_build_query($query));
	$html = str_get_html($page);

	$content = $html->find('div#col2_internal', 0);
	$table = $content->find('table', 0);

	if (empty($table)) return;

	$results = array();

	foreach ($table->find('tr') as $index => $row) {
		if ($index == 0) continue;
		$td = $row->find('td');
		$name = $td[1]->innertext;
		$namehref = $td[1]->find('a', 0)->href;
		$namearray = explode('/', $namehref);
		$nameid = $namearray[count($namearray)-1];
		$investorsdom = $td[4]->find('a');
		$investors = array();
		foreach ($investorsdom as $index => $link) {
			$namearray = explode('/', $link->href);
			$nameid = $namearray[count($namearray)-1];
			array_push($investors, array(
				'name'=>$link->plaintext,
				'nameid'=>$nameid
			));
		}
		$info = removeTrimWhitespace(array(
			'date'=>$td[0]->plaintext,
			'name'=>$td[1]->plaintext,
			'nameid'=>$nameid,
			'round'=>$td[2]->plaintext,
			'size'=>$td[3]->plaintext,
			'investors'=>$investors,
		));
		array_push($results, $info);
	}

	print_json($results);
}

crawlQuery();
?>

