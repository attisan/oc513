<div id="controls">
	<div class="button-group" style="float:left;margin: 5px 3px;">
	<button class="button button-info" id="addnewtask"><?php p($l->t('New Task')); ?></button>
	</div>
	<div id="taskmanagertitle"><?php p($l->t('All')); ?>  <?php p($l->t('Tasks')); ?> </div>
</div>
<br style="clear:both;" />
<div id="tasks_lists">
	
	<?php print_unescaped($_['taskList']); ?>
	
</div>
<div id="tasks_list">

</div>
<div id="dialog" title="Basic dialog"></div>
<div id="dialogmore" title="Basic dialog"></div>
<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('aufgaben/js', 'categories.php')) ?>"></script>