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

if (isset($_GET['info'])) {
	defined('PRINTINFO') or define('PRINTINFO', true);
}

$aC = array(
	'url'=>'http://www.crunchbase.com/funding-rounds?',
	'apikey'=>'ahxcatmbhhr9nzzjrm8r65fg'
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
	'page'=>isset($_GET['page']) ? $_GET['page'] : 1,
	'q'=>isset($_GET['q']) ? $_GET['q'] : ''
);

$namespaces = array(
	'company',
	'person',
	'financial-organization'
);

function getEntityURL($namespace, $permalink){
	global $aC;
	return 'http://api.crunchbase.com/v/1/' . $namespace . '/' . $permalink . '.js?api_key=' . $aC['apikey'];
}

function crawlQuery(){
	global $aC, $query, $namespaces;
	$page = getURL($aC['url'] . http_build_query($query));
	$html = str_get_html($page);

	$content = $html->find('div#col2_internal', 0);
	$table = $content->find('table', 0);

	if (empty($table)) return;

	$results = array();

	foreach ($table->find('tr') as $index => $row) {
		if ($index == 0) continue;
		$td = $row->find('td');
		$company = $td[1]->innertext;
		$companyhref = $td[1]->find('a', 0)->href;
		$companyarray = explode('/', $companyhref);
		$companyid = $companyarray[count($companyarray)-1];
		$companyjson = json_decode(getURL(getEntityURL($namespaces[0], $companyid)));
		$investorsdom = $td[4]->find('a');
		$investors = array();
		$address = array();
		$location = "";

		foreach ($investorsdom as $index => $link) {
			$namearray = explode('/', $link->href);
			$nameid = $namearray[count($namearray)-1];
			array_push($investors, array(
				'name'=>$link->plaintext,
				'nameid'=>$nameid
			));
		}

		$info = removeWhitespace(array(
			'date'=>$td[0]->plaintext,
			'company'=>$td[1]->plaintext,
			'companyid'=>$companyid,
			'round'=>$td[2]->plaintext,
			'size'=>$td[3]->plaintext,
			'investors'=>$investors
		));

		$datatable = removeWhitespace(array(
			'date'=>$td[0]->plaintext,
			'company'=>$td[1]->plaintext,
			'round'=>$td[2]->plaintext,
			'size'=>$td[3]->plaintext,
			'investors'=>count($investors)
		));

		if (!empty($companyjson)) {
			if (count($companyjson->offices)) {
				if (!empty($companyjson->offices[0]->address)) {
					$address[] = $companyjson->offices[0]->address;
				}
				if (!empty($companyjson->offices[0]->address2)) {
					$address[] = $companyjson->offices[0]->address2;
				}
				if (!empty($companyjson->offices[0]->zip_code)) {
					$address[] = $companyjson->offices[0]->zip_code;
				}
				if (!empty($companyjson->offices[0]->city)) {
					$address[] = $companyjson->offices[0]->city;
				}
				if (empty($address) && !empty($companyjson->offices[0]->description)) {
					$address[] = $companyjson->offices[0]->description;
				}
				if (!empty($companyjson->offices[0]->state_code)) {
					$address[] = $companyjson->offices[0]->state_code;
				}
				if (!empty($companyjson->offices[0]->country_code)) {
					$address[] = $companyjson->offices[0]->country_code;
				}
				$location = implode(', ', array_slice($address, -3, 3));
			}
			$companyinfo = array(
				'number_of_employees'=>$companyjson->number_of_employees,
				'founded_year'=>$companyjson->founded_year,
				'founded_month'=>$companyjson->founded_month,
				'founded_day'=>$companyjson->founded_day,
				'offices'=>$companyjson->offices,
				'location'=>$location,
				'total_money_raised'=>$companyjson->total_money_raised,
				'ipo'=>$companyjson->ipo
			);

			$info['companyinfo'] = $companyinfo;
			$datatable['employees'] = $companyinfo['number_of_employees'];
			$datatable['location'] = $location;
			$datatable['founded'] = $companyinfo['founded_year'];

			if (empty($datatable['employees'])) {
				$datatable['employees'] = 'N/A';
			}
			if (empty($datatable['location'])) {
				$datatable['location'] = 'N/A';
			}
			if (empty($datatable['founded'])) {
				$datatable['founded'] = 'N/A';
			}
		} else {
			$datatable['employees'] = 'N/A';
			$datatable['location'] = 'N/A';
			$datatable['founded'] = 'N/A';
		}

		array_push($results, (defined('PRINTINFO') ? $info : $datatable));
	}

	prettyPrintJSON($results);
}

crawlQuery();
?>
