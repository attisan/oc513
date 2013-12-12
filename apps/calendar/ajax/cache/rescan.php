<?php
/**
 * Copyright (c) 2012 Georg Ehrke <georg@ownCloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
$calendars = OCA\Calendar\Calendar::allCalendars(OCP\USER::getUser());
foreach($calendars as $calendar) {
	OCA\Calendar\Repeat::cleancalendar($calendar['id']);
	OCA\Calendar\Repeat::generatecalendar($calendar['id']);
}
OCP\JSON::success();