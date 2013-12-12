
	<ul id="calendarList">
		<li style="font-weight:bold;"><?php p($l->t('Active Calendars')); ?></li>
	<?php 
	   $mySharees=OCA\Calendar\Object::getCalendarSharees();
	   foreach($_['calendars'] as $calInfo){
	    $rightsOutput='';
		   $share='';
		 if($mySharees[$calInfo['id']]) $share='<img class="svg" style="margin-top:3px;margin-bottom:-3px;margin-right:2px;" src="'.OCP\Util::imagePath('core', 'actions/shared.svg').'" title="shared">'; 	
		$displayName='<span class="descr">'.$share.$calInfo['displayname'].'</span>';
		
         if($calInfo['userid'] != OCP\USER::getUser()){
  	        $rightsOutput=OCA\Calendar\Calendar::permissionReader($calInfo['permissions']);	
  	        $displayName='<span class="showToolTip descr" title="'.$rightsOutput.'">'.$calInfo['displayname'].' (' . OCA\Calendar\App::$l10n->t('by') . ' ' .$calInfo['userid'].')</span>';
        }
		 $countCalEvents=0;
		 if($_['aCountCalEvents'][(string)$calInfo['id']]!='') $countCalEvents=$_['aCountCalEvents'][(string)$calInfo['id']];
		
		 
	   	print_unescaped('<li class="calListen" data-id="'.$calInfo['id'].'" title="Kalendar '.$calInfo['displayname'].'"><span class="colorCal" style="background-color:'.$calInfo['calendarcolor'].'">&nbsp;</span> '.$displayName.'<span class="iCount">'.$countCalEvents.'</span></li>');
	   }
	   
	   
	 ?>
	 </ul>
	 <br style="clear:both;" /><br />
	 <ul id="taskstime">
	 	<li class="taskstimerow" data-id="today" title="<?php p($l->t('Tasks')); ?>  am <?php p($_['aTaskTime']['today']); ?>"><span class="descr"><?php p($l->t('Tasks')); ?> <?php p($l->t('today')); ?></span><span class="iCount"><?php p($_['tasksCount']['today']); ?></span></li>
	 	<li class="taskstimerow" data-id="tomorrow" title="<?php p($l->t('Tasks')); ?>  am <?php p($_['aTaskTime']['tomorrow']); ?>"><span class="descr"><?php p($l->t('Tasks')); ?>  <?php p($l->t('tomorrow')); ?></span><span class="iCount"><?php p($_['tasksCount']['tomorrow']); ?></span></li>
	 	<li class="taskstimerow" data-id="actweek" title="<?php p($l->t('Tasks')); ?>   <?php p($_['aTaskTime']['actweek']); ?>"><span class="descr"><?php p($l->t('This Week')); ?></span><span class="iCount"><?php p($_['tasksCount']['actweek']); ?></span></li>
       	<li class="taskstimerow" data-id="comingsoon" title="<?php p($l->t('Coming soon')); ?>"><span class="descr"><?php p($l->t('Coming soon')); ?> </span><span class="iCount"><?php p($_['tasksCount']['comingsoon']); ?></span></li>
	 	<li class="taskstimerow" data-id="withoutdate" title="<?php p($l->t('Tasks')); ?>  <?php p($l->t('Without Time')); ?>"><span class="descr"><?php p($l->t('Without Time')); ?></span><span class="iCount"><?php p($_['tasksCount']['withoutdate']); ?></span></li>
	 	<li class="taskstimerow" data-id="missedactweek" title="<?php p($l->t('Missed')); ?> <?php p($l->t('Tasks')); ?>"><span class="descr"><?php p($l->t('Missed')); ?> <?php p($l->t('Tasks')); ?></span><span class="iCount"><?php p($_['tasksCount']['missedactweek']); ?></span></li>
	 	</ul>
	 <br style="clear:both;" /><br />
	  <ul id="taskssum">
	 	<li class="calListen" data-id="0" title="<?php p($l->t('All')); ?> <?php p($l->t('Tasks')); ?> "><span class="descr"><?php p($l->t('All')); ?> <?php p($l->t('Tasks')); ?> </span><span class="iCount"><?php p($_['tasksCount']['alltasks']); ?></span></li>
	 	<li class="taskstimerow" data-id="alltasksdone" title="<?php p($l->t('Completed')); ?> <?php p($l->t('Tasks')); ?>"><span class="descr"><?php p($l->t('Completed')); ?> <?php p($l->t('Tasks')); ?> </span><span class="iCount"><?php p($_['tasksCount']['alltasksdone']); ?></span></li>
	 	<li class="taskstimerow" data-id="sharedtasks" title="<?php p($l->t('Shared')); ?> <?php p($l->t('Tasks')); ?>"><span class="descr"><?php p($l->t('Shared')); ?> <?php p($l->t('Tasks')); ?> </span><span class="iCount"><?php p($_['tasksCount']['sharedtasks']); ?></span></li>

	 </ul>

</div>