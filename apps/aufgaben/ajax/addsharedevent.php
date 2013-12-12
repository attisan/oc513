<?php
/**
 * Copyright (c) 2013 Sebastian Doell 
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */



OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('aufgaben');
OCP\JSON::callCheck();

    $taskid = $_POST['taskid'];
	$calid = $_POST['calid'];
	
	try {
		OCA\Aufgaben\App::addSharedTask($taskid,$calid);
	} catch(Exception $e) {
		OCP\JSON::error(array('message'=>$e->getMessage()));
		exit;
	}
	OCP\JSON::success();
