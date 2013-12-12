<?php
/**
 * Copyright (c) 2013 Lukas Reschke <lukas@statuscode.ch>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

// Set the content type to Javascript
header("Content-type: text/javascript");

// Disallow caching
header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 

$calendars = OCA\Calendar\Calendar::allCalendars(OCP\User::getUser());
$myCalendars=array();

foreach($calendars as $calendar) {
	if(!array_key_exists('active', $calendar)){
		$calendar['active'] = 1;
	}
	if($calendar['active'] == 1) {
		//$calendarInfo[$calendar['id']]=array('bgcolor'=>$calendar['calendarcolor'],'color'=>OCA\Calendar\Calendar::generateTextColor($calendar['calendarcolor']));
		$myCalendars[$calendar['id']]=array('id'=>$calendar['id'],'name'=>$calendar['displayname']);
	}
}

$array = array(
'mycalendars'=>json_encode($myCalendars),
'categories'=>json_encode(OCA\Calendar\App::getCategoryOptions())
);
// Echo it
foreach ($array as  $setting => $value) {
	echo("var ". $setting ."=".$value.";\n");
}