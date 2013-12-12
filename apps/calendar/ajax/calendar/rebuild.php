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

$calendars = OCA\Calendar\Calendar::allCalendars(OCP\USER::getUser(), false);
$output='<div id="leftcontentInner">
				<h3>Kalender </h3>
				<ul id="calendarList">';

   foreach($calendars as $calInfo){
	   	$checked=$calInfo['active'] ? ' checked="checked"' : '';
	    $displayName=$calInfo['displayname'];
         if($calInfo['userid'] != OCP\USER::getUser()){
  	        $displayName=$calInfo['displayname'].' (' . OCA\Calendar\App::$l10n->t('by') . ' ' .$calInfo['userid'].')';
        }
	   	$checkBox='<input class="activeCalendarNav" data-id="'.$calInfo['id'].'" style="float:left;margin-right:5px;" id="edit_active_'.$calInfo['id'].'" type="checkbox" '.$checked.' />';
	   	$output.='<li class="calListen">'.$checkBox.'<div class="colCal" style="background-color:'.$calInfo['calendarcolor'].'">&nbsp;</div> '.$displayName.'</li>';
  }
   
   $output.='</ul>
	          </div>
	     <div id="datepickerNav"></div>';
		 
  print $output;
