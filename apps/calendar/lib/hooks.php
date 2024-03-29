<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * This class contains all hooks.
 */
 namespace OCA\Calendar;
 
class Hooks{
	/**
	 * @brief Creates default calendar for a user
	 * @param paramters parameters from postCreateUser-Hook
	 * @return array
	 */
	public static function createUser($parameters) {
		Calendar::addDefaultCalendars($parameters['uid']);

		return true;
	}

	/**
	 * @brief Deletes all calendars of a certain user
	 * @param paramters parameters from postDeleteUser-Hook
	 * @return array
	 */
	public static function deleteUser($parameters) {
		$calendars = Calendar::allCalendars($parameters['uid']);

		foreach($calendars as $calendar) {
			if($parameters['uid'] === $calendar['userid']) {
				Calendar::deleteCalendar($calendar['id']);
			}
		}

		return true;
	}
}
