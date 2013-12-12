<?php
/**
 * ownCloud - Aufgaben Remastered
 *
 * @author Sebastian Doell
 * @copyright 2013 sebastian doell sebastian@libasys.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
 

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('aufgaben');
OCP\JSON::callCheck();

    $calendars = OCA\Calendar\Calendar::allCalendars(OCP\User::getUser(), true);
    
	$cDataTimeLine=new OCA\Aufgaben\Timeline();
	$cDataTimeLine->setCalendars($calendars);
	$outputTodoNav=$cDataTimeLine->generateTodoOutput();
	
	$tmpl = new OCP\Template('aufgaben', 'tasks.list', '');
	$tmpl->assign('calendars', $calendars);
	$tmpl->assign('tasksCount', $outputTodoNav['tasksCount']);
	$tmpl->assign('aTaskTime', $outputTodoNav['aTaskTime']);
	$tmpl->assign('aCountCalEvents', $outputTodoNav['aCountCalEvents']);
	
	$tmpl -> printPage();
