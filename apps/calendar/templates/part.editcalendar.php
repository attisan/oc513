<?php
/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
?>
<td id="<?php p($_['new'] ? 'new' : 'edit') ?>calendar_dialog" title="<?php p($_['new'] ? $l->t("New calendar") : $l->t("Edit calendar")); ?>" colspan="6">
<table width="100%" style="border: 0;">
<tr>
	<th><?php p($l->t('Displayname')) ?></th>
	<td>
		<input id="displayname_<?php p($_['calendar']['id']) ?>" type="text" value="<?php p($_['calendar']['displayname']) ?>">
	</td>
</tr>
<?php if (!$_['new']): ?>
<tr>
	<td></td>
	<td>
		<input id="edit_active_<?php p($_['calendar']['id']) ?>" type="checkbox"<?php p($_['calendar']['active'] ? ' checked="checked"' : '') ?>>
		<label for="edit_active_<?php p($_['calendar']['id']) ?>">
			<?php p($l->t('Active')) ?>
		</label>
	</td>
</tr>
<?php endif; ?>
<tr>
	<th><?php p($l->t('Calendar color')) ?></th>
	<td>
	  
	<input type="hidden" class="minicolor" id="calendarcolor_<?php p($_['calendar']['id']) ?>" value="<?php print_unescaped($_['calendar']['calendarcolor']) ?>" /> 
		
	</td>
</tr>
</table>
<input style="float: left;"  id="editCalendar-submit" type="button" data-id="<?php p($_['new'] ? "new" : $_['calendar']['id']) ?>" value="<?php p($_['new'] ? $l->t("Save") : $l->t("Submit")); ?>">
<input style="float: left;"  id="editCalendar-cancel"  type="button" data-id="<?php p($_['new'] ? "new" : $_['calendar']['id']) ?>" value="<?php p($l->t("Cancel")); ?>">
</td>
