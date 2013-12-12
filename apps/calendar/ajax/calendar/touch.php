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
$id = $_POST['eventid'];

$data = OCA\Calendar\App::getEventObject($id, false, false);
	$vcalendar = OC_VObject::parse($data['calendardata']);
	 $vevent=$vcalendar->VEVENT;
     $vevent->setDateTime('LAST-MODIFIED', 'now', Sabre\VObject\Property\DateTime::UTC);
	$vevent->setDateTime('DTSTAMP', 'now', Sabre\VObject\Property\DateTime::UTC);
	

	try {
		OCA\Calendar\Object::edit($id, $vcalendar->serialize());
	} catch(Exception $e) {
		OCP\JSON::error(array('message'=>$e->getMessage()));
		exit;
	}

OCP\JSON::success();