<?php
/**
 * Copyright (c) 2012 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('calendar');
OCP\JSON::callCheck();

$categories = isset($_POST['categories']) ? $_POST['categories'] : null;


$vcategories = new OC_VCategories('event');
$vcategories->delete($categories);


OC_JSON::success(array('data' => array('categories'=>$vcategories->categories())));
