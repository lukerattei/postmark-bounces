<?php

require_once 'PostmarkBounceApi.php';

$api = new PostmarkBounceApi('your-api-key');

echo "Message ID: ";
$id = trim(fgets(STDIN));
if ($id === '') {
	echo "Message ID is required!\n";
	exit(1);
}

print_r($api->getBounce($id));
