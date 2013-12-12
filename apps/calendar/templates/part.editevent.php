<div id="event" title="<?php p($l->t("Edit an event"));?>">
	<form id="event_form">
		<input type="hidden" name="id" value="<?php p($_['eventid']) ?>">
		<input type="hidden" name="lastmodified" value="<?php p($_['lastmodified']) ?>">
		<input type="hidden" name="choosendate" id="choosendate" value="<?php p($_['choosendate']) ?>">
<?php print_unescaped($this->inc("part.eventform")); ?>
	<div style="text-align: center;color: #FF1D1D;" id="errorbox"></div>
	<div id="actions" style="border-top:1px solid #bbb;height:50px;line-height:50px;min-width:480px;">
		
		<div  class="button-group" style="margin: 10px 3px;width:60%; float:left;">
		 <?php 
		       $DeleteButtonTitle=$l->t("Delete");
		        if($_['addSingleDeleteButton'] ) {
		          	$DeleteButtonTitle=$l->t("Delete Serie");
		          }
		 ?> 
		 	
		<input type="button" class="submit  button" id="editEvent-submit" value="<?php p($l->t("Save"));?>" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'ajax/event/edit.php')) ?>">
		<?php if($_['permissions'] & OCP\PERMISSION_DELETE) { ?>
		<input type="button" class="submit  button" id="editEvent-delete"  name="delete" value="<?php p($DeleteButtonTitle);?>" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'ajax/event/delete.php')) ?>">
		   <?php if($_['addSingleDeleteButton'] ) { ?>
					<input type="button" class="submit  button" id="editEvent-delete-single"  name="delete" value="Event l&ouml;schen" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'ajax/event/delete-single.php')) ?>">
			<?php } ?>
		<?php } ?>
		</div>
		<div  class="button-group" style="right:1%; margin: 10px 3px;float:right;">
		<input type="button" class="submit button" id="editEvent-export"  name="export" value="<?php p($l->t("Export"));?>" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'export.php')) ?>?eventid=<?php p($_['eventid']) ?>">
		<input type="button" class="button" id="closeDialog"  " value="<?php p($l->t("Ready"));?>" />

	   </div>
	
	</div>
	</form>
</div>
