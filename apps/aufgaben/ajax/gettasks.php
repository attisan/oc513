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

$atasksModeAllowed = array('today'=>1,'tomorrow'=>1,'actweek'=>1,'withoutdate'=>1,'missedactweek'=>1,'alltasks'=>1,'alltasksdone'=>1,'sharedtasks'=>1,'comingsoon'=>1);
$tasks = array();

$SMODE='';
if(array_key_exists('mode',$_POST) && $_POST['mode']!='') $SMODE=$_POST['mode'];

//Get Tasks of an Calendar
if(array_key_exists('calid',$_POST) && intval($_POST['calid'])>0 && $SMODE=='') {
	$calId=intval($_POST['calid']);
	
	$cDataTimeLine=new OCA\Aufgaben\Timeline();
	$cDataTimeLine->setTimeLineMode('');
	$cDataTimeLine->setCalendarId($calId);
	$tasks=$cDataTimeLine->generateCalendarSingleOutput();
	
}

// Get All Tasks
if(array_key_exists('calid',$_POST) && intval($_POST['calid'])==0 && $SMODE==''){
	
	 \OCP\Util::writeLog('calendar','AlarmDB LALAL :' ,\OCP\Util::DEBUG);	
		
	$calendars = OCA\Calendar\Calendar::allCalendars(OCP\User::getUser(), true);
	
	$cDataTimeLine=new OCA\Aufgaben\Timeline();
	$cDataTimeLine->setTimeLineMode('');
	$cDataTimeLine->setCalendarId(0);
	$cDataTimeLine->setCalendars($calendars);
	$tasks=$cDataTimeLine->generateTasksAllOutput();
	
}

// Get Timelined tasks
if($SMODE!='' && $atasksModeAllowed[$SMODE] && $SMODE!='sharedtasks'){
		
	   $calendars = OCA\Calendar\Calendar::allCalendars(OCP\User::getUser(), true);
		
	   $cDataTimeLine=new OCA\Aufgaben\Timeline();
	   $cDataTimeLine->setTimeLineMode($_POST['mode']);
	   $cDataTimeLine->setCalendars($calendars);
	   $tasks=$cDataTimeLine->generateTasksAllOutput();

}
//Get Shared Tasks
if($SMODE!='' && $atasksModeAllowed[$SMODE] && $SMODE=='sharedtasks'){
	
	$singletodos = OCP\Share::getItemsSharedWith('todo', OCA\Aufgaben\Share_Backend_Vtodo::FORMAT_TODO);
			if(is_array($singletodos)){
				foreach($singletodos as $singletodo) {
						$tasks[] =  $singletodo;
					 \OCP\Util::writeLog('aufgaben','shared Todos'.$singletodo['id'], \OCP\Util::DEBUG);
				}
		   }	
}

OCP\JSON::encodedPrint($tasks);
