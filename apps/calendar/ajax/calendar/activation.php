<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$calendarid = $_POST['calendarid'];
$calendar = OCA\Calendar\App::getCalendar($calendarid, true);
if(!$calendar) {
	OCP\JSON::error(array('message'=>'permission denied'));
	exit;
}

try {
	OCA\Calendar\Calendar::setCalendarActive($calendarid, $_POST['active']);
} catch(Exception $e) {
	OCP\JSON::error(array('message'=>$e->getMessage()));
	exit;
}

$calendar = OCA\Calendar\App::getCalendar($calendarid);
OCP\JSON::success(array(
	'active' => $calendar['active'],
	'eventSource' => OCA\Calendar\Calendar::getEventSourceInfo($calendar),
));