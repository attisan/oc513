<?php
/**
 * Copyright (c) 2011 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('calendar');

// Create default calendar ...
$calendars = OCA\Calendar\Calendar::allCalendars(OCP\USER::getUser(), false);
if( count($calendars) == 0) {
	OCA\Calendar\Calendar::addDefaultCalendars(OCP\USER::getUser());
	$calendars = OCA\Calendar\Calendar::allCalendars(OCP\USER::getUser(), true);
}

//Fix currentview for fullcalendar
/*NEW DAYVIEW*/
if(OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month') == "onedayview") {
	OCP\Config::setUserValue(OCP\USER::getUser(), "calendar", "currentview", "agendaDay");
}

if(OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month') == "oneweekview") {
	OCP\Config::setUserValue(OCP\USER::getUser(), "calendar", "currentview", "agendaWeek");
}
if(OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month') == "onemonthview") {
	OCP\Config::setUserValue(OCP\USER::getUser(), "calendar", "currentview", "month");
}
if(OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'currentview', 'month') == "listview") {
	OCP\Config::setUserValue(OCP\USER::getUser(), "calendar", "currentview", "list");
}

OCP\Util::addscript('calendar/3rdparty/fullcalendar', 'fullcalendar');
OCP\Util::addStyle('calendar/3rdparty/fullcalendar', 'fullcalendar');
OCP\Util::addscript('calendar','timepicker');

OCP\Util::addscript("3rdparty", "chosen/chosen.jquery.min");
OCP\Util::addStyle("3rdparty", "chosen/chosen");


if(OCP\Config::getUserValue(OCP\USER::getUser(), "calendar", "timezone") == null || OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timezonedetection') == 'true') {
	OCP\Util::addscript('calendar', 'geo');
}
OCP\Util::addscript('calendar', 'calendar');
OCP\Util::addStyle('calendar', 'style');
OCP\Util::addscript('', 'jquery.multiselect');
OCP\Util::addStyle('', 'jquery.multiselect');
OCP\Util::addscript('contacts','jquery.multi-autocomplete');
OCP\Util::addscript('','oc-vcategories');
OCP\Util::addscript('calendar','on-event');

OCP\App::setActiveNavigationEntry('calendar_index');

$leftNavAktiv=OCP\Config::getUserValue(OCP\USER::getUser(),'calendar','calendarnav');

$rightNavAktiv=OCP\Config::getUserValue(OCP\USER::getUser(),'calendar','tasknav');

$tmpl = new OCP\Template('calendar', 'calendar', 'user');
if($leftNavAktiv==='true') {
	$tmpl->assign('calendars', $calendars);
	$tmpl->assign('leftnavAktiv', $leftNavAktiv);
	$tmpl->assign('isHiddenCal', '');	
	$tmpl->assign('buttonCalAktive', 'button-info');	
}else{
	
	$tmpl->assign('calendars', '');
	$tmpl->assign('leftnavAktiv', $leftNavAktiv);
	$tmpl->assign('isHiddenCal', 'class="isHiddenCal"');	
	$tmpl->assign('buttonCalAktive', '');	
}

if($rightNavAktiv==='true' && OCP\App::isEnabled('aufgaben')) {
	

	$cDataTimeLine=new OCA\Aufgaben\Timeline();
	$cDataTimeLine->setCalendars($calendars);
	$taskOutPutbyTime=$cDataTimeLine->generateAddonCalendarTodo();
	$list = new OCP\Template('aufgaben', 'calendars.tasks.list', '');
	$list->assign('taskOutPutbyTime', $taskOutPutbyTime);
	
	$tmpl->assign('isHidden', '');
	$tmpl->assign('buttonTaskAktive', 'button-info');	
	$tmpl->assign('taskOutput', $list->fetchPage());
	$tmpl->assign('rightnavAktiv', $rightNavAktiv);
	
}else{
	$tmpl->assign('buttonTaskAktive', '');	
	$tmpl->assign('taskOutput', '');
	$tmpl->assign('rightnavAktiv', $rightNavAktiv);
	$tmpl->assign('isHidden', 'class="isHiddenTask"');
}

if(array_key_exists('showevent', $_GET)) {
	$tmpl->assign('showevent', $_GET['showevent']);
}
$tmpl->printPage();



