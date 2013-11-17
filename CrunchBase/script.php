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

$page = getURL($aC['url'] . http_build_query($query));
$html = str_get_html($page);

echo $html;
?>

