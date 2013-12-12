<div id="event" title="<?php p($l->t("View an event"));?>">
	<input type="hidden" id="eventid" name="eventid" value="<?php p($_['eventid']) ?>">
	<input type="hidden" name="lastmodified" value="<?php p($_['lastmodified']) ?>">
	<input type="hidden" name="choosendate" id="choosendate" value="<?php p($_['choosendate']) ?>">
	
<ul>
	<li><a href="#tabs-1"><?php p($l->t('Eventinfo')); ?></a></li>
	
	<?php if($_['eventid'] != 'new' && $_['permissions'] & OCP\PERMISSION_SHARE) { ?>
	<li><a href="#tabs-5"><?php p($l->t('Share')); ?></a></li>
	<?php } ?>
	<!--<li><a href="#tabs-3"><?php p($l->t('Alarm')); ?></a></li>
	<li><a href="#tabs-4"><?php p($l->t('Attendees')); ?></a></li>-->
</ul>
<div id="tabs-1">
	<table width="100%">
		<tr>
			<td style="font-size:16px; font-weight:bold;line-height:22px;">
				<?php p(isset($_['title']) ? $_['title'] : '') ?>
			</td>
		</tr>
	</table>
	
	
	<table width="100%">
		<?php if($_['allday']) { ?>
		<tr>
			<th class="leftDescr"></th>
			<td>
				<input type="checkbox"<?php if($_['allday']) {print_unescaped('checked="checked"');} ?> id="allday_checkbox" name="allday" disabled="disabled">
				<?php p($l->t("All Day Event"));?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th class="leftDescr"><?php p($l->t("From"));?></th>
			<td>
				<?php p($_['startdate']);?>
				&nbsp;&nbsp;
				<?php p($_['starttime']);?>
			</td>
		</tr>
		<tr>
			<th class="leftDescr"><?php p($l->t("To"));?></th>
			<td>
				<?php p($_['enddate']);?>
				&nbsp;&nbsp;
				<?php p($_['endtime']);?>
			</td>
		</tr>
	</table>
	
		<?php if($_['location']!=''){ ?>
		<table>
			
			<tr>
				<th class="leftDescr"><?php p($l->t("Location"));?></th>
				<td>
					<a id="showLocation" target="_blank" href="http://maps.google.com/maps?q=<?php p(isset($_['location']) ? $_['location'] : '') ?>&amp;z=20" data-geo="data-geo"><?php p(isset($_['location']) ? $_['location'] : '') ?></a>
					
				</td>
			</tr>
		</table>
	<?php } ?>
	 <?php if($_['aAlarm']!=''){ ?>
		<table>
			<tr>
				<th class="leftDescr" style="vertical-align: top;"><?php p($l->t("Reminder"));?></th>
				<td>
					 <?php p($_['aAlarm']['action']);?><br />
					 <?php p($_['aAlarm']['timeOptions']);?><br />
					 <?php if($_['aAlarm']['email']!='') p($_['aAlarm']['email']);?>
				</td> 
			</tr>
		</table>
	<?php } ?>
      <?php if($_['repeat']!=='doesnotrepeat'){?>
		<table>
			
			<tr>
				<th class="leftDescr" style="vertical-align: top;"><?php p($l->t("Repeating"));?></th>
				<td>
					 <?php p($_['repeatInfo']['infoRepeat']);?><br />
					 <?php p($_['repeatInfo']['interval']);?>
					  <?php p($l->t("End"));?>: <?php p($_['repeatInfo']['end']);?><br />
				</td> 
			</tr>
		</table>
				<?php if($_['exDate']!=''){ ?>
				<table>
					<tr>
						<th class="leftDescr" style="vertical-align: top;"><?php p($l->t("Exception"));?></th>
						<td>
				<ul>
		         <?php foreach($_['exDate'] as $key => $value): ?>
				   <li class="exdatelistrow" data-exdate="<?php p($key); ?>"><?php p($value); ?> <img style="cursor:pointer;margin-top:2px;margin-bottom:-2px;" class="svg" src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')) ?>"></li>
				   	<?php endforeach; ?>
		           </ul>
				  </td> 
					</tr>
				</table>
				<?php } ?>
		<?php } ?>
		
		
		   <?php if($_['description']!=''){ ?>
		<table>
			<tr>
				<th class="leftDescr" style="vertical-align: top;"><?php p($l->t("Notice"));?></th>
				<td>
					<?php p(isset($_['description']) ? $_['description'] : '') ?>
				 </td>	
					</tr>
		</table>
		 <?php } ?>
		 <br />
		<table width="100%">
		<tr>
			<th class="leftDescr"><?php p($l->t("Calendar"));?></th>
			<td>
			<?php
			$calendar =OCA\Calendar\App::getCalendar($_['calendar'], false, false);
			p($calendar['displayname']) . ' ' . $l->t('of') . ' ' . $calendar['userid'];
			?>
			<input type="hidden" name="calendar" id="shareCalid" value="<?php p($_['calendar_options'][0]['id']) ?>">

			</td>
			
			
		
			<?php if($_['accessclass'] !='') { ?>
		
			<th class="leftDescr"><?php p($l->t("Access Class"));?></th>
			<td>
			  
				<?php
				p($_['access_class_options'][$_['accessclass']]);
				?>
			
			</td>
		</tr>
		<?php } ?>
		
		<?php
				if(count($_['categories']) > 0 && $_['categories']!='' ) { ?>
		<tr>
			<th class="leftDescr"><?php p($l->t("Category"));?></th>
			<td colspan="3">
				<?php
				
					print_unescaped('<ul>');
					if(is_array($_['categories'])){
						foreach($_['categories'] as $categorie) {
							print_unescaped('<li>' . OC_Util::sanitizeHTML($categorie) . '</li>');
						}
					}else{
						print_unescaped('<li>' . OC_Util::sanitizeHTML($_['categories']) . '</li>');
					}
					print_unescaped('</ul>');
				
				?>
			</td>
			</tr>
			<?php } ?>
			
	</table>
	  
	</div>

<!--<div id="tabs-3">//Alarm</div>
<div id="tabs-4">//Attendees</div>-->
<?php if($_['eventid'] != 'new' && $_['permissions'] & OCP\PERMISSION_SHARE) { ?>
<div id="tabs-5">
	<?php if($_['eventid'] != 'new') { print_unescaped($this->inc('part.share')); } ?>
</div>
<?php } ?>
<div id="actions" style="border-top:1px solid #bbb;">
<div  class="button-group" style="margin: 10px 3px;width:60%; float:left;">

<?php if($_['permissions'] & OCP\PERMISSION_UPDATE) { ?>
	<button class="button" id="editEventButton"><?php p($l->t("Edit"));?></button>
	<?php } ?>
	<?php if($_['permissions'] & OCP\PERMISSION_DELETE) { ?>
		<button class="button" id="editEvent-delete"  name="delete" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'ajax/event/delete.php')) ?>"><?php p($l->t("Delete"));?></button>
		   
		<?php } ?> 
	<?php if($_['bShareOnlyEvent'] ) { ?>	 
			<button class="button" id="editEvent-add"  name="addSharedEvent" data-link="<?php print_unescaped(OCP\Util::linkTo('calendar', 'ajax/event/addsharedevent.php')) ?>"><?php p($l->t("Add"));?></button>
	
	<?php } ?> 
</div>	
<div  class="button-group" style="right:1%; margin: 10px 3px;float:right;">
	
		<button class="button" id="closeDialog"><?php p($l->t("Ready"));?></button>
	   </div>
</div>	   
</div>