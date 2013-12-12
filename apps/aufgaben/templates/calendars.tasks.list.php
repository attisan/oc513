
<h3>Aufgaben</h3>
<ul id="taskList">
	<?php if($_['taskOutPutbyTime']['today']) { ?>
			<li><b><?php p($l->t('today')); ?></b></li>
			<?php print_unescaped($_['taskOutPutbyTime']['today']); ?>
	<?php } ?>  
	<?php if($_['taskOutPutbyTime']['tomorrow']) { ?>
			<li><b><?php p($l->t('tomorrow')); ?></b></li>
			<?php print_unescaped($_['taskOutPutbyTime']['tomorrow']); ?>
	<?php } ?>  
	<?php if($_['taskOutPutbyTime']['week']) { ?>
			<li><b><?php p($l->t('This Week')); ?></b></li>
			<?php print_unescaped($_['taskOutPutbyTime']['week']); ?>
	<?php } ?>  	
	<?php if($_['taskOutPutbyTime']['commingsoon']) { ?>
			<li><b><?php p($l->t('Coming soon')); ?></b></li>
			<?php print_unescaped($_['taskOutPutbyTime']['commingsoon']); ?>
	<?php } ?>  	
	<?php if($_['taskOutPutbyTime']['missed']) { ?>
			<li><b><?php p($l->t('Missed')); ?></b></li>
			<?php print_unescaped($_['taskOutPutbyTime']['missed']); ?>
	<?php } ?>  	
	<?php if($_['taskOutPutbyTime']['notermin']) { ?>
			<li><b><?php p($l->t('Without Time')); ?></b></li>
			<?php print_unescaped($_['taskOutPutbyTime']['notermin']); ?>
	<?php } ?>		
	</ul>

