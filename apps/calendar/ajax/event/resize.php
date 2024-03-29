<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

$id = $_POST['id'];

$vcalendar = OCA\Calendar\App::getVCalendar($id, false, false);
$vevent = $vcalendar->VEVENT;

$accessclass = $vevent->getAsString('CLASS');
$permissions = OCA\Calendar\App::getPermissions($id, OCA\Calendar\App::EVENT, $accessclass);
if(!$permissions & OCP\PERMISSION_UPDATE) {
	OCP\JSON::error(array('message'=>'permission denied'));
	exit;
}

$delta = new DateInterval('P0D');
$delta->d = $_POST['dayDelta'];
$delta->i = $_POST['minuteDelta'];

OCA\Calendar\App::isNotModified($vevent, $_POST['lastmodified']);

$dtend = OCA\Calendar\Object::getDTEndFromVEvent($vevent);
$end_type = $dtend->getDateType();
$dtend->setDateTime($dtend->getDateTime()->add($delta), $end_type);
unset($vevent->DURATION);

$vevent->setDateTime('LAST-MODIFIED', 'now', Sabre\VObject\Property\DateTime::UTC);
$vevent->setDateTime('DTSTAMP', 'now', Sabre\VObject\Property\DateTime::UTC);

OCA\Calendar\Object::edit($id, $vcalendar->serialize());
$lastmodified = $vevent->__get('LAST-MODIFIED')->getDateTime();
OCP\JSON::success(array('lastmodified'=>(int)$lastmodified->format('U')));
