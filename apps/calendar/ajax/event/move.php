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

$allday = $_POST['allDay'];
$delta = new DateInterval('P0D');
$delta->d = $_POST['dayDelta'];
$delta->i = $_POST['minuteDelta'];
OCA\Calendar\App::isNotModified($vevent, $_POST['lastmodified']);

$dtstart = $vevent->DTSTART;
$dtend = OCA\Calendar\Object::getDTEndFromVEvent($vevent);
$start_type = $dtstart->getDateType();

$end_type = $dtend->getDateType();
if ($allday && $start_type != Sabre\VObject\Property\DateTime::DATE) {
	$start_type = $end_type = Sabre\VObject\Property\DateTime::DATE;
	$dtend->setDateTime($dtend->getDateTime()->modify('+1 day'), $end_type);
}
if (!$allday && $start_type == Sabre\VObject\Property\DateTime::DATE) {
	$start_type = $end_type = Sabre\VObject\Property\DateTime::LOCALTZ;
}
if($vevent->EXDATE){
	$aExt=$vevent->EXDATE;
	$vevent->setString('EXDATE','');
	 $timezone = OCA\Calendar\App::getTimezone();
	foreach($aExt as $param){
		$dateTime = new \DateTime($param->value);
		$datetime_element = new Sabre\VObject\Property\DateTime('EXDATE');
		$datetime_element -> setDateTime($dateTime->add($delta),$start_type);
	    $vevent->addProperty('EXDATE;TZID='.$timezone,(string) $datetime_element);
		//$output.=$dateTime->format('Ymd\THis').':'.$datetime_element.'success';
	}
	
}
$dtstart->setDateTime($dtstart->getDateTime()->add($delta), $start_type);
$dtend->setDateTime($dtend->getDateTime()->add($delta), $end_type);
unset($vevent->DURATION);

$vevent->setDateTime('LAST-MODIFIED', 'now', Sabre\VObject\Property\DateTime::UTC);
$vevent->setDateTime('DTSTAMP', 'now', Sabre\VObject\Property\DateTime::UTC);

try {
	OCA\Calendar\Object::edit($id, $vcalendar->serialize());
	OCA\Calendar\Repeat::update($id);
} catch(Exception $e) {
	OCP\JSON::error(array('message'=>$e->getMessage()));
	exit;
}

$lastmodified = $vevent->__get('LAST-MODIFIED')->getDateTime();
OCP\JSON::success(array('lastmodified'=>(int)$lastmodified->format('U'),'message'=>(string)$output));