<?php
$l=OC_L10N::get('calendar');
require_once __DIR__ . '/bootstrap.php';



OCP\App::addNavigationEntry( array(
  'id' => 'calendar_index',
  'order' => 10,
  'href' => OCP\Util::linkTo( 'calendar', 'index.php' ),
  'icon' => OCP\Util::imagePath( 'calendar', 'calendar.svg' ),
  'name' => $l->t('Calendar')));
  
OCP\Util::addscript('calendar','loader');
OCP\Util::addStyle('3rdparty/miniColors', 'jquery.miniColors');
OCP\Util::addscript('3rdparty/miniColors', 'jquery.miniColors.min');

OC_Search::registerProvider('OCA\Calendar\SearchProvider');
OCP\Util::addscript('calendar','alarm');

OCP\Backgroundjob::addRegularTask('OCA\Calendar\Jobs', 'run');

//\OC_BackgroundJob_RegularTask::register('OC_Calendar_Jobs', 'checkAlarm');
// used for LiveReminderCheck

