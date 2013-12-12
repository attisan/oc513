<?php
/*************************************************
 * ownCloud - Tasks Plugin                        *
 *                                                *
 * (c) Copyright 2011 Bart Visscher               *
 * This file is licensed under the Affero General *
 * Public License version 3 or later.             *
 * See the COPYING-README file.                   *
 *************************************************/

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('aufgaben');

if (!OCP\App::isEnabled('calendar')) {
	OCP\Template::printUserPage('aufgaben', 'no-calendar-app');
	exit;
}

$l=OC_L10N::get('aufgaben');

     $calendars = OCA\Calendar\Calendar::allCalendars(OCP\User::getUser(), true);
    
	$cDataTimeLineNav=new OCA\Aufgaben\Timeline();
	$cDataTimeLineNav->setCalendars($calendars);
	$outputTodoNav=$cDataTimeLineNav->generateTodoOutput();
		

if( count($calendars) == 0 ) {
	header('Location: ' . OCP\Util::linkTo('calendar', 'index.php'));
	exit;
}

OCP\Util::addScript('3rdparty/timepicker', 'jquery.ui.timepicker');
OCP\Util::addStyle('3rdparty/timepicker', 'jquery.ui.timepicker');
OCP\Util::addScript('aufgaben', 'aufgaben');
OCP\Util::addStyle('aufgaben', 'style');
OCP\Util::addScript('contacts', 'jquery.multi-autocomplete');
OCP\Util::addScript('', 'oc-vcategories');
OCP\App::setActiveNavigationEntry('aufgaben_index');

$priority_options = OCA\Aufgaben\App::getPriorityOptions();
$Categories = OCA\Calendar\App::getCategoryOptions();

$list = new OCP\Template('aufgaben', 'tasks.list', '');
$list->assign('calendars', $calendars);
$list->assign('tasksCount', $outputTodoNav['tasksCount']);
$list->assign('aTaskTime', $outputTodoNav['aTaskTime']);
$list->assign('aCountCalEvents', $outputTodoNav['aCountCalEvents']);

$output = new OCP\Template('aufgaben', 'index', 'user');
$output->assign('taskList', $list->fetchPage());



$output->assign('Categories', $Categories);

$output -> printPage();

