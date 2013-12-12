<?php
/**
 * Copyright (c) 2011, 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
session_write_close();

// Look for the calendar id
$calendar_id = null;
if (strval(intval($_GET['calendar_id'])) == strval($_GET['calendar_id'])) { // integer for sure.
	$id = intval($_GET['calendar_id']);
	$calendarrow = OCA\Calendar\App::getCalendar($id, true, false); // Let's at least security check otherwise we might as well use OCA\Calendar\Calendar::find())
	if($calendarrow !== false) {
		$calendar_id = $id;
	}else{
		if(OCP\Share::getItemSharedWithBySource('calendar', $id) === false){
			OCP\JSON::encodedPrint(array());
			exit;
		}
	}
}
$calendar_id = (is_null($calendar_id)?strip_tags($_GET['calendar_id']):$calendar_id);



OCP\Util::writeLog('calendar','END: ->'.date('d.m.Y',$_GET['end']), OCP\Util::DEBUG);

$start = (version_compare(PHP_VERSION, '5.3.0', '>='))?DateTime::createFromFormat('U', $_GET['start']):new DateTime('@' . $_GET['start']);
$end = (version_compare(PHP_VERSION, '5.3.0', '>='))?DateTime::createFromFormat('U', $_GET['end']):new DateTime('@' . $_GET['end']);
$events = OCA\Calendar\App::getrequestedEvents($calendar_id, $start, $end);
$output = array();

foreach($events as $event) {

		$eventArray=	OCA\Calendar\App::generateEventOutput($event, $start, $end);
		if(is_array($eventArray)) $output = array_merge($output, $eventArray);
	
}
OCP\JSON::encodedPrint($output);
