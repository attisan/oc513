<?php
/**
 * ownCloud - Aufgaben Remastered
 *
 * @author Sebastian Doell
 * @copyright 2013 sebastian doell sebastian@libasys.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
 

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('aufgaben');
OCP\JSON::callCheck();

$id = $_POST['id'];
$checked = $_POST['checked'];

$vcalendar = OCA\Calendar\App::getVCalendar( $id );
$vtodo = $vcalendar->VTODO;

OCA\Aufgaben\App::setComplete($vtodo, $checked ? '100' : '0', null);
OCA\Calendar\Object::edit($id, $vcalendar->serialize());
$user_timezone = OCA\Calendar\App::getTimezone();
$task_info = OCA\Aufgaben\App::arrayForJSON($id, $vtodo, $user_timezone);

OCP\JSON::success(array('data' => $task_info));
