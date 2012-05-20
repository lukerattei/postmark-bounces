<?php

require_once 'PostmarkBounceApi.php';

echo "Email address: ";
$email = trim(fgets(STDIN));

$api = new PostmarkBounceApi('your-api-key');

$filters = array();
if ($email !== '') {
	$filters['emailFilter'] = $email;
}

$bounces = $api->getBounces($filters);

$emailsToActivate = array();
foreach ($bounces as $bounce) {
	if ($bounce['Inactive'] == '1' && $bounce['CanActivate'] == '1') {
		$emailsToActivate[$bounce['Email']] = $bounce['ID'];
	}
}

if (count($emailsToActivate) === 0) {
	echo "No inactive email addresses found, so there's nothing to reactivate.\n";
	exit(0);
}

echo count($emailsToActivate) . " inactive email addresses found. Would you like to reactivate all of them? (y / n)\n";
if (trim(fgets(STDIN)) == 'y') {
	foreach ($emailsToActivate as $email => $id) {
		echo "Reactivating '$email'\n";
		$api->activateBounce($id);
	}
	exit(0);
}

echo "Would you like to choose which email addresses to reactivate? (y / n)\n";
if (trim(fgets(STDIN)) === 'y') {
	foreach ($emailsToActivate as $email => $id) {
		echo "Reactivate '$email'? (y / n)  ";
		if (trim(fgets(STDIN)) == 'y') {
			$api->activateBounce($id);
		}
	}
	exit(0);
}

echo "OK!  No email addresses were reactivated.\n";
