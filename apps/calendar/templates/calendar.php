
<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('calendar/js', 'l10n.php'));?>"></script>

<div id="loading">
<img style="position:relative;left:50%;top:50%; margin-left:-110px; width:220px; height:19px;"  src="<?php print_unescaped(OCP\Util::imagePath('core', 'ajax-loader.gif')); ?>" />
</div>
<div id="notification" style="display:none;"></div>
<div id="controls">
	<div class="leftControls">
	<div class="button-group" style="margin: 5px 3px;">	
		<button id="calendarnavActive" class="button <?php p($_['buttonCalAktive']) ?>" style="padding-bottom:2px;"><img  class="svg" src="<?php print_unescaped(OCP\Util::imagePath('calendar', 'calendar_small.svg')); ?>" height="16" /></button>	

	<button class="button"  id="datecontrol_today"><?php p($l->t('Today'));?></button>
	
	</div>
	
	</div>
	<div class="centerControls">
		
		<div id="view" class="button-group" style="left:33%;margin: 5px 3px;">
		<button class="button"  id="datecontrol_left">&nbsp;&lt;&nbsp;</button>		
		<button class="button"  id="onedayview_radio">Tag</button>
		<button class="button"  id="threedayview_radio">3-Tage</button>	
		<button class="button"  id="oneweekworkview_radio">Arbeitswoche</button>			
		<button class="button"  id="oneweekview_radio"><?php p($l->t('Week'));?></button>
	  <button class="button" id="onemonthview_radio"><?php p($l->t('Month'));?></button>
	  <button class="button" id="listview_radio"><?php p($l->t('List'));?></button>
	  <button class="button"  id="datecontrol_right">&nbsp;&gt;&nbsp;</button>			
	  </div>
  
	</div>
	<div class="rightControls">
	<div class="button-group" style="margin: 5px 3px;float:right;">	
		<button id="choosecalendarGeneralsettings" class="button" title="<?php p($l->t('Settings')); ?>" style="padding-bottom:2px;"><img height="16"  class="svg" src="<?php print_unescaped(OCP\Util::imagePath('core', 'actions/settings.svg')); ?>" alt="<?php p($l->t('Settings')); ?>" /></button>
		<button id="tasknavActive" class="button <?php p($_['buttonTaskAktive']) ?>" style="padding-bottom:2px;"><img  class="svg" src="<?php print_unescaped(OCP\Util::imagePath('calendar', 'task_small.svg')); ?>" height="16" /></button>	

	</div>
	</div>	
	
	
	
	
</div>

<div id="leftcontent" <?php print_unescaped($_['isHiddenCal']); ?>>
	<?php if($_['leftnavAktiv']==='true') {?>
	<div id="leftcontentInner">
	<h3>Kalender </h3>
	<ul id="calendarList">
		
	<?php 
	    $mySharees=OCA\Calendar\Object::getCalendarSharees();
		 
	   foreach($_['calendars'] as $calInfo){
	   	
		$rightsOutput='';
		  $share='';
		 if($mySharees[$calInfo['id']]) $share='<img class="svg" style="margin-top:3px;margin-bottom:-3px;margin-right:2px;" src="'.OCP\Util::imagePath('core', 'actions/shared.svg').'" title="shared">'; 	
		   $displayName='<span class="descr">'.$share.$calInfo['displayname'].'</span>';
		
         if($calInfo['userid'] != \OCP\USER::getUser()){
  	        $rightsOutput=OCA\Calendar\Calendar::permissionReader($calInfo['permissions']);	
  	        $displayName='<span class="toolTip" title="'.$rightsOutput.'">'.$calInfo['displayname'].' (' . OCA\Calendar\App::$l10n->t('by') . ' ' .$calInfo['userid'].')</span>';
            
		 }
	
	   	$checked=$calInfo['active'] ? ' checked="checked"' : '';
	   	$checkBox='<input class="activeCalendarNav" data-id="'.$calInfo['id'].'" style="float:left;margin-right:5px;" id="edit_active_'.$calInfo['id'].'" type="checkbox" '.$checked.' />';
	   	print_unescaped('<li class="calListen">'.$checkBox.'<div class="colCal" style="background-color:'.$calInfo['calendarcolor'].'">&nbsp;</div> '.$displayName.'</li>');
	   }
	 ?>
	 </ul>
	 </div>
	 <div id="datepickerNav"></div>
	 <?php } ?>
</div>


<div id="fullcalendar"></div>

	<div id="rightCalendarNav" <?php print_unescaped($_['isHidden']); ?>>
		<?php if($_['rightnavAktiv']==='true') {?>
		
		<?php print_unescaped($_['taskOutput']); ?>
		<?php } ?>
	</div>
<div id="dialog_message" style="width:0;height:0;top:0;left:0;display:none;"></div>	
<div id="dialog" style="width:0;height:0;top:0;left:0;display:none;"></div>
<div id="dialog_holder" style="width:0;height:0;top:0;left:0;display:none;"></div>
<div id="appsettings" class="popup topright hidden"></div>