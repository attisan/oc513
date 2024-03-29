<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

$tmpl = new OCP\Template( 'calendar', 'settings');
$timezone=OCP\Config::getUserValue(OCP\USER::getUser(),'calendar','timezone','');
$tmpl->assign('timezone',$timezone);


$tmpl->assign('timezones',DateTimeZone::listIdentifiers());
$tmpl->assign('calendars', OCA\Calendar\Calendar::allCalendars(OCP\USER::getUser()), false);

OCP\Util::addscript('calendar','settings');

$tmpl->printPage();