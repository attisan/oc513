<div id="event" title="<?php p($l->t("Create a new event"));?>">
	<form id="event_form">
<?php print_unescaped($this->inc("part.eventform")); ?>
	<div style="text-align: center;color: #FF1D1D;" id="errorbox"></div>
	<div id="actions" style="border-top:1px solid #bbb;">
		<div  class="button-group" style="margin: 10px 3px;width:60%; float:left;">
		<input type="button" id="submitNewEvent" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'ajax/event/new.php')); ?>" class="submit button"  value="<?php p($l->t("Save"));?>">
       
	</div>
	<div  class="button-group" style="margin: 10px 3px;right:1%; float:right;">
		<input type="button" class="button" id="closeDialog"  " value="<?php p($l->t("Ready"));?>" />	
	</div>
	</div>
	</form>
</div>
