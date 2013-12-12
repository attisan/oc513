<?php
OC::$CLASSPATH['OCA\Calendar\App'] = 'calendar/lib/app.php';
OC::$CLASSPATH['OCA\Calendar\Alarm'] = 'calendar/lib/alarm.php';
OC::$CLASSPATH['OCA\Calendar\Calendar'] = 'calendar/lib/calendar.php';
OC::$CLASSPATH['OCA\Calendar\Jobs'] = 'calendar/lib/jobs.php';
OC::$CLASSPATH['OCA\Calendar\Object'] = 'calendar/lib/object.php';
OC::$CLASSPATH['OCA\Calendar\Hooks'] = 'calendar/lib/hooks.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV'] = 'calendar/lib/sabre/backend.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_CalendarRoot'] = 'calendar/lib/sabre/calendarroot.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_UserCalendars'] = 'calendar/lib/sabre/usercalendars.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_Calendar'] = 'calendar/lib/sabre/calendar.php';
OC::$CLASSPATH['OC_Connector_Sabre_CalDAV_CalendarObject'] = 'calendar/lib/sabre/object.php';
OC::$CLASSPATH['OCA\Calendar\Repeat'] = 'calendar/lib/repeat.php';
OC::$CLASSPATH['OCA\Calendar\SearchProvider'] = 'calendar/lib/search.php';
OC::$CLASSPATH['OCA\Calendar\Export'] = 'calendar/lib/export.php';
OC::$CLASSPATH['OCA\Calendar\Import'] = 'calendar/lib/import.php';
OC::$CLASSPATH['OCA\Calendar\Share_Backend_Calendar'] = 'calendar/lib/share/calendar.php';
OC::$CLASSPATH['OCA\Calendar\Share_Backend_Event'] = 'calendar/lib/share/event.php';

Sabre\VObject\Property::$classMap['SUMMARY'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['DESCRIPTION'] = 'OC\VObject\StringProperty';
Sabre\VObject\Property::$classMap['LOCATION'] = 'OC\VObject\StringProperty';

//General Hooks
OCP\Util::connectHook('OC_User', 'post_createUser', 'OC_Calendar_Hooks', 'createUser');
OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OC_Calendar_Hooks', 'deleteUser');
//Repeating Events Hooks
OCP\Util::connectHook('OC_Calendar', 'addEvent', 'OCA\Calendar\Repeat', 'generate');
OCP\Util::connectHook('OC_Calendar', 'editEvent', 'OCA\Calendar\Repeat', 'update');
OCP\Util::connectHook('OC_Calendar', 'deleteEvent', 'OCA\Calendar\Repeat', 'clean');
OCP\Util::connectHook('OC_Calendar', 'moveEvent', 'OCA\Calendar\Repeat', 'update');
OCP\Util::connectHook('OC_Calendar', 'deleteCalendar', 'OCA\Calendar\Repeat', 'cleanCalendar');

OCP\Share::registerBackend('calendar', 'OCA\Calendar\Share_Backend_Calendar');
OCP\Share::registerBackend('event', 'OCA\Calendar\Share_Backend_Event');

