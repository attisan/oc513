<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$ALARMDATA=new OCA\Calendar\Alarm();
$ALARMDATA->checkAlarm();
$result=$ALARMDATA->getAlarms();
\OCP\Util::writeLog('calendar','Alarm Result:'.$result.':'.count($result) ,\OCP\Util::DEBUG);
if(count($result)>0){
	OCP\JSON::success(array('data'=>$result));
}	
