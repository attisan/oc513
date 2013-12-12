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

 	$id = intval($_POST['tid']);
//	$cid = intval($_POST['cid']);
    $data = OCA\Aufgaben\App::getEventObject($id, false, false);
	$object = OC_VObject::parse($data['calendardata']);
	$calId = OCA\Calendar\Object::getCalendarid($id); 
	$orgId=$data['org_objid'];
	
	
 if(isset($_POST['hiddenfield']) && $_POST['hiddenfield']==='edititTask' && $id>0){
 		
		
		OCA\Aufgaben\App::updateVCalendarFromRequest($_POST, $object);

		try {
			OCA\Aufgaben\App::edit($id, $object->serialize(),$orgId);
		} catch(Exception $e) {
			OCP\JSON::error(array('message'=>$e->getMessage()));
			exit;
		}
		
	if($calId!=intval($_POST['read_worker'])){
			OCA\Calendar\Object::moveToCalendar($id, intval($_POST['read_worker']));
		}

 }
 
    $vtodo = $object -> VTODO;
	$object = OCA\Calendar\Object::cleanByAccessClass($id, $object);
	$accessclass = $vtodo -> getAsString('CLASS');
	$permissions = OCA\Aufgaben\App::getPermissions($id, OCA\Aufgaben\App::TODO, $accessclass);
	
	
 
$TaskDate=''; 
$TaskTime='';
if($vtodo->due){
	 $dateDue=$vtodo->due;
	 	
	switch($dateDue->getDateType()) {
		case Sabre\VObject\Property\DateTime::LOCALTZ :
		case Sabre\VObject\Property\DateTime::LOCAL :
			$TaskDate = $dateDue -> getDateTime() -> format('d.m.Y');
			$TaskTime = $dateDue -> getDateTime() -> format('H:i');
			
			break;
		case Sabre\VObject\Property\DateTime::DATE :
			$TaskDate = $dateDue -> getDateTime() -> format('d.m.Y');
			$TaskTime = '';
			
			break;
	}
}
/*
if($vtodo->due){
    $dateTmp=$vtodo->due->getDateTime()->format('U');
	$TaskDate=date("d.m.Y",$dateTmp);
	$TaskTime=date("H:i",$dateTmp);
	if($TaskTime=='00:00') $TaskTime='';
}*/


$accessclass = $vtodo -> getAsString('CLASS');

$calendarsArrayTmp = OCA\Calendar\Calendar::allCalendars(OCP\User::getUser(), true);
$calendarsArray=array();

if($permissions !== OCP\PERMISSION_ALL){
	$sharedCal=OCA\Calendar\Object::getowner($id);
	$calendarsArray[$calId]=$sharedCal;
	
}else{
	
		foreach($calendarsArrayTmp as $calInfo){
			$calendarsArray[$calInfo['id']]=$calInfo['displayname'];
		}
		
		
}

$aktiveWorker=OCA\Aufgaben\App::generateSelectFieldArray('read_worker',$calId,$calendarsArray,false);
$priorityOptionsArray=OCA\Aufgaben\App::getPriorityOptionsFilterd();
$priorityOptions=OCA\Aufgaben\App::generateSelectFieldArray('priority',(string)$vtodo->priority,$priorityOptionsArray,false);
$access_class_options = OCA\Calendar\App::getAccessClassOptions();
//NEW Reminder
$reminder_options = OCA\Calendar\App::getReminderOptions();
$reminder_time_options = OCA\Calendar\App::getReminderTimeOptions();
//reminder
  
  $vtodosharees = array();
	
		
		$sharedwithByVtodo = OCP\Share::getItemShared('todo',$id);
		
		if(is_array($sharedwithByVtodo)) {
			foreach($sharedwithByVtodo as $share) {
				if($share['share_type'] == OCP\Share::SHARE_TYPE_USER || $share['share_type'] == OCP\Share::SHARE_TYPE_GROUP) {
					$vtodosharees[] = $share;
				}
			}
		}
 //NEW Reminder
/*testalarm*/

$aAlarm='';
if($vtodo -> VALARM){
	$valarm=$vtodo -> VALARM;
	$aAlarm['action']=$valarm -> getAsString('ACTION');
	$aAlarm['trigger']=$valarm -> getAsString('TRIGGER');
	$aAlarm['email']='';
	if($valarm ->ATTENDEE){
		$aAlarm['email']=$valarm -> getAsString('ATTENDEE');
		if(stristr($aAlarm['email'],'mailto:')) $aAlarm['email']=substr($aAlarm['email'],7,strlen($aAlarm['email']));
	}

	if(stristr($aAlarm['trigger'],'PT')){
			$tempDescr='';
		    $reminderdate='';
			$remindertime='';
			if(stristr($aAlarm['trigger'],'-PT')){
				$tempDescr='before';
			}
			if(stristr($aAlarm['trigger'],'+PT')){
				$tempDescr='after';
			}
			//GetTime
			$TimeCheck=substr($aAlarm['trigger'],3,strlen($aAlarm['trigger']));
			
			$reminder_time_input=substr($TimeCheck,0,(strlen($TimeCheck)-1));
			
			//returns M,H,D
			$alarmTimeDescr=substr($aAlarm['trigger'],-1,1);
			if($alarmTimeDescr=='H'){
				$reminder_time_select='hours'.$tempDescr;
			}
			if($alarmTimeDescr=='M'){
				$reminder_time_select='minutes'.$tempDescr;
			}
			if($alarmTimeDescr=='D'){
				$reminder_time_select='days'.$tempDescr;
			}
	}else{
	   
	    $dttriggertime=$valarm ->TRIGGER;
		switch($dttriggertime->getDateType()) {
			case Sabre\VObject\Property\DateTime::UTC :
				$timezone = new DateTimeZone(OCA\Calendar\App::getTimezone());
				$newDT = $dttriggertime -> getDateTime();
				$newDT -> setTimezone($timezone);
				$dttriggertime -> setDateTime($newDT);
			case Sabre\VObject\Property\DateTime::LOCALTZ :
			case Sabre\VObject\Property\DateTime::LOCAL :
				$reminderdate = $dttriggertime -> getDateTime() -> format('d-m-Y');
				$remindertime = $dttriggertime -> getDateTime() -> format('H:i');
				break;
         }

		$reminder_time_input='';
		$reminder_time_select='ondate';
	}
	
}

	$tmpl = new OCP\Template('aufgaben', 'event.edit', '');
	
	$tmpl -> assign('reminder_options', $reminder_options);
	$tmpl -> assign('reminder', $aAlarm['action']);
	$tmpl -> assign('reminder_time_options', $reminder_time_options);
	$tmpl -> assign('remindertimeselect', $reminder_time_select);
	$tmpl -> assign('remindertimeinput', $reminder_time_input);
	$tmpl -> assign('reminderemailinput', $aAlarm['email']);
	$tmpl -> assign('reminderdate', $reminderdate);
	$tmpl -> assign('remindertime', $remindertime);
	$tmpl -> assign('access_class_options', $access_class_options);
	$tmpl -> assign('accessclass', $accessclass);
	$tmpl -> assign('id', $id);
	$tmpl -> assign('calId', $calId);
	$tmpl -> assign('orgId', $orgId);
	$tmpl -> assign('permissions', $permissions);
	$tmpl -> assign('vtodo', $vtodo);
	$tmpl -> assign('priorityOptions', $priorityOptions);
	$tmpl -> assign('aktiveWorker', $aktiveWorker);
	$tmpl -> assign('TaskDate', $TaskDate);
	$tmpl -> assign('TaskTime', $TaskTime);
	$tmpl -> assign('vtodosharees', $vtodosharees);
	$tmpl -> printPage();


