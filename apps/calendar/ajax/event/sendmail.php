<?php
/**
 * Copyright (c) 2013 Visitha Baddegama <visithauom@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$eventId = $_POST['eventId'];
//$emails[]=$_POST['emails'];
$emails=array_map('trim',explode(",",$_POST['emails']));
//check if user is actually allowed to access event
$event = OCA\Calendar\App::getEventObject($eventId);
if($event === false || $event === null) {
	OCP\JSON::error();
	exit;
}

$summary = $event['summary'];
$dtstart = $event['startdate'];
$dtend = $event['enddate'];

try {
	OCA\Calendar\App::sendEmails($eventId, $summary, $dtstart, $dtend,$emails);
	OCP\JSON::success();
} catch(Exception $e) {
	\OCP\Util::writeLog('calendar', 'sending mail failed (' . $e->getMessage() . ')', \OCP\Util::WARN);
	OCP\JSON::error();
}