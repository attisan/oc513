<div id="new-event" style="border:none;">
<form name="taskForm" id="taskForm" action=" ">	
<input type="hidden" name="hiddenfield" value="" />	
<ul>
	<li><a href="#tabs-1"><?php p($l->t('Task')); ?> </a></li>
	<li><a href="#tabs-3"><?php p($l->t('Reminder')); ?></a></li>
</ul>
<div id="tabs-1">
  
    
     <span class="labelLeft"><?php p($l->t('Task')); ?> </span><input style="width:60%;" type="text" name="tasksummary" id="tasksummary" value="<?php p($_['vtodo']->summary); ?>" />
    <br class="clearing"  />
     <span class="labelLeft"><?php p($l->t('Priority')); ?></span><?php print_unescaped($_['priorityOptions']); ?>
    <br class="clearing"  />
    <span class="labelLeft"><?php p($l->t("Access Class"));?></span>
				<select style="width:140px;" name="accessclass">
					<?php
					print_unescaped(OCP\html_select_options($_['access_class_options'], $_['accessclass']));
					?>
				</select>
	<br class="clearing"  />
      <span class="labelLeft"><?php p($l->t('List')); ?> </span>
     <input style="width:60%;" type="text" name="taskcategories" id="taskcategories" value="" />
       <span class="labelLeft"><?php p($l->t('Calendar')); ?></span><?php print_unescaped($_['aktiveWorker']); ?>
    <br class="clearing"  />
    <span class="labelLeft"><?php p($l->t('Location')); ?> </span>
   <input style="width:40%;" type="text" name="tasklocation" id="tasklocation" value="" />
    <br class="clearing"  />
    <span class="labelLeft"><?php p($l->t('Due')); ?>  <?php p($l->t('on')); ?> </span> 
    <input type="text" name="sWV" id="sWV" class="textField"  size="10" value="" />
    <input type="text" name="sWV_time" id="sWV_time" class="textField" style="width:50px;" size="5" value="" />
	
    <br class="clearing"  />
    <span class="labelLeft"><?php p($l->t('Notice')); ?></span><textarea name="noticetxt" class="textClass pflicht" style="width:60%;height:60px;"></textarea>
     <br class="clearing"  />
    
   </div>
<div id="tabs-3">
	<table style="width:100%">
			<tr>
				<th width="95"><?php p($l->t("Reminder"));?>:</th>
				<td>
				<select id="reminder" name="reminder">
					<?php
					print_unescaped(OCP\html_select_options($_['reminder_options'], $_['reminder']));
					?>
				</select></td>
				<td>
					&nbsp;
					</td>
			</tr>
			 </table>
				<div id="reminderTable">
				<table style="width:100%">
			   <tr>
					<th width="95">&nbsp;</th>
					<td>
						<input type="number" min="1" max="365" maxlength="3" style="width:40px; float:left;" name="remindertimeinput" id="remindertimeinput" value="<?php p($_['remindertimeinput']); ?>" />
						<select id="remindertimeselect" name="remindertimeselect">
							<?php
							print_unescaped(OCP\html_select_options($_['reminder_time_options'], $_['remindertimeselect']));
							?>
						</select>
						
					</td>
				</tr>
				</table>
				</div>
				<div id="reminderdateTable">
				<table style="width:100%">
				<tr>
					<th width="95"><?php p($l->t("Date"));?>:</th>
					<td>
						<input type="text" value="<?php p($_['reminderdate']);?>" name="reminderdate" id="reminderdate">
						&nbsp;&nbsp;
						<input type="time" style="width:50px;" value="<?php p($_['remindertime']);?>" name="remindertime" id="remindertime">
					</td>
				</tr>
				</table>
		      </div>
				<div id="reminderemailinputTable">
				<table style="width:100%">
				<tr>
					<th width="95"><?php p($l->t("Email"));?>:</th>
					<td>
						<input type="text" name="reminderemailinput" id="reminderemailinput" value="<?php p($_['reminderemailinput']); ?>" />
						
					</td>
				</tr>
				</table>
		      </div>
	
</div>
 </form>
 </div>  	       