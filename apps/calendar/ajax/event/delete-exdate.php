<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
 
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$id = $_POST['id'];
$choosenDate=$_POST['choosendate'];



try {
	
	$data = OCA\Calendar\App::getEventObject($id, false, false);
	$vcalendar = OC_VObject::parse($data['calendardata']);
	$vevent = $vcalendar->VEVENT;
	
	
	$vevent->setDateTime('LAST-MODIFIED', 'now', Sabre\VObject\Property\DateTime::UTC);
	$vevent->setDateTime('DTSTAMP', 'now', Sabre\VObject\Property\DateTime::UTC);
	$timezone = OCA\Calendar\App::getTimezone();
	$paramsExt=array();
	foreach($vevent->EXDATE as $key => $param){
		$paramToCheck= new DateTime($param);
		$checkEx=$paramToCheck -> format('U');
		if($checkEx!=$choosenDate){
			$paramsExt[]=$param;
		}
	} 
	 $vevent->setString('EXDATE','');
	
	foreach($paramsExt as $param){
		   
		    $vevent->addProperty('EXDATE',(string)$param);
	}
	
	
	
	$output='success';
	
	OCA\Calendar\Object::edit($id, $vcalendar->serialize());
	OCA\Calendar\Repeat::update($id);
	
} catch(Exception $e) {
	OCP\JSON::error(array('message'=>$e->getMessage().$datetime_element));
	exit;
}

OCP\JSON::success(array('message'=>(string)$output));