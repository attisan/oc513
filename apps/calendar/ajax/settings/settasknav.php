<?php

// Init owncloud

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();
$isHidden='false';
if($_POST['checked']==='true') {
	OCP\Config::setUserValue(OCP\USER::getUser(), 'calendar', 'tasknav', 'true');
	$isHidden='false';
}else{
	OCP\Config::setUserValue(OCP\USER::getUser(), 'calendar', 'tasknav', 'false');
	$isHidden='true';
}
OCP\JSON::success(array('isHidden' => $isHidden));
