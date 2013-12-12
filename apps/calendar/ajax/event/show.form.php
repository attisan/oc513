<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

if (!OCP\User::isLoggedIn()) {
	OCP\User::checkLoggedIn();
}

OCP\JSON::checkAppEnabled('calendar');

$id = $_POST['id'];
$data = OCA\Calendar\App::getEventObject($id, false, false);

$l=OC_L10N::get('calendar');


if (!$data) {
	OCP\JSON::error(array('data' => array('message' => OCA\Calendar\App::$l10n -> t('Wrong calendar'))));
	exit ;
}
$object = OC_VObject::parse($data['calendardata']);
$vevent = $object -> VEVENT;
$object = OCA\Calendar\Object::cleanByAccessClass($id, $object);
$accessclass = $vevent -> getAsString('CLASS');
$permissions = OCA\Calendar\App::getPermissions($id, OCA\Calendar\App::EVENT, $accessclass);


$dtstart = $vevent -> DTSTART;
$dtend = OCA\Calendar\Object::getDTEndFromVEvent($vevent);
switch($dtstart->getDateType()) {
	case Sabre\VObject\Property\DateTime::UTC :
		$timezone = new DateTimeZone(OCA\Calendar\App::getTimezone());
		$newDT = $dtstart -> getDateTime();
		$newDT -> setTimezone($timezone);
		$dtstart -> setDateTime($newDT);
		$newDT = $dtend -> getDateTime();
		$newDT -> setTimezone($timezone);
		$dtend -> setDateTime($newDT);
	case Sabre\VObject\Property\DateTime::LOCALTZ :
	case Sabre\VObject\Property\DateTime::LOCAL :
		$startdate = $dtstart -> getDateTime() -> format('d-m-Y');
		$starttime = $dtstart -> getDateTime() -> format('H:i');
		$enddate = $dtend -> getDateTime() -> format('d-m-Y');
		$endtime = $dtend -> getDateTime() -> format('H:i');
		$allday = false;
		break;
	case Sabre\VObject\Property\DateTime::DATE :
		$startdate = $dtstart -> getDateTime() -> format('d-m-Y');
		$starttime = '';
		$dtend -> getDateTime() -> modify('-1 day');
		$enddate = $dtend -> getDateTime() -> format('d-m-Y');
		$endtime = '';
		//$choosenDate=$choosenDate + (3600 * 24);
		$allday = true;
		break;
}

if($_POST['choosendate']!='') $choosenDate=$_POST['choosendate'];
else $choosenDate=$dtstart -> getDateTime() -> format('U');

$summary = strtr($vevent -> getAsString('SUMMARY'), array('\,' => ',', '\;' => ';'));
$location = strtr($vevent -> getAsString('LOCATION'), array('\,' => ',', '\;' => ';'));
$categories = $vevent -> getAsString('CATEGORIES');
$description = strtr($vevent -> getAsString('DESCRIPTION'), array('\,' => ',', '\;' => ';'));
$last_modified = $vevent -> __get('LAST-MODIFIED');
if ($last_modified) {
	$lastmodified = $last_modified -> getDateTime() -> format('U');
} else {
	$lastmodified = 0;
}
$addSingleDeleteButton=false;

if ($data['repeating'] == 1) {
	$addSingleDeleteButton=true;
	$rrule = explode(';', $vevent -> getAsString('RRULE'));
	$rrulearr = array();
	foreach ($rrule as $rule) {
		list($attr, $val) = explode('=', $rule);
		$rrulearr[$attr] = $val;
	}
	
	
	if (!isset($rrulearr['INTERVAL']) || $rrulearr['INTERVAL'] == '') {
		$rrulearr['INTERVAL'] = 1;
		
	}
	if (array_key_exists('BYDAY', $rrulearr)) {
		if (substr_count($rrulearr['BYDAY'], ',') == 0) {
			if (strlen($rrulearr['BYDAY']) == 2) {
				$repeat['weekdays'] = array($rrulearr['BYDAY']);
			} elseif (strlen($rrulearr['BYDAY']) == 3) {
				$repeat['weekofmonth'] = substr($rrulearr['BYDAY'], 0, 1);
				$repeat['weekdays'] = array(substr($rrulearr['BYDAY'], 1, 2));
			} elseif (strlen($rrulearr['BYDAY']) == 4) {
				$repeat['weekofmonth'] = substr($rrulearr['BYDAY'], 0, 2);
				$repeat['weekdays'] = array(substr($rrulearr['BYDAY'], 2, 2));
			}
		} else {
			$byday_days = explode(',', $rrulearr['BYDAY']);
			foreach ($byday_days as $byday_day) {
				if (strlen($byday_day) == 2) {
					$repeat['weekdays'][] = $byday_day;
				} elseif (strlen($byday_day) == 3) {
					$repeat['weekofmonth'] = substr($byday_day, 0, 1);
					$repeat['weekdays'][] = substr($byday_day, 1, 2);
				} elseif (strlen($byday_day) == 4) {
					$repeat['weekofmonth'] = substr($byday_day, 0, 2);
					$repeat['weekdays'][] = substr($byday_day, 2, 2);
				}
			}
		}
		
	}
	if (array_key_exists('BYMONTHDAY', $rrulearr)) {
		if (substr_count($rrulearr['BYMONTHDAY'], ',') == 0) {
			$repeat['bymonthday'][] = $rrulearr['BYMONTHDAY'];
		} else {
			$bymonthdays = explode(',', $rrulearr['BYMONTHDAY']);
			foreach ($bymonthdays as $bymonthday) {
				$repeat['bymonthday'][] = $bymonthday;
			}
		}
	}
	if (array_key_exists('BYYEARDAY', $rrulearr)) {
		if (substr_count($rrulearr['BYYEARDAY'], ',') == 0) {
			$repeat['byyearday'][] = $rrulearr['BYYEARDAY'];
		} else {
			$byyeardays = explode(',', $rrulearr['BYYEARDAY']);
			foreach ($byyeardays as $yearday) {
				$repeat['byyearday'][] = $yearday;
			}
		}
	}
	if (array_key_exists('BYWEEKNO', $rrulearr)) {
		if (substr_count($rrulearr['BYWEEKNO'], ',') == 0) {
			$repeat['byweekno'][] = (string)$rrulearr['BYWEEKNO'];
		} else {
			$byweekno = explode(',', $rrulearr['BYWEEKNO']);
			foreach ($byweekno as $weekno) {
				$repeat['byweekno'][] = (string)$weekno;
			}
		}
	}
	if (array_key_exists('BYMONTH', $rrulearr)) {
		$months = OCA\Calendar\App::getByMonthOptions();
		if (substr_count($rrulearr['BYMONTH'], ',') == 0) {
			$repeat['bymonth'][] = $months[$rrulearr['BYMONTH']];
		} else {
			$bymonth = explode(',', $rrulearr['BYMONTH']);
			foreach ($bymonth as $month) {
				$repeat['bymonth'][] = $months[$month];
			}
		}
		//BUG
		//$repeat['bymonthday'][] =$dtstart -> getDateTime() -> format('d');
	}
	switch($rrulearr['FREQ']) {
		case 'DAILY' :
			$repeat['repeat'] = 'daily';
			break;
		case 'WEEKLY' :
			if (array_key_exists('BYDAY', $rrulearr) === false) {
				$rrulearr['BYDAY'] = '';
			}
			if ($rrulearr['INTERVAL'] % 2 == 0) {
				$repeat['repeat'] = 'biweekly';
				$rrulearr['INTERVAL'] = $rrulearr['INTERVAL'] / 2;
			} elseif ($rrulearr['BYDAY'] == 'MO,TU,WE,TH,FR') {
				$repeat['repeat'] = 'weekday';
			} else {
				$repeat['repeat'] = 'weekly';
			}
			break;
		case 'MONTHLY' :
			$repeat['repeat'] = 'monthly';
			if (array_key_exists('BYDAY', $rrulearr)) {
				$repeat['month'] = 'weekday';
			} else {
				$repeat['month'] = 'monthday';
			}
			break;
		case 'YEARLY' :
			$repeat['repeat'] = 'yearly';
			if (array_key_exists('BYMONTH', $rrulearr)) {
				$repeat['year'] = 'bydaymonth';
			} elseif (array_key_exists('BYWEEKNO', $rrulearr)) {
				$repeat['year'] = 'byweekno';
			 } elseif (array_key_exists('BYYEARDAY', $rrulearr)) {
				$repeat['year'] = 'byyearday';
			}else {
				$repeat['year'] = 'bydate';
			}
	}
	$repeat['interval'] = $rrulearr['INTERVAL'];
	
	
	
	if (array_key_exists('COUNT', $rrulearr)) {
		$repeat['end'] = 'count';
		$repeat['count'] = $rrulearr['COUNT'];
	} elseif (array_key_exists('UNTIL', $rrulearr)) {
		$repeat['end'] = 'date';
		$endbydate_day = substr($rrulearr['UNTIL'], 6, 2);
		$endbydate_month = substr($rrulearr['UNTIL'], 4, 2);
		$endbydate_year = substr($rrulearr['UNTIL'], 0, 4);
		$repeat['date'] = $endbydate_day . '-' . $endbydate_month . '-' . $endbydate_year;
	} else {
		$repeat['end'] = 'never';
	}
	if (array_key_exists('weekdays', $repeat)) {
		$repeat_weekdays_ = array();
		$days = OCA\Calendar\App::getWeeklyOptions();
		foreach ($repeat['weekdays'] as $weekday) {
			$repeat_weekdays_[] = $days[$weekday];
		}
		$repeat['weekdays'] = $repeat_weekdays_;
	}
} else {
	$repeat['repeat'] = 'doesnotrepeat';
}

if($permissions !== OCP\PERMISSION_ALL){
	 $calendar_options[0]['id']=$data['calendarid'];
}else{
	$calendar_options = OCA\Calendar\Calendar::allCalendars(OCP\USER::getUser());
}

$category_options = OCA\Calendar\App::getCategoryOptions();
$access_class_options = OCA\Calendar\App::getAccessClassOptions();
$repeat_options = OCA\Calendar\App::getRepeatOptions();
$repeat_end_options = OCA\Calendar\App::getEndOptions();
$repeat_month_options = OCA\Calendar\App::getMonthOptions();
$repeat_year_options = OCA\Calendar\App::getYearOptions();
$repeat_weekly_options = OCA\Calendar\App::getWeeklyOptions();
$repeat_weekofmonth_options = OCA\Calendar\App::getWeekofMonth();
$repeat_byyearday_options = OCA\Calendar\App::getByYearDayOptions();
$repeat_bymonth_options = OCA\Calendar\App::getByMonthOptions();
$repeat_byweekno_options = OCA\Calendar\App::getByWeekNoOptions();
$repeat_bymonthday_options = OCA\Calendar\App::getByMonthDayOptions();

$repeatInfo=array();
if($repeat['end']=='count'){
	$repeatInfo['end']=$l->t('after').' '.$repeat['count'].' '.$l->t('Events');
}
if($repeat['end']=='date'){
	$repeatInfo['end']=$repeat['date'];
}
if($repeat['end']=='never'){
	$repeatInfo['end']=$repeat_end_options[$repeat['end']];
}

 $repeatInfo['interval']='';
if($repeat['interval']>1) $repeatInfo['interval']=$l->t('Interval'). ': '.$repeat['interval'].'<br/>';

$repeatInfo['infoRepeat']=$repeat_options[$repeat['repeat']].' ';

if($repeat['repeat']=='weekly'){
	$repeatInfo['infoRepeat'].=$l->t('Every').' ';	
	if(isset($repeat['weekdays'])){
			if(count($repeat['weekdays'])>1){
			  	$tVal='';		
			  	foreach($repeat['weekdays'] as $val){
			  		if($tVal=='') $tVal=$val;
					else $tVal.=',  '.$val;	
				  	}
				$repeatInfo['infoRepeat'].=$tVal;
			  }else{
			  	$repeatInfo['infoRepeat'].=$repeat['weekdays'][0];
			  }
	}
}


if($repeat['repeat']=='monthly'){
		
	if(isset($repeat['weekofmonth'])){
		$repeatInfo['infoRepeat'].=$l->t('Every').' '.$repeat_weekofmonth_options[$repeat['weekofmonth']].' ';
	}
	
	if(isset($repeat['weekdays'])){
			if(count($repeat['weekdays'])>1){
			  	$tVal='';		
			  	foreach($repeat['weekdays'] as $val){
			  		if($tVal=='') $tVal=$val;
					else $tVal.=',  '.$val;	
			  	}
				$repeatInfo['infoRepeat'].=$tVal;
			  }else{
			  	$repeatInfo['infoRepeat'].=$repeat['weekdays'][0];
			  }
	}
	
	if(isset($repeat['bymonthday'])){
			  $repeatInfo['infoRepeat'].=' '.$l->t('on').' ';	
			if(count($repeat['bymonthday'])>1){
			  	$tVal='';	
			  	foreach($repeat['bymonthday'] as $val){
			  		if($tVal=='') $tVal=$val.'.';
					else $tVal.=' '.$l->t('and').' '.$val.'.';
			  	}
				$repeatInfo['infoRepeat'].=$tVal;
			  }else{
			  	$repeatInfo['infoRepeat'].=$repeat['bymonthday'][0].'.';
			  }
			 
	}
}

if($repeat['repeat']=='yearly'){
	//bydate $repeat_year_options[];
	//\OCP\Util::writeLog('calendar',$repeat['year'] ,\OCP\Util::DEBUG);
	if($repeat['year']=='bydate') $repeatInfo['infoRepeat'].=$repeat_year_options[$repeat['year']];
	
	//if($repeat['year']=='byyearday') $repeatInfo['infoRepeat'].='jedes Jahr';
	/*
	if($repeat['year']=='byweekno') {
		$repeatInfo['infoRepeat'].='in der Kalenderwoche ';
		if(count($repeat['byweekno'])>1){
			  	$tVal='';	
			  	foreach($repeat['byweekno'] as $val){
			  		if($tVal=='') $tVal=$val.'.';
					else $tVal.=' '.$l->t('and').' '.$val.'.';
			  	}
				$repeatInfo['infoRepeat'].=$tVal;
			  }else{
			  	$repeatInfo['infoRepeat'].=$repeat['byweekno'][0];
			  }
			  $repeatInfo['infoRepeat'].=' '.$l->t('on').' ';	
		
		if(isset($repeat['weekdays'])){
			if(count($repeat['weekdays'])>1){
			  	$tVal='';		
			  	foreach($repeat['weekdays'] as $val){
			  		if($tVal=='') $tVal=$val;
					else $tVal.=' '.$l->t('and').' '.$val;
			  	}
				$repeatInfo['infoRepeat'].=$tVal;
			  }else{
			  	$repeatInfo['infoRepeat'].=$repeat['weekdays'][0];
			  }
	   }
	}
	*/
	if($repeat['year']=='bydaymonth') {
		
		 if(isset($repeat['bymonthday'])){
			  
			  if(count($repeat['bymonth'])>1){
			  	$tVal='';	
			  	foreach($repeat['bymonth'] as $val){
			  		if($tVal=='') $tVal=$val;
					else $tVal.=', '.$val;
			  	}
				$repeatInfo['infoRepeat'].=$tVal;
			  }else{
			  	$repeatInfo['infoRepeat'].=$repeat['bymonth'][0];
			  }
			$repeatInfo['infoRepeat'].=' '.$l->t('on').' ';	
			
			if(count($repeat['bymonthday'])>1){
			  	$tVal='';
					  	
			  	foreach($repeat['bymonthday'] as $val){
			  		if($tVal=='') $tVal=$val.'.';
					else $tVal.=' '.$l->t('and').' '.$val.'.';
			  	}
				$repeatInfo['infoRepeat'].=$tVal;
			  }else{
			  	$repeatInfo['infoRepeat'].=$repeat['bymonthday'][0].'.';
			  }
			 
	}	
			
		
	}
}

$aOExdate='';
if($vevent->EXDATE){
	
	$timezone = OCA\Calendar\App::getTimezone();
	
	foreach($vevent->EXDATE as $param){
		$param= new DateTime($param);
		$aOExdate[$param -> format('U')] = $param -> format('d-m-Y');
	}
	
	
	
}

//NEW Reminder
$reminder_options = OCA\Calendar\App::getReminderOptions();
$reminder_time_options = OCA\Calendar\App::getReminderTimeOptions();
//reminder
$aAlarm='';
if($vevent -> VALARM){
	$valarm=$vevent -> VALARM;
	$aAlarm['action']=$reminder_options[$valarm -> getAsString('ACTION')];
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
        $aAlarm['timeOptions']=$reminder_time_input.' '.$reminder_time_options[$reminder_time_select];
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
         $aAlarm['timeOptions'].='am '.$reminderdate.' '.$remindertime;
		
	}
	
}

if ($permissions & OCP\PERMISSION_READ) {
	$tmpl = new OCP\Template('calendar', 'part.showevent');
} elseif ($permissions === 0) {
	OCP\JSON::error(array('data' => array('message' => OCA\Calendar\App::$l10n -> t('You do not have the permissions to edit this event.'))));
	exit ;
}

$bShareOnlyEvent=0;
if(OCA\Calendar\Object::checkShareEventMode($id)){
	$bShareOnlyEvent=1;
}
$tmpl -> assign('bShareOnlyEvent', $bShareOnlyEvent);

$tmpl -> assign('eventid', $id);
$tmpl -> assign('permissions', $permissions);
$tmpl -> assign('lastmodified', $lastmodified);
$tmpl -> assign('exDate', $aOExdate);

$tmpl -> assign('calendar_options', $calendar_options);
$tmpl -> assign('access_class_options', $access_class_options);
$tmpl -> assign('aAlarm', $aAlarm);

$tmpl -> assign('title', $summary);
$tmpl -> assign('accessclass', $accessclass);
$tmpl -> assign('location', $location);
$tmpl -> assign('categories', $categories);
$tmpl -> assign('calendar', $data['calendarid']);
$tmpl -> assign('allday', $allday);
$tmpl -> assign('startdate', $startdate);
$tmpl -> assign('starttime', $starttime);
$tmpl -> assign('enddate', $enddate);
$tmpl -> assign('endtime', $endtime);
$tmpl -> assign('description', $description);
$tmpl -> assign('addSingleDeleteButton', $addSingleDeleteButton);
$tmpl -> assign('choosendate',$choosenDate);

$tmpl -> assign('repeat', $repeat['repeat']);
if ($repeat['repeat'] != 'doesnotrepeat') {
	$tmpl -> assign('repeatInfo',$repeatInfo);	
}		

$tmpl -> printpage();
