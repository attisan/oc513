<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/**
 *
 * The following SQL statement is just a help for developers and will not be
 * executed!
 *
 * CREATE TABLE clndr_calendars (
 *     id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 *     userid VARCHAR(255),
 *     displayname VARCHAR(100),
 *     uri VARCHAR(100),
 *     active INTEGER UNSIGNED NOT NULL DEFAULT '0',
 *     ctag INTEGER UNSIGNED NOT NULL DEFAULT '0',
 *     calendarorder INTEGER UNSIGNED NOT NULL DEFAULT '0',
 *     calendarcolor VARCHAR(10),
 *     timezone TEXT,
 *     components VARCHAR(20)
 * );
 *
 */

 /****BEGIN:VCALENDAR

BEGIN:VEVENT
CREATED:20131014T085352Z
UID:DDCE19A9-D323-4964-AE09-23103935DAA3
DTEND;TZID=Europe/Berlin:20131015T173000
EXDATE;TZID=Europe/Berlin:20131029T160000
RRULE:FREQ=WEEKLY;INTERVAL=1
TRANSP:OPAQUE
SUMMARY:Sharing with repeat
DTSTART;TZID=Europe/Berlin:20131015T160000
DTSTAMP:20131014T085526Z
SEQUENCE:4
END:VEVENT



BEGIN:VEVENT
CREATED:20131014T092522Z
UID:FDF19FB6-C617-44F3-80F1-FB7367D496A8
DTEND;TZID=Europe/Berlin:20131015T140000
EXDATE;TZID=Europe/Berlin:20131126T104500
EXDATE;TZID=Europe/Berlin:20131112T104500
EXDATE;TZID=Europe/Berlin:20131029T104500
EXDATE;TZID=Europe/Berlin:20131022T104500
RRULE:FREQ=WEEKLY;INTERVAL=1
TRANSP:OPAQUE
SUMMARY:Ta Tests
DTSTART;TZID=Europe/Berlin:20131015T104500
DTSTAMP:20131014T092715Z
SEQUENCE:3
END:VEVENT

  *  
  
  * ****/
 
/**
 * This class manages our calendars
 */
 namespace OCA\Calendar;
 
class Calendar{
	/**
	 * @brief Returns the list of calendars for a specific user.
	 * @param string $uid User ID
	 * @param boolean $active Only return calendars with this $active state, default(=false) is don't care
	 * @return array
	 */
	public static function allCalendars($uid, $active=false) {
		$values = array($uid);
		$active_where = '';
		if (!is_null($active) && $active) {
			$active_where = ' AND `active` = ?';
			$values[] = (int)$active;
		}
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_calendars` WHERE `userid` = ?' . $active_where );
		$result = $stmt->execute($values);

		$calendars = array();
		while( $row = $result->fetchRow()) {
			$row['permissions'] = \OCP\PERMISSION_ALL;
			$calendars[] = $row;
		}
		$calendars = array_merge($calendars, \OCP\Share::getItemsSharedWith('calendar', Share_Backend_Calendar::FORMAT_CALENDAR));

		return $calendars;
	}

	/**
	 * @brief Returns the list of calendars for a principal (DAV term of user)
	 * @param string $principaluri
	 * @return array
	 */
	public static function allCalendarsWherePrincipalURIIs($principaluri) {
		$uid = self::extractUserID($principaluri);
		return self::allCalendars($uid);
	}

	/**
	 * @brief Gets the data of one calendar
	 * @param integer $id
	 * @return associative array
	 */
	public static function find($id) {
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_calendars` WHERE `id` = ?' );
		$result = $stmt->execute(array($id));

		$row = $result->fetchRow();
		if($row['userid'] != \OCP\USER::getUser() && !\OC_Group::inGroup(\OCP\User::getUser(), 'admin')) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_READ)) {
					
				return $row; // I have to return the row so e.g. Object::getowner() works.
			}
			
			$row['permissions'] = $sharedCalendar['permissions'];
			
		} else {
			$row['permissions'] = \OCP\PERMISSION_ALL;
		}
		
		return $row;
	}

	/**
	 * @brief Creates a new calendar
	 * @param string $userid
	 * @param string $name
	 * @param string $components Default: "VEVENT,VTODO,VJOURNAL"
	 * @param string $timezone Default: null
	 * @param integer $order Default: 1
	 * @param string $color Default: null, format: '#RRGGBB(AA)'
	 * @return insertid
	 */
	public static function addCalendar($userid,$name,$components='VEVENT,VTODO,VJOURNAL',$timezone=null,$order=0,$color=null) {
		$all = self::allCalendars($userid);
		$uris = array();
		foreach($all as $i) {
			$uris[] = $i['uri'];
		}

		$uri = self::createURI($name, $uris );

		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_calendars` (`userid`,`displayname`,`uri`,`ctag`,`calendarorder`,`calendarcolor`,`timezone`,`components`) VALUES(?,?,?,?,?,?,?,?)' );
		$result = $stmt->execute(array($userid,$name,$uri,1,$order,$color,$timezone,$components));

		$insertid = \OCP\DB::insertid('*PREFIX*clndr_calendars');
		\OCP\Util::emitHook('OC_Calendar', 'addCalendar', $insertid);

		return $insertid;
	}

	/**
	 * @brief Creates default calendars
	 * @param string $userid
	 * @return boolean
	 */
	public static function addDefaultCalendars($userid = null) {
		if(is_null($userid)) {
			$userid = \OCP\USER::getUser();
		}
		
		$id = self::addCalendar($userid,$userid);

		return true;
	}
	/**
	 * @brief Creates default calendars
	 * @param string $userid
	 * @return boolean
	 */
	public static function addSharedCalendars($userid = null) {
		if(is_null($userid)) {
			$userid = \OCP\USER::getUser();
		}
		
		$id = self::addCalendar($userid,'shared_events_'.$userid);

		return true;
	}
  
	/**
	 * @brief Creates a new calendar from the data sabredav provides
	 * @param string $principaluri
	 * @param string $uri
	 * @param string $name
	 * @param string $components
	 * @param string $timezone
	 * @param integer $order
	 * @param string $color format: '#RRGGBB(AA)'
	 * @return insertid
	 */
	public static function addCalendarFromDAVData($principaluri,$uri,$name,$components,$timezone,$order,$color) {
		$userid = self::extractUserID($principaluri);

		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_calendars` (`userid`,`displayname`,`uri`,`ctag`,`calendarorder`,`calendarcolor`,`timezone`,`components`) VALUES(?,?,?,?,?,?,?,?)' );
		$result = $stmt->execute(array($userid,$name,$uri,1,$order,$color,$timezone,$components));

		$insertid = \OCP\DB::insertid('*PREFIX*clndr_calendars');
		\OCP\Util::emitHook('OC_Calendar', 'addCalendar', $insertid);

		return $insertid;
	}

	/**
	 * @brief Edits a calendar
	 * @param integer $id
	 * @param string $name Default: null
	 * @param string $components Default: null
	 * @param string $timezone Default: null
	 * @param integer $order Default: null
	 * @param string $color Default: null, format: '#RRGGBB(AA)'
	 * @return boolean
	 *
	 * Values not null will be set
	 */
	public static function editCalendar($id,$name=null,$components=null,$timezone=null,$order=null,$color=null) {
		// Need these ones for checking uri
		$calendar = self::find($id);
		if ($calendar['userid'] != \OCP\User::getUser() && !\OC_Group::inGroup(\OCP\User::getUser(), 'admin')) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_UPDATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to update this calendar.'
					)
				);
			}
		}

		// Keep old stuff
		if(is_null($name)) $name = $calendar['displayname'];
		if(is_null($components)) $components = $calendar['components'];
		if(is_null($timezone)) $timezone = $calendar['timezone'];
		if(is_null($order)) $order = $calendar['calendarorder'];
		if(is_null($color)) $color = $calendar['calendarcolor'];

		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_calendars` SET `displayname`=?,`calendarorder`=?,`calendarcolor`=?,`timezone`=?,`components`=?,`ctag`=`ctag`+1 WHERE `id`=?' );
		$result = $stmt->execute(array($name,$order,$color,$timezone,$components,$id));

		\OCP\Util::emitHook('OC_Calendar', 'editCalendar', $id);
		return true;
	}

	/**
	 * @brief Sets a calendar (in)active
	 * @param integer $id
	 * @param boolean $active
	 * @return boolean
	 */
	public static function setCalendarActive($id,$active) {
		$calendar = self::find($id);
		if ($calendar['userid'] != \OCP\User::getUser()) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_UPDATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to update this calendar.'
					)
				);
			}
		}
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_calendars` SET `active` = ? WHERE `id` = ?' );
		$stmt->execute(array((int)$active, $id));

		return true;
	}

	/**
	 * @brief Updates ctag for calendar
	 * @param integer $id
	 * @return boolean
	 */
	public static function touchCalendar($id) {
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_calendars` SET `ctag` = `ctag` + 1 WHERE `id` = ?' );
		$stmt->execute(array($id));

		return true;
	}

	/**
	 * @brief removes a calendar
	 * @param integer $id
	 * @return boolean
	 */
	public static function deleteCalendar($id) {
		$calendar = self::find($id);
		if ($calendar['userid'] != \OCP\User::getUser() && !\OC_Group::inGroup(\OCP\User::getUser(), 'admin')) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_DELETE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to delete this calendar.'
					)
				);
			}
		}
		$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_calendars` WHERE `id` = ?' );
		$stmt->execute(array($id));

		$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_objects` WHERE `calendarid` = ?' );
		$stmt->execute(array($id));

		\OCP\Share::unshareAll('calendar', $id);

		\OCP\Util::emitHook('OC_Calendar', 'deleteCalendar', $id);
		if(\OCP\USER::isLoggedIn() and count(self::allCalendars(\OCP\USER::getUser())) == 0) {
			self::addDefaultCalendars(\OCP\USER::getUser());
		}

		return true;
	}

	/**
	 * @brief merges two calendars
	 * @param integer $id1
	 * @param integer $id2
	 * @return boolean
	 */
	public static function mergeCalendar($id1, $id2) {
		$calendar = self::find($id1);
		if ($calendar['userid'] != \OCP\User::getUser() && !\OC_Group::inGroup(\OCP\User::getUser(), 'admin')) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $id1);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_UPDATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to add to this calendar.'
					)
				);
			}
		}
		$stmt = \OCP\DB::prepare('UPDATE `*PREFIX*clndr_objects` SET `calendarid` = ? WHERE `calendarid` = ?');
		$stmt->execute(array($id1, $id2));
		self::touchCalendar($id1);
		self::deleteCalendar($id2);
	}

	/**
	 * @brief Creates a URI for Calendar
	 * @param string $name name of the calendar
	 * @param array  $existing existing calendar URIs
	 * @return string uri
	 */
	public static function createURI($name,$existing) {
		$strip=array(' ','/','?','&');//these may break sync clients
		$name=str_replace($strip,'',$name);
		$name = strtolower($name);

		$newname = $name;
		$i = 1;
		while(in_array($newname,$existing)) {
			$newname = $name.$i;
			$i = $i + 1;
		}
		return $newname;
	}

	/**
	 * @brief gets the userid from a principal path
	 * @return string
	 */
	public static function extractUserID($principaluri) {
		list($prefix,$userid) = \Sabre_DAV_URLUtil::splitPath($principaluri);
		return $userid;
	}

	/**
	 * @brief returns the possible color for calendars
	 * @return array
	 */
	public static function getCalendarColorOptions() {
		return array(
			'#ff0000', // "Red"
			'#b3dc6c', // "Green"
			'#ffff00', // "Yellow"
			'#808000', // "Olive"
			'#ffa500', // "Orange"
			'#ff7f50', // "Coral"
			'#ee82ee', // "Violet"
			'#9fc6e7', // "light blue"
		);
	}

	/**
	 * @brief generates the Event Source Info for our JS
	 * @param array $calendar calendar data
	 * @return array
	 */
	public static function getEventSourceInfo($calendar) {
		return array(
			'url' => \OCP\Util::linkTo('calendar', 'ajax/events.php').'?calendar_id='.$calendar['id'],
			'backgroundColor' => $calendar['calendarcolor'],
			'borderColor' => $calendar['calendarcolor'],
			'textColor' => self::generateTextColor($calendar['calendarcolor']),
			'cache' => false,
		);
	}

	/*
	 * @brief checks if a calendar name is available for a user
	 * @param string $calendarname
	 * @param string $userid
	 * @return boolean
	 */
	public static function isCalendarNameavailable($calendarname, $userid) {
		$calendars = self::allCalendars($userid);
		foreach($calendars as $calendar) {
			if($calendar['displayname'] == $calendarname) {
				return false;
			}
		}
		return true;
	}

	/*
	 * @brief generates the text color for the calendar
	 * @param string $calendarcolor rgb calendar color code in hex format (with or without the leading #)
	 * (this function doesn't pay attention on the alpha value of rgba color codes)
	 * @return boolean
	 */
	public static function generateTextColor($calendarcolor) {
		if(substr_count($calendarcolor, '#') == 1) {
			$calendarcolor = substr($calendarcolor,1);
		}
		$red = hexdec(substr($calendarcolor,0,2));
		$green = hexdec(substr($calendarcolor,2,2));
		$blue = hexdec(substr($calendarcolor,4,2));
		//recommendation by W3C
		$computation = ((($red * 299) + ($green * 587) + ($blue * 114)) / 1000);
		return ($computation > 130)?'#000000':'#FAFAFA';
	}
	
	public static function permissionReader($iPermission){
			
			$l = \OC_L10N::get('core');
			
			$aPermissionArray=array(
			   16 => $l->t('share'),
			   8 => $l->t('delete'),
			   4 => $l->t('create'),
			   2 => $l->t('update'),
			   1 => 'lesen',
			);
			
			if($iPermission==1) return '(readonly)';
			if($iPermission==31) return false;
			
			$outPutPerm='';
			foreach($aPermissionArray as $key => $val){
				if($iPermission>= $key){
					if($outPutPerm=='') $outPutPerm.=$val;
					else $outPutPerm.=', '.$val;
					$iPermission-=$key;
				}
			}
			return $outPutPerm;
		
	}
	/**
	 * @brief Get the email address of a user
	 * @returns the email address of the user

	 * This method returns the email address of selected user.
	 */
	public static function getUsersEmails($names) {
		return \OCP\Config::getUserValue(\OCP\User::getUser(), 'settings', 'email');
	}
	
}
