<?php

require_once 'PostmarkBounceApi.php';
require_once 'fileUtil.php';

echo "Email address: ";
$email = trim(fgets(STDIN));

echo "Save results to file: ";
$filename = trim(fgets(STDIN));
if ($filename === '') {
	$filename = 'bounces';
}
else if (preg_match('/\.csv$|\.json$/', $filename)) {
	$filename = substr($filename, 0, strrpos($filename, '.'));
}

$api = new PostmarkBounceApi('your-api-key');

// To see all possible filters, visit
// http://developer.postmarkapp.com/developer-bounces.html#get-bounces
$filters = array();
if ($email !== '') {
	$filters['emailFilter'] = $email;
}

$bounces = $api->getBounces($filters);

if (empty($bounces)) {
	echo "No bounces were found\n";
	exit(0);
}

echo "Found " . count($bounces) . " bounces\n";

if (writeJsonToFile("$filename.json", $bounces)) {
	echo "Saved bounces to $filename.json\n";
}
if (writeCsvToFile("$filename.csv", $bounces)) {
	echo "Saved bounces to $filename.csv\n";
}
