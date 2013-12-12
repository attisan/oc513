<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');

$calendarcolor_options = OCA\Calendar\Calendar::getCalendarColorOptions();
$calendar = OCA\Calendar\App::getCalendar($_POST['calendarid'], true, true);
$tmpl = new OCP\Template("calendar", "part.editcalendar");
$tmpl->assign('new', false);
$tmpl->assign('calendarcolor_options', $calendarcolor_options);
$tmpl->assign('calendar', $calendar);
$tmpl->printPage();
