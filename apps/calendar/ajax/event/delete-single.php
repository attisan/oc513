<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
 /*

BEGIN:VCALENDAR
VERSION:2.0
PRODID:ownCloud Calendar

BEGIN:VEVENT
CREATED;VALUE=DATE-TIME:20131014T161915Z
UID:1566a63aaa
RRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20131130
LAST-MODIFIED;VALUE=DATE-TIME:20131015T155427Z
DTSTAMP;VALUE=DATE-TIME:20131015T155427Z
SUMMARY:testing
DTSTART;VALUE=DATE-TIME:20131010T100000Z
DTEND;VALUE=DATE-TIME:20131010T103000Z
CLASS:PUBLIC
EXDATE;VALUE=DATE-TIME:20131017T090000Z
END:VEVENT
END:VCALENDAR
  * 
  * 
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Inc.//iCal 4.0.4//EN
CALSCALE:GREGORIAN

BEGIN:VEVENT
CREATED:20131015T103327Z
UID:90DFCA6B-F625-4A54-987C-B71AB9141A0F
DTEND;TZID=Europe/Berlin:20131026T140000
RRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20131231T225959Z
TRANSP:OPAQUE
SUMMARY:test win/som
DTSTART;TZID=Europe/Berlin:20131026T130000
DTSTAMP:20131015T103410Z
SEQUENCE:5
END:VEVENT
END:VCALENDAR

  * 
*/
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$id = $_POST['id'];
$choosenDate=$_POST['choosendate'];



try {
	
	$data = OCA\Calendar\App::getEventObject($id, false, false);
	$vcalendar = OC_VObject::parse($data['calendardata']);
	$vevent = $vcalendar->VEVENT;
	/*
	$winter = new DateTime(date('Y').'-12-21', new DateTimeZone(OCA\Calendar\App::getTimezone()));
	$winterOffset=OC_VObject::offsetSec2His($winter->getOffset());
	
    $summer = new DateTime(date('Y').'-06-21', new DateTimeZone(OCA\Calendar\App::getTimezone()));
	$summerOffset=OC_VObject::offsetSec2His($summer->getOffset());
	
	$timezone = new DateTimeZone(OCA\Calendar\App::getTimezone());
	$transitionsStart = $timezone->getTransitions($vevent->DTSTART->getDateTime()->format('U'));
	//$aRule=$vevent->RRULE->getAsArray();
	$rrule = explode(';', $vevent -> getAsString('RRULE'));
	$rrulearr = array();
	foreach ($rrule as $rule) {
		list($attr, $val) = explode('=', $rule);
		$rrulearr[$attr] = $val;
	}
	
	if($rrulearr['UNTIL']){
		$endbydate_day = substr($rrulearr['UNTIL'], 6, 2);
		$endbydate_month = substr($rrulearr['UNTIL'], 4, 2);
		$endbydate_year = substr($rrulearr['UNTIL'], 0, 4);
		$endTime = new DateTime($endbydate_day . '-' . $endbydate_month . '-' . $endbydate_year);
		$endTime=$endTime->format('U');
		$transitionsEnd = $timezone->getTransitions($endTime);
	}else{
		$transitionsEnd = $timezone->getTransitions($vevent->DTEND->getDateTime()->format('U'));
	}*/
	
	/*
	if(!$vcalendar->VTIMEZONE){
		  $vtimezone = new OC_VObject('VTIMEZONE');
	      $vcalendar->add($vtimezone);
	     $vtimezone->addProperty('TZID',OCA\Calendar\App::getTimezone());
		 
		 $vstandard = new OC_VObject('STANDARD');
		 $vtimezone->add($vstandard);
		 $vstandard->addProperty('TZOFFSETFROM',(string)$summerOffset);
		 $vstandard->addProperty('TZOFFSETTO',(string)$winterOffset);
		 $vstandard->addProperty('TZNAME','CET');
		 $vstandard->addProperty('DTSTART','20131031T020000');
		 
		 $vdaylight = new OC_VObject('DAYLIGHT');
		 $vtimezone->add($vdaylight);
		 $vdaylight->addProperty('TZOFFSETFROM',(string)$winterOffset);
		 $vdaylight->addProperty('TZOFFSETTO',(string)$summerOffset);
		 $vdaylight->addProperty('TZNAME','CEST');
		 $vdaylight->addProperty('DTSTART','20130328T030000');
	}*/

	
	
	
	$vevent->setDateTime('LAST-MODIFIED', 'now', Sabre\VObject\Property\DateTime::UTC);
	$vevent->setDateTime('DTSTAMP', 'now', Sabre\VObject\Property\DateTime::UTC);
	
	if($_POST['allday']=='true'){
		
		    $dateTime = new \DateTime($choosenDate);
			$datetime_element = new Sabre\VObject\Property\DateTime('EXDATE');
			$datetime_element -> setDateTime($dateTime, Sabre\VObject\Property\DateTime::DATE);
		    $vevent->addProperty('EXDATE',(string) $datetime_element);
		    
	}else{
	       $timezone = OCA\Calendar\App::getTimezone();
           $timezone = new DateTimeZone($timezone);
	       $dStartTime=$vevent->DTSTART->getDateTime();
		   $TimeSave=$dStartTime->format('H:i');
	       $dateTime = new \DateTime($choosenDate.' '.$TimeSave,$timezone);
		   /*
		   $dateTimeOut=$dateTime->format('Ymd\THis');
		   $vevent->addProperty('EXDATE;TZID='.OCA\Calendar\App::getTimezone(),(string)$dateTimeOut);
		  */
		    $datetime_element = new Sabre\VObject\Property\DateTime('EXDATE');
			$datetime_element -> setDateTime($dateTime, Sabre\VObject\Property\DateTime::LOCALTZ);
		    $vevent->addProperty('EXDATE',(string) $datetime_element);
	   
	}
	
	$output='success';
	
	OCA\Calendar\Object::edit($id, $vcalendar->serialize());
	OCA\Calendar\Repeat::update($id);
	
} catch(Exception $e) {
	OCP\JSON::error(array('message'=>$e->getMessage().$datetime_element));
	exit;
}

OCP\JSON::success(array('message'=>(string)$output));