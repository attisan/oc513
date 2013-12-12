<?php

$installedVersion=OCP\Config::getAppValue('calendar', 'installed_version');

if (version_compare($installedVersion, '0.6.4', '<')) {
//if ($installedVersion == '0.1.1') {
		$SQL="ALTER TABLE `*PREFIX*clndr_objects` 
		ADD `isalarm` TINYINT( 1 ) NOT NULL  DEFAULT '0',
		ADD `org_objid` INT( 10 ) NOT NULL DEFAULT '0', 
		ADD `userid` VARCHAR( 200 ) NOT NULL,
		ADD INDEX ( `calendarid` , `objecttype` ) 
		";
	$stmt = OCP\DB::prepare($SQL);
	$stmt->execute();
}
