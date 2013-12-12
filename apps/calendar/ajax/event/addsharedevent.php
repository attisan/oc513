<?php
/**
 * Copyright (c) 2013 Sebastian Doell 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */



OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

    $eventid = $_POST['eventid'];
	$calid = $_POST['calid'];
	
	try {
		OCA\Calendar\Object::addSharedEvent($eventid,$calid);
	} catch(Exception $e) {
		OCP\JSON::error(array('message'=>$e->getMessage()));
		exit;
	}
	OCP\JSON::success();
