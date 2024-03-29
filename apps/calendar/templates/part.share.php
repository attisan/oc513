<?php
$calid = isset($_['calendar']) ? $_['calendar'] : null;
$eventid = isset($_['eventid']) ? $_['eventid'] : null;

$calsharees = array();
$eventsharees = array();

$sharedwithByCalendar = OCP\Share::getItemShared('calendar', $calid);
$sharedwithByEvent = OCP\Share::getItemShared('event', $eventid);

if(is_array($sharedwithByCalendar)) {
	foreach($sharedwithByCalendar as $share) {
		if($share['share_type'] == OCP\Share::SHARE_TYPE_USER || $share['share_type'] == OCP\Share::SHARE_TYPE_GROUP) {
			$calsharees[] = $share;
		}
	}
}
if(is_array($sharedwithByEvent)) {
	foreach($sharedwithByEvent as $share) {
		if($share['share_type'] == OCP\Share::SHARE_TYPE_USER || $share['share_type'] == OCP\Share::SHARE_TYPE_GROUP) {
			$eventsharees[] = $share;
		}
	}
}
?>

<label for="sharewith"><?php p($l->t('Share with:')); ?></label>
<input type="text" id="sharewith" data-item-source="<?php p($eventid); ?>" /><br />
<input type="hidden" id="haveshareaction" value="0" />
<strong><?php p($l->t('Shared with')); ?></strong>
<ul class="sharedby eventlist">
<?php foreach($eventsharees as $sharee): ?>
	<li data-share-with="<?php p($sharee['share_with']); ?>"
		data-item="<?php p($eventid); ?>"
		data-item-type="event"
		data-permissions="<?php p($sharee['permissions']); ?>"
		data-share-type="<?php p($sharee['share_type']); ?>">
		<?php p($sharee['share_with'] . ' (' . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_USER ? 'user' : 'group'). ')'); ?>
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
<?php if(!$eventsharees) {
	$nobody = $l->t('Nobody');
	print_unescaped('<div id="sharedWithNobody">' . OC_Util::sanitizeHTML($nobody) . '</div>');
} ?>
<br />

<strong><?php p($l->t('Shared via calendar')); ?></strong>
<ul class="sharedby calendarlist">
<?php foreach($calsharees as $sharee): ?>
	<li data-share-with="<?php p($sharee['share_with']); ?>"
		data-item="<?php p($calid); ?>"
		data-item-type="calendar"
		data-permissions="<?php p($sharee['permissions']); ?>"
		data-share-type="<?php p($sharee['share_type']); ?>">
		<?php p($sharee['share_with'] . ' (' . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_USER ? 'user' : 'group'). ')'); ?>
		<span class="shareactions">
			<input class="update" type="checkbox" <?php p(($sharee['permissions'] & OCP\PERMISSION_UPDATE?'checked="checked"':''))?>
				title="<?php p($l->t('Editable')); ?>">
			<input class="share" type="checkbox" <?php p(($sharee['permissions'] & OCP\PERMISSION_SHARE?'checked="checked"':''))?>
				title="<?php p($l->t('Shareable')); ?>">
			<input class="delete" type="checkbox" <?php p(($sharee['permissions'] & OCP\PERMISSION_DELETE?'checked="checked"':''))?>
				title="<?php p($l->t('Deletable')); ?>">
			<img src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')); ?>" class="svg action delete"
				title="<?php p($l->t('Unshare')); ?>">
		</span>
	</li>
<?php endforeach; ?>
</ul>
<br />
<div style="max-width:400px;">
<?php p($l->t('NOTE: Actions on events shared via calendar will affect the entire calendar sharing.')); ?>
</div><br />
<input type="text" name="inviteEmails" id="inviteEmails" placeholder="<?php p($l->t('Email event to person')); ?>" style="float:left;width:260px;" value="" />
<button id="sendemailbutton" style="float:left;" class="button" data-eventid="<?php p($eventid);?>"><?php p($l->t("Send Email")); ?></button>
<br /><br style="clear:both;" />


