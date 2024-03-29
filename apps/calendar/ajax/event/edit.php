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

$id = $_POST['id'];

if(!array_key_exists('calendar', $_POST)) {
	$cal = OCA\Calendar\Object::getCalendarid($id);
	$_POST['calendar'] = $cal;
}else{
	$cal = $_POST['calendar'];
}

$errarr = OCA\Calendar\Object::validateRequest($_POST);

if($errarr) {
	//show validate errors
	OCP\JSON::error($errarr);
	exit;
}else{
	$data = OCA\Calendar\App::getEventObject($id, false, false);
	$vcalendar = OC_VObject::parse($data['calendardata']);

	OCA\Calendar\App::isNotModified($vcalendar->VEVENT, $_POST['lastmodified']);
	OCA\Calendar\Object::updateVCalendarFromRequest($_POST, $vcalendar);

	try {
		OCA\Calendar\Object::edit($id, $vcalendar->serialize());
	} catch(Exception $e) {
		OCP\JSON::error(array('message'=>$e->getMessage()));
		exit;
	}
	if ($data['calendarid'] != $cal) {
		try {
			OCA\Calendar\Object::moveToCalendar($id, $cal);
			
			
		} catch(Exception $e) {
			OCP\JSON::error(array('message'=>$e->getMessage()));
			exit;
		}
	}
	OCP\JSON::success();
}