<div id="edit-event" style="border:none;">
<form name="taskForm" id="taskForm" action=" ">
    <input type="hidden" name="tid" id="taskid" value="<?php p($_['id']); ?>" />
     <input type="hidden" name="cid" value="<?php p($_['calId']); ?>" />
     <input type="hidden" name="orgid" value="<?php p($_['orgId']); ?>" />
    <input type="hidden" name="hiddenfield" value="" />
    	
<ul>
	<li><a href="#tabs-1"><?php p($l->t('Task')); ?></a></li>
	<li><a href="#tabs-2"><?php p($l->t('Reminder')); ?></a></li>
	<?php if($_['permissions'] & OCP\PERMISSION_SHARE) { ?>
	<li><a href="#tabs-3"><?php p($l->t('Share')); ?></a></li>
	
	<?php } ?>
</ul>
<div id="tabs-1">
    <span class="labelLeft"><?php p($l->t('Task')); ?></span><input style="width:60%;" type="text" name="tasksummary" id="tasksummary" value="<?php p($_['vtodo']->summary); ?>" />
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
      <span class="labelLeft"><?php p($l->t('List')); ?></span>
     <input style="width:60%;" type="text" name="taskcategories" id="taskcategories" value="<?php p($_['vtodo']->categories); ?>" />
    <br class="clearing"  />
       <span class="labelLeft"><?php p($l->t('Calendar')); ?></span><?php print_unescaped($_['aktiveWorker']); ?>
    <br class="clearing"  />
    <span class="labelLeft"><?php p($l->t('Location')); ?></span>
   <input style="width:40%;" type="text" name="tasklocation" id="tasklocation" value="<?php p(stripslashes($_['vtodo']->location)); ?>" />
    <br class="clearing"  />
    <span class="labelLeft"><?php p($l->t('Due')); ?>  <?php p($l->t('on')); ?></span> 
    <input type="text" name="sWV" id="sWV" class="textField"  size="10" value="<?php p($_['TaskDate']); ?>" />
    <input type="text" name="sWV_time" id="sWV_time" class="textField" style="width:50px;" size="5" value="<?php p($_['TaskTime']); ?>" />
	
    <br class="clearing"  />
    <span class="labelLeft"><?php p($l->t('Notice')); ?></span><textarea name="noticetxt" class="textClass pflicht" style="width:60%;height:60px;"><?php p($_['vtodo']->description); ?></textarea>
     <br class="clearing"  />
    
   </div>
   	<div id="tabs-2">
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
  <?php if($_['permissions'] & OCP\PERMISSION_SHARE) { ?>
   <div id="tabs-3">
  
   	
   	<label for="sharewith"><?php p($l->t('Share with:')); ?></label>
<input type="text" id="sharewith" data-item-source="<?php p($_['id']); ?>" /><br />

<strong><?php p($l->t('Shared with')); ?></strong>
<ul class="sharedby todolist">
<?php foreach($_['vtodosharees'] as $sharee): ?>
	<li data-share-with="<?php p($sharee['share_with']); ?>"
		data-item="<?php p($_['id']); ?>"
		data-item-type="todo"
		data-permissions="<?php p($sharee['permissions']); ?>"
		data-share-type="<?php p($sharee['share_type']); ?>">
		<?php p($sharee['share_with'] . ' (' . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_USER ? 'user' : 'group'). ') '); ?>
		<span class="shareactions">
			<?php p($l->t('can edit')); ?> <input class="update" type="checkbox" <?php p(($sharee['permissions'] & OCP\PERMISSION_UPDATE?'checked="checked"':''))?>
				title="<?php p($l->t('Editable')); ?>">
			<?php p($l->t('share')); ?> <input class="share" type="checkbox" <?php p(($sharee['permissions'] & OCP\PERMISSION_SHARE?'checked="checked"':''))?>
				title="<?php p($l->t('Shareable')); ?>">
			<?php p($l->t('delete')); ?> <input class="delete" type="checkbox" <?php p(($sharee['permissions'] & OCP\PERMISSION_DELETE?'checked="checked"':''))?>
				title="<?php p($l->t('Deletable')); ?>">
			<img style="cursor: pointer;" src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')); ?>" class="svg action unshare"
				title="<?php p($l->t('Unshare')); ?>">
		</span>
	</li>
<?php endforeach; ?>
</ul>
<?php if(!$_['vtodosharees']) {
	$nobody = $l->t('Nobody');
	print_unescaped('<div id="sharedWithNobody">' . OC_Util::sanitizeHTML($nobody) . '</div>');
} ?>
<br />
<br />
<input type="text" name="inviteEmails" id="inviteEmails" placeholder="<?php p($l->t('Email event to person')); ?>" style="float:left;width:260px;" value="" />
<button id="sendemailbutton" style="float:left;" class="button" data-eventid="<?php p($_['id']);?>"><?php p($l->t("Send Email")); ?></button>
<br /><br style="clear:both;" />

   	</div>
   	<?php } ?>  
 </form>
   	 
 </div>  	       