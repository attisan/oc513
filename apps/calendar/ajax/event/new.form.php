<?php
/**
 * Copyright (c) 2011 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */



if(!OCP\User::isLoggedIn()) {
	OCP\User::checkLoggedIn();
}
OCP\JSON::checkAppEnabled('calendar');
$l=OC_L10N::get('calendar');

if (!isset($_POST['start'])) {
	OCP\JSON::error();
	die;
}
$start = $_POST['start'];
$end = $_POST['end'];
$allday = $_POST['allday'];

if (!$end) {
	$duration = OCP\Config::getUserValue( OCP\USER::getUser(), 'calendar', 'duration', '60');
	$end = $start + ($duration * 60);
}
$start = new DateTime('@'.$start);
$end = new DateTime('@'.$end);
$timezone = OCA\Calendar\App::getTimezone();
$start->setTimezone(new DateTimeZone($timezone));
$end->setTimezone(new DateTimeZone($timezone));

$calendars = OCA\Calendar\Calendar::allCalendars(OCP\USER::getUser());
$calendar_options = array();

foreach($calendars as $calendar) {
	if($calendar['userid'] != OCP\User::getUser()) {
		$sharedCalendar = OCP\Share::getItemSharedWithBySource('calendar', $calendar['id']);
		if ($sharedCalendar && ($sharedCalendar['permissions'] & OCP\PERMISSION_CREATE)) {
			array_push($calendar_options, $calendar);
		}
	} else {
		array_push($calendar_options, $calendar);
	}
}
$access_class_options = OCA\Calendar\App::getAccessClassOptions();
$repeat_options = OCA\Calendar\App::getRepeatOptions();
$repeat_end_options = OCA\Calendar\App::getEndOptions();
$repeat_month_options = OCA\Calendar\App::getMonthOptions();
$repeat_year_options = OCA\Calendar\App::getYearOptions();
$repeat_weekly_options = OCA\Calendar\App::getWeeklyOptions();
$repeat_weekofmonth_options = OCA\Calendar\App::getWeekofMonth();
$repeat_byyearday_options = OCA\Calendar\App::getByYearDayOptions();
$repeat_bymonth_options = OCA\Calendar\App::getByMonthOptions();
$repeat_byweekno_options = OCA\Calendar\App::getByWeekNoOptions();
$repeat_bymonthday_options = OCA\Calendar\App::getByMonthDayOptions();

//NEW Reminder
$reminder_options = OCA\Calendar\App::getReminderOptions();
$reminder_time_options = OCA\Calendar\App::getReminderTimeOptions();
//reminder

$tmpl = new OCP\Template('calendar', 'part.newevent');
$tmpl->assign('access', 'owner');
$tmpl->assign('accessclass', 'PUBLIC');
$tmpl->assign('calendar_options', $calendar_options);
$tmpl->assign('access_class_options', $access_class_options);
$tmpl->assign('repeat_options', $repeat_options);
$tmpl->assign('repeat_month_options', $repeat_month_options);
$tmpl->assign('repeat_weekly_options', $repeat_weekly_options);
$tmpl->assign('repeat_end_options', $repeat_end_options);
$tmpl->assign('repeat_year_options', $repeat_year_options);
$tmpl->assign('repeat_byyearday_options', $repeat_byyearday_options);
$tmpl->assign('repeat_bymonth_options', $repeat_bymonth_options);
$tmpl->assign('repeat_byweekno_options', $repeat_byweekno_options);
$tmpl->assign('repeat_bymonthday_options', $repeat_bymonthday_options);
$tmpl->assign('repeat_weekofmonth_options', $repeat_weekofmonth_options);

$tmpl -> assign('reminder_options', $reminder_options);
$tmpl -> assign('reminder','');

$tmpl -> assign('reminder_time_options', $reminder_time_options);
$tmpl -> assign('remindertimeselect','');
$tmpl -> assign('remindertimeinput','');
$tmpl -> assign('reminderemailinput','');


$tmpl->assign('eventid', 'new');
$tmpl->assign('startdate', $start->format('d-m-Y'));

//init
$tWeekDay=$start->format('l');
$transWeekDay=$l->t((string)$tWeekDay);
$tDayOfMonth=$start->format('j');
$tMonth=$start->format('F');
$transMonth=$l->t((string)$tMonth);
$transByWeekNo=$start->format('W');
$transByYearDay=$start->format('z');
//\OCP\Util::writeLog('calendar',$tMonth.$transMonth ,\OCP\Util::DEBUG);
$tmpl->assign('repeat_weekdays',$transWeekDay);
$tmpl -> assign('repeat_bymonthday',$tDayOfMonth);
$tmpl->assign('repeat_bymonth',$transMonth);
$tmpl -> assign('repeat_byweekno', $transByWeekNo);
$tmpl -> assign('repeat_byyearday',$transByYearDay);
$tmpl->assign('starttime', $start->format('H:i'));
$tmpl->assign('enddate', $end->format('d-m-Y'));
$tmpl->assign('endtime', $end->format('H:i'));
$tmpl->assign('allday', $allday);
$tmpl->assign('repeat', 'doesnotrepeat');
$tmpl->assign('repeat_month','monthday');

$tmpl->assign('repeat_interval', 1);
$tmpl->assign('repeat_end', 'never');
$tmpl->assign('repeat_count', '10');
$tmpl->assign('repeat_weekofmonth', 'auto');
$tmpl->assign('repeat_date', '');
$tmpl->assign('repeat_year', 'bydate');
$tmpl->printpage();
