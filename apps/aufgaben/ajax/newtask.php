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


 if(isset($_POST['hiddenfield']) && $_POST['hiddenfield']==='newitTask'){
 	  	   
        $cid = intval($_POST['read_worker']);
	 
	    $vcalendar = OCA\Aufgaben\App::createVCalendarFromRequest($_POST);
	    try {
			OCA\Calendar\Object::add($cid, $vcalendar->serialize());
		} catch(Exception $e) {
			OCP\JSON::error(array('message'=>$e->getMessage()));
			exit;
		}
		
 }
 


$calendarsArrayTmp = OCA\Calendar\Calendar::allCalendars(OCP\User::getUser(), true);
//Filter Importent Values
$calendarsArray=array();
foreach($calendarsArrayTmp as $calInfo){
	if($calInfo['permissions'] & OCP\PERMISSION_CREATE){	
		$calendarsArray[$calInfo['id']]=$calInfo['displayname'];
	}
}


$priorityOptionsArray=OCA\Aufgaben\App::getPriorityOptionsFilterd();
$access_class_options = OCA\Calendar\App::getAccessClassOptions();

$priorityOptions=OCA\Aufgaben\App::generateSelectFieldArray('priority',$_POST['priority'],$priorityOptionsArray,false);
$aktiveWorker=OCA\Aufgaben\App::generateSelectFieldArray('read_worker',$_POST['read_worker'],$calendarsArray);
//NEW Reminder
$reminder_options = OCA\Calendar\App::getReminderOptions();
$reminder_time_options = OCA\Calendar\App::getReminderTimeOptions();
//reminder


    $tmpl = new OCP\Template('aufgaben', 'event.new', '');
	$tmpl -> assign('priorityOptions', $priorityOptions);
	$tmpl->assign('access_class_options', $access_class_options);
	$tmpl -> assign('aktiveWorker', $aktiveWorker);
	$tmpl -> assign('reminder_options', $reminder_options);
	$tmpl -> assign('reminder','');
	$tmpl -> assign('reminder_time_options', $reminder_time_options);
	$tmpl -> assign('remindertimeselect','');
	$tmpl -> assign('remindertimeinput','');
	$tmpl -> assign('reminderemailinput','');
	
	$tmpl -> printPage();


