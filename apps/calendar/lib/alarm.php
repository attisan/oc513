<?php
/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev@georgswebsite.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/*
 * This class manages reminders for calendars
 */
 namespace OCA\Calendar;
 
class Alarm{
	private $nowTime=0;
	private $activeAlarms=array();
	private $aCalendars=array();
	
	public function __construct(){
			
		$this->nowTime=strtotime(date('d.m.Y H:i',time()))+(3600);
	
		
		$this->aCalendars = Calendar::allCalendars(\OCP\User::getUser());
		$this->checkAlarm();
		
	}

	public function checkAlarm(){
		
		$addWhereSql='';
		
		$aExec=array('1','VJOURNAL');
		
		foreach($this->aCalendars as $calInfo){
			if($addWhereSql=='') {
				$addWhereSql="`calendarid` = ? ";
				array_push($aExec,$calInfo['id']);
			}else{
				$addWhereSql.="OR `calendarid` = ? ";
				array_push($aExec,$calInfo['id']);
			}
			//\OCP\Util::writeLog('calendar','AlarmDB ID :'.$calInfo['id'] ,\OCP\Util::DEBUG);
		}
		
			//\OCP\Util::writeLog('calendar','AlarmDB :'.$addWhereSql.\OCP\User::getUser() ,\OCP\Util::DEBUG);
			
			
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_objects` WHERE `isalarm` = ? AND `objecttype`!= ?  AND ('.$addWhereSql.')');
		$result = $stmt->execute($aExec);
		$calendarobjects = array();
		while( $row = $result->fetchRow()) {
			
			$calendarobjects[]=$row;
		}
		
		if(is_array($calendarobjects)) $this->parseAlarm($calendarobjects);
		else return false;
		
	}
	
	public function parseAlarm($aEvents){
		
		$factor=60;
		
		foreach($aEvents as $event){
			   $startalarmtime=0;
			   $vMode='';
			   $object = \OC_VObject::parse($event['calendardata']);
			   if (isset($object->VEVENT)) {
			   		 $vevent = $object -> VEVENT;
			   		  $dtstart = $vevent -> DTSTART;
			   		 $vMode='event';
			   } 
			   if(isset($object->VTODO)) {
					$vevent = $object->VTODO;
				    $dtstart = $vevent -> DUE;
					 $vMode='todo';
				}
			   
              
			  
			   $starttime=$dtstart->getDateTime()->format('d.m.Y H:i');
			   $starttime=strtotime($starttime);
			  
			   if($vevent->VALARM){
			   	    $valarm=$vevent -> VALARM;
				    $triggerTime=$valarm -> getAsString('TRIGGER');
					if(stristr($triggerTime,'PT')){
						$triggerAlarm=self::parseTrigger($triggerTime);
						$startalarmtime=$starttime+$triggerAlarm;
					}else{
						 $triggerDate=$valarm -> TRIGGER;
						 $triggerAlarm=$triggerDate->getDateTime()->format('d.m.Y H:i');
						 $startalarmtime=strtotime($triggerAlarm);
					}
					
					$triggerAction=$valarm -> getAsString('ACTION');
					
			   }
			 
			 
			  // $checktime=$startalarmtime-$this->nowTime;
			   if($this->nowTime==$startalarmtime){
				   	   $userid=Object::getowner($event['id']);	
				    $link='';
				     if($vMode=='event') $link=\OCP\Util::linkTo('calendar', 'index.php').'#'.urlencode($event['id']);
				     if($vMode=='todo')   $link=\OCP\Util::linkTo('aufgaben', 'index.php').'#'.urlencode($event['id']);
					 
			   	  $this->activeAlarms[$event['id']]=array(
				     'id'=>$event['id'],
				     'userid'=>$userid,
				     'link'=>$link,
				     'action'=>$triggerAction,
				     'summary'=>$event['summary'],
				     'startdate'=>$dtstart->getDateTime()->format('d.m.Y H:i'),
				  );
			   }
			   \OCP\Util::writeLog('calendar','AlarmCheck Active:'.$event['summary'].' -> '.date('d.m.Y H:i',$startalarmtime).' : '.date('d.m.Y H:i',$this->nowTime),\OCP\Util::DEBUG);
	
			 // \OCP\Util::writeLog('calendar','AlarmCheck:'.$event['summary'].' -> '.date('d.m.Y H:i',$startalarmtime).' : '.date('d.m.Y H:i',$this->nowTime) ,\OCP\Util::DEBUG);
		}
		
	}
  
  public function getAlarms(){
  	    return $this->activeAlarms;
  }
  
	
	public static function parseTrigger($sTrigger){
		
		$iTriggerTime=0;
			  	
				$minutesCalc=60;
			    $hourCalc=($minutesCalc * 60);
				$dayCalc=($hourCalc * 24);
					
			  	$TimeCheck=substr($sTrigger,3,strlen($sTrigger));
			    //integer Val
			    $alarmTime=substr($TimeCheck,0,(strlen($TimeCheck)-1));
				//Minutes, Hour, Days
				$alarmTimeUnit=substr($sTrigger,-1,1);
				
				if($alarmTimeUnit=='M'){
			  	  	 $iTriggerTime=($alarmTime * $minutesCalc);
			  	 }
				if($alarmTimeUnit=='H'){
			  	  	 $iTriggerTime=($alarmTime * $hourCalc);
			  	  }
				if($alarmTimeUnit=='D'){
			  	  	 $iTriggerTime=($alarmTime * $dayCalc);
			  	  }
				
			  if(stristr($sTrigger,'-PT')){
			  	  $iTriggerTime= -$iTriggerTime;
			  }
			  
			  if(stristr($sTrigger,'+PT')){
			  	 $iTriggerTime= $iTriggerTime;
			  }
		
	   // \OCP\Util::writeLog('calendar','AlarmCheck: -> '.date('H:i',$iTriggerTime).':'.$iTriggerTime ,\OCP\Util::DEBUG);
		
		return $iTriggerTime;
		
	}

}