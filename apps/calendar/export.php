<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('calendar');
$cal = isset($_GET['calid']) ? $_GET['calid'] : null;
$event = isset($_GET['eventid']) ? $_GET['eventid'] : null;
if(!is_null($cal)) {
	$calendar = OCA\Calendar\App::getCalendar($cal, true);
	if(!$calendar) {
		header('HTTP/1.0 403 Forbidden');
		exit;
	}
	header('Content-Type: text/calendar');
	header('Content-Disposition: inline; filename=' . str_replace(' ', '-', $calendar['displayname']) . '.ics');
	echo OCA\Calendar\Export::export($cal, OCA\Calendar\Export::CALENDAR);
}elseif(!is_null($event)) {
	$data = OCA\Calendar\App::getEventObject($_GET['eventid'], true);
	if(!$data) {
		header('HTTP/1.0 403 Forbidden');
		exit;
	}
	header('Content-Type: text/calendar');
	header('Content-Disposition: inline; filename=' . str_replace(' ', '-', $data['summary']) . '.ics');
	echo OCA\Calendar\Export::export($event, OCA\Calendar\Export::EVENT);
}