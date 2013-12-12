<?php

namespace OCA\Calendar;

class SearchProvider extends \OC_Search_Provider{
	
	
	
	function search($query) {
			
		$today= date('Y-m-d',time());
		$allowedCommands=array('#ra'=>1,'#dt'=>1);	
			
		$calendars = Calendar::allCalendars(\OCP\USER::getUser(), true);
		if(count($calendars)==0 || !\OCP\App::isEnabled('calendar')) {
			//return false;
		}
		$results=array();
		$searchquery=array();
		if(substr_count($query, ' ') > 0) {
			$searchquery = explode(' ', $query);
		}else{
			$searchquery[] = $query;
		}
	
		
		$user_timezone = App::getTimezone();
		$l = new \OC_l10n('calendar');
		
		$isDate=false;
		if(strlen($query)>=5 && self::validateDate($query)){
			$isDate=true;
			//\OCP\Util::writeLog('calendar','VALID DATE FOUND', \OCP\Util::DEBUG);
		}
		
		foreach($calendars as $calendar) {
			$objects = Object::all($calendar['id']);
			foreach($objects as $object) {
				if($object['objecttype']!='VEVENT') {
					continue;
				}
				$searchAdvanced=false;
	
					if($isDate==true && strlen($query)>=5){
						\OCP\Util::writeLog('calendar','search: ->'.$query, \OCP\Util::DEBUG);
						$tempQuery=strtotime($query);
					   $checkDate=date('Y-m-d',$tempQuery);
					   if(substr_count($object['startdate'],$checkDate)>0){
					 	  $searchAdvanced=true;
					    }
					}
				
				if(array_key_exists($query,$allowedCommands) && $allowedCommands[$query]){
					if($query=='#dt'){
						$search=$object['startdate'];	
						if(substr_count($search,$today)>0){
							$searchAdvanced=true;
							
						}
					}
					
					if($query=='#ra'){
						if($object['isalarm']==1){
							$searchAdvanced=true;
						}		
						
					}
		         }
				
				if(substr_count(strtolower($object['summary']), strtolower($query)) > 0 || $searchAdvanced==true) {
					$calendardata = \OC_VObject::parse($object['calendardata']);
					$vevent = $calendardata->VEVENT;
					$dtstart = $vevent->DTSTART;
					$dtend = Object::getDTEndFromVEvent($vevent);
					$start_dt = $dtstart->getDateTime();
					$start_dt->setTimezone(new \DateTimeZone($user_timezone));
					$end_dt = $dtend->getDateTime();
					$end_dt->setTimezone(new \DateTimeZone($user_timezone));
					if ($dtstart->getDateType() == \Sabre\VObject\Property\DateTime::DATE) {
						$end_dt->modify('-1 sec');
						if($start_dt->format('d.m.Y') != $end_dt->format('d.m.Y')) {
							$info = $l->t('Date') . ': ' . $start_dt->format('d.m.Y') . ' - ' . $end_dt->format('d.m.Y');
						}else{
							$info = $l->t('Date') . ': ' . $start_dt->format('d.m.Y');
						}
					}else{
						$info = $l->t('Date') . ': ' . $start_dt->format('d.m.y H:i') . ' - ' . $end_dt->format('d.m.y H:i');
					}
					$link = \OCP\Util::linkTo('calendar', 'index.php').'#'.urlencode($object['id']);
					$results[]=new \OC_Search_Result($object['summary'],$info, $link,(string)$l->t('Cal.'));//$name,$text,$link,$type
				}
			}
		}
		return $results;
	}

  public static function validateDate($Str){
	   $Stamp = strtotime( $Str );
	   $Month = date( 'm', $Stamp );
	   $Day   = date( 'd', $Stamp );
	   $Year  = date( 'Y', $Stamp );
	
	  return checkdate( $Month, $Day, $Year );
  }
}
