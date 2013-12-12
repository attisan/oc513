<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */



OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$errarr = OCA\Calendar\Object::validateRequest($_POST);
if($errarr) {
	//show validate errors
	OCP\JSON::error($errarr);
	exit;
}else{
	$cal = $_POST['calendar'];
	$vcalendar = OCA\Calendar\Object::createVCalendarFromRequest($_POST);
	try {
		OCA\Calendar\Object::add($cal, $vcalendar->serialize());
	} catch(Exception $e) {
		OCP\JSON::error(array('message'=>$e->getMessage()));
		exit;
	}
	OCP\JSON::success();
}