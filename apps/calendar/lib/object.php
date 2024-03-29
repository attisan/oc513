<?php
/**
 * Copyright (c) 2011 Jakob Sack <mail@jakobsack.de>
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
 /**
 *
 * The following SQL statement is just a help for developers and will not be
 * executed!
 *
 * CREATE TABLE clndr_objects (
 *     id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 *     calendarid INTEGER UNSIGNED NOT NULL,
 *     objecttype VARCHAR(40) NOT NULL,
 *     startdate DATETIME,
 *     enddate DATETIME,
 *     repeating INT(1),
 *     summary VARCHAR(255),
 *     calendardata TEXT,
 *     uri VARCHAR(100),
 *     lastmodified INT(11)
 * );
 *
 */

/**
 * This class manages our calendar objects
 */
 namespace OCA\Calendar;
 
class Object{
	/**
	 * @brief Returns all objects of a calendar
	 * @param integer $id
	 * @return array
	 *
	 * The objects are associative arrays. You'll find the original vObject in
	 * ['calendardata']
	 */
	public static function all($id) {
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_objects` WHERE `calendarid` = ? ');
		$result = $stmt->execute(array($id));

		$calendarobjects = array();
		while( $row = $result->fetchRow()) {
			$calendarobjects[] = $row;
		}

		return $calendarobjects;
	}
   
   /**
	 * @brief Returns true or false if event is an shared event
	 * @param integer $id
	 * 
	 * @return true or false
	 *
	 */

    public static function checkSharedEvent($id){
    	  
		   $stmt = \OCP\DB::prepare('
    	   SELECT id FROM `*PREFIX*clndr_objects`
     	   WHERE `org_objid` = ? AND `userid` = ? AND `objecttype` = ?');
		   $result = $stmt->execute(array($id,\OCP\User::getUser(),'VEVENT'));
		   $row = $result->fetchRow();
		   if(is_array($row)) return $row;
		   else return false;
    }
	
	/**
	 * @brief Returns all  shared events where user is owner
	 * 
	 * @return array
	 */
	 
    public static function getEventSharees(){
   	
		$SQL='SELECT item_source FROM `*PREFIX*share` WHERE `uid_owner` = ? AND `item_type` = ?';
		$stmt = \OCP\DB::prepare($SQL);
		$result = $stmt->execute(array(\OCP\User::getUser(),'event'));
		$aSharees = '';
		while( $row = $result->fetchRow()) {
			$aSharees[$row['item_source']]=1;
		}
		
		if(is_array($aSharees)) return $aSharees;
		else return false;
    }
	
	
	/**
	 * @brief Returns all  shared calendar where user is owner
	 * 
	 * @return array
	 */
	
	public static function getCalendarSharees(){
    	
		$SQL='SELECT item_source FROM `*PREFIX*share` WHERE `uid_owner` = ? AND `item_type` = ?';
		$stmt = \OCP\DB::prepare($SQL);
		$result = $stmt->execute(array(\OCP\User::getUser(),'calendar'));
		$aSharees = '';
		while( $row = $result->fetchRow()) {
			$aSharees[$row['item_source']]=1;
		}
		
		if(is_array($aSharees)) return $aSharees;
		else return false;
    }

	/**
	 * @brief Returns all objects of a calendar between $start and $end
	 * @param integer $id
	 * @param DateTime $start
	 * @param DateTime $end
	 * @return array
	 *
	 * The objects are associative arrays. You'll find the original vObject
	 * in ['calendardata']
	 */
	 
	 public static function allInPeriod($id, $start, $end) {
		
	   $sharedwithByEvents = self::getEventSharees();
       
			
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_objects` WHERE `calendarid` = ? AND `objecttype`= ?' 
		.' AND ((`startdate` >= ? AND `enddate` <= ? AND `repeating` = 0)'
		.' OR (`enddate` >= ? AND `startdate` <= ? AND `repeating` = 0)'
		.' OR (`startdate` <= ? AND `repeating` = 1) )' );
		$start = self::getUTCforMDB($start);
		$end = self::getUTCforMDB($end);
		$result = $stmt->execute(array($id,'VEVENT',
					$start, $end,					
					$start, $end,
					$end));
      \OCP\Util::writeLog('calendar','Events: -> Beginn', \OCP\Util::DEBUG);
		$calendarobjects = array();
		while( $row = $result->fetchRow()) {
			
			
			$row['shared']=0;
			if(is_array($sharedwithByEvents) && isset($sharedwithByEvents[$row['id']])){
				 $row['shared']=1;
				\OCP\Util::writeLog('calendar','Events Shared Found: ->'.$row['id'], \OCP\Util::DEBUG);
			}
			
			$calendarobjects[] = $row;
			
			\OCP\Util::writeLog('calendar','Events: ->'.$row['summary'], \OCP\Util::DEBUG);
				
		}

		return $calendarobjects;
	}
	
	
	
	
	/**
	 * @brief Returns an object
	 * @param integer $id
	 * @return associative array
	 */
	public static function find($id) {
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_objects` WHERE `id` = ?' );
		$result = $stmt->execute(array($id));

		return $result->fetchRow();
	}

	/**
	 * @brief finds an object by its DAV Data
	 * @param integer $cid Calendar id
	 * @param string $uri the uri ('filename')
	 * @return associative array
	 */
	public static function findWhereDAVDataIs($cid,$uri) {
		$stmt = \OCP\DB::prepare( 'SELECT * FROM `*PREFIX*clndr_objects` WHERE `calendarid` = ? AND `uri` = ?' );
		$result = $stmt->execute(array($cid,$uri));

		return $result->fetchRow();
	}

	/**
	 * @brief Adds an object
	 * @param integer $id Calendar id
	 * @param string $data  object
	 * @return insertid
	 */
	public static function add($id,$data) {
		$calendar = Calendar::find($id);
		if ($calendar['userid'] != \OCP\User::getUser()) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_CREATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to add events to this calendar.'
					)
				);
			}
		}
		$object = \OC_VObject::parse($data);
		list($type,$startdate,$enddate,$summary,$repeating,$uid,$isAlarm) = self::extractData($object);

		if(is_null($uid)) {
			$object->setUID();
			$data = $object->serialize();
		}

		$uri = 'owncloud-'.md5($data.rand().time()).'.ics';

		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_objects` (`calendarid`,`objecttype`,`startdate`,`enddate`,`repeating`,`summary`,`calendardata`,`uri`,`lastmodified`,`isalarm`) VALUES(?,?,?,?,?,?,?,?,?,?)' );
		$stmt->execute(array($id,$type,$startdate,$enddate,$repeating,$summary,$data,$uri,time(),$isAlarm));
		$object_id = \OCP\DB::insertid('*PREFIX*clndr_objects');

		App::loadCategoriesFromVCalendar($object_id, $object);

		Calendar::touchCalendar($id);
		\OCP\Util::emitHook('OC_Calendar', 'addEvent', $object_id);
		return $object_id;
	}

    /**
	 * @brief Adds an object
	 * @param integer $id Calendar id
	 * @param string $data  object
	 * @return insertid
	 */
	public static function addSharedEvent($id,$calid) {
		$shareevent = self::find($id);
		/*
		$object = \OC_VObject::parse($shareevent['calendardata']);
		$data = $object->serialize();
		list($type,$startdate,$enddate,$summary,$repeating,$uid,$isAlarm) = self::extractData($data);

		$uri = 'owncloud-'.md5($data.rand().time()).'.ics';
        */
		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_objects` (`calendarid`,`objecttype`,`startdate`,`enddate`,`repeating`,`summary`,`calendardata`,`uri`,`lastmodified`,`isalarm`,`org_objid`,`userid`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)' );
		$stmt->execute(array($calid,$shareevent['objecttype'],$shareevent['startdate'],$shareevent['enddate'],$shareevent['repeating'],$shareevent['summary'],$shareevent['calendardata'],$shareevent['uri'],time(),$shareevent['isalarm'],$id,\OCP\User::getUser()));
		$object_id = \OCP\DB::insertid('*PREFIX*clndr_objects');

		App::loadCategoriesFromVCalendar($object_id, $object);
		Calendar::touchCalendar($calid);
		\OCP\Util::emitHook('OC_Calendar', 'addEvent', $object_id);
		return $object_id;
	}


	/**
	 * @brief Adds an object with the data provided by sabredav
	 * @param integer $id Calendar id
	 * @param string $uri   the uri the card will have
	 * @param string $data  object
	 * @return insertid
	 */
	public static function addFromDAVData($id,$uri,$data) {
		$calendar = Calendar::find($id);
		if ($calendar['userid'] != \OCP\User::getUser()) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $id);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_CREATE)) {
				throw new \Sabre_DAV_Exception_Forbidden(
					App::$l10n->t(
						'You do not have the permissions to add events to this calendar.'
					)
				);
			}
		}
		$object = \OC_VObject::parse($data);
		list($type,$startdate,$enddate,$summary,$repeating,$uid,$isAlarm) = self::extractData($object);
      
		
		$stmt = \OCP\DB::prepare( 'INSERT INTO `*PREFIX*clndr_objects` (`calendarid`,`objecttype`,`startdate`,`enddate`,`repeating`,`summary`,`calendardata`,`uri`,`lastmodified`,`isalarm`) VALUES(?,?,?,?,?,?,?,?,?,?)' );
		$stmt->execute(array($id,$type,$startdate,$enddate,$repeating,$summary,$data,$uri,time(),$isAlarm));
		$object_id = \OCP\DB::insertid('*PREFIX*clndr_objects');

		Calendar::touchCalendar($id);
		\OCP\Util::emitHook('OC_Calendar', 'addEvent', $object_id);
		return $object_id;
	}

    public static function checkShareMode($calid){
       
	    $bCheckCalUser=false;
		$stmt = \OCP\DB::prepare( 'SELECT share_with FROM `*PREFIX*share` WHERE `item_type`= ? AND `item_source` = ? ' );
		$result = $stmt->execute(array('calendar',$calid));
        while( $row = $result->fetchRow()) {
			if ($row['share_with'] == \OCP\User::getUser() || \OC_Group::inGroup(\OCP\User::getUser(), $row['share_with'])) {
				 $bCheckCalUser=true;
			}
		}
		 return $bCheckCalUser;
		
    }
	
	public static function checkShareEventMode($eventid){
    	  //$usersInGroup = \OC_Group::usersInGroup($row['share_with']);  inGroup( $uid, $gid )
    
	    $bCheckCalUser=false;
		$stmt = \OCP\DB::prepare( 'SELECT share_with FROM `*PREFIX*share` WHERE `item_type`= ? AND `item_source` = ?' );
		$result = $stmt->execute(array('event',$eventid));
       
		while( $row = $result->fetchRow()) {
			if ($row['share_with'] == \OCP\User::getUser() || \OC_Group::inGroup(\OCP\User::getUser(), $row['share_with'])) {
				 $bCheckCalUser=true;
			}
		}
		
		 return $bCheckCalUser;
		
    }
	
	/**
	 * @brief edits an object
	 * @param integer $id id of object
	 * @param string $data  object
	 * @return boolean
	 */
	public static function edit($id, $data) {
		$oldobject = self::find($id);
		$calid = self::getCalendarid($id);
		
		
		$calendar = Calendar::find($calid);
		$oldvobject = \OC_VObject::parse($oldobject['calendardata']);
		
		if ($calendar['userid'] != \OCP\User::getUser()) {
				
			$shareMode=self::checkShareMode($calid);
			if($shareMode){
				$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $calid); //calid, not objectid !!!! 1111 one one one eleven
			}else{
				$sharedCalendar = \OCP\Share::getItemSharedWithBySource('event', $id); 
			}
			
			$sharedAccessClassPermissions = Object::getAccessClassPermissions($oldvobject);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_UPDATE) || !($sharedAccessClassPermissions & \OCP\PERMISSION_UPDATE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to edit this event. Fehler'.$sharedCalendar.$id
					)
				);
			}
		}
		$object = \OC_VObject::parse($data);
		App::loadCategoriesFromVCalendar($id, $object);
		list($type,$startdate,$enddate,$summary,$repeating,$uid,$isAlarm) = self::extractData($object);

        //check Share
        $stmtShare = \OCP\DB::prepare("SELECT COUNT(*) AS COUNTSHARE FROM `*PREFIX*share` WHERE `item_source` = ? AND `item_type`= ? ");
        $result=$stmtShare->execute(array($id,'event'));
		$row = $result->fetchRow();
		
        if($row['COUNTSHARE']>=1){
        		$stmtShareUpdate = \OCP\DB::prepare( "UPDATE `*PREFIX*share` SET `item_target`= ? WHERE `item_source` = ? AND `item_type` = ? ");
		        $stmtShareUpdate->execute(array($summary,$id,'event'));
				
				$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_objects` SET `objecttype`=?,`startdate`=?,`enddate`=?,`repeating`=?,`summary`=?,`calendardata`=?,`lastmodified`= ?,`isalarm`= ? WHERE `org_objid` = ?' );
		        $stmt->execute(array($type,$startdate,$enddate,$repeating,$summary,$data,time(),$isAlarm,$id));
        }
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_objects` SET `objecttype`=?,`startdate`=?,`enddate`=?,`repeating`=?,`summary`=?,`calendardata`=?,`lastmodified`= ?,`isalarm`= ? WHERE `id` = ?' );
		$stmt->execute(array($type,$startdate,$enddate,$repeating,$summary,$data,time(),$isAlarm,$id));

		Calendar::touchCalendar($oldobject['calendarid']);
		\OCP\Util::emitHook('OC_Calendar', 'editEvent', $id);

		return true;
	}

	/**
	 * @brief edits an object with the data provided by sabredav
	 * @param integer $id calendar id
	 * @param string $uri   the uri of the object
	 * @param string $data  object
	 * @return boolean
	 */
	public static function editFromDAVData($cid,$uri,$data) {
		$oldobject = self::findWhereDAVDataIs($cid,$uri);

		$calendar = Calendar::find($cid);
		$oldvobject = \OC_VObject::parse($oldobject['calendardata']);
		if ($calendar['userid'] != \OCP\User::getUser()) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $cid);
			$sharedAccessClassPermissions = Object::getAccessClassPermissions($oldvobject);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_UPDATE) || !($sharedAccessClassPermissions & \OCP\PERMISSION_UPDATE)) {
				throw new \Sabre_DAV_Exception_Forbidden(
					App::$l10n->t(
						'You do not have the permissions to edit this event.'
					)
				);
			}
		}
		$object = \OC_VObject::parse($data);
		list($type,$startdate,$enddate,$summary,$repeating,$uid,$isAlarm) = self::extractData($object);
	

		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_objects` SET `objecttype`=?,`startdate`=?,`enddate`=?,`repeating`=?,`summary`=?,`calendardata`=?,`lastmodified`= ?,`isalarm`= ? WHERE `id` = ?' );
		$stmt->execute(array($type,$startdate,$enddate,$repeating,$summary,$data,time(),$isAlarm,$oldobject['id']));

		Calendar::touchCalendar($oldobject['calendarid']);
		\OCP\Util::emitHook('OC_Calendar', 'editEvent', $oldobject['id']);

		return true;
	}

	/**
	 * @brief deletes an object
	 * @param integer $id id of object
	 * @return boolean
	 */
	public static function delete($id) {
		$oldobject = self::find($id);
		$calid = self::getCalendarid($id);
		
		$calendar = Calendar::find($calid);
		$oldvobject = \OC_VObject::parse($oldobject['calendardata']);
		if ($calendar['userid'] != \OCP\User::getUser()) {
			$shareMode=self::checkShareMode($calid);
			if($shareMode){
				$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $calid); //calid, not objectid !!!! 1111 one one one eleven
			}else{
				$sharedCalendar = \OCP\Share::getItemSharedWithBySource('event', $id); 
			}
			
			$sharedAccessClassPermissions = Object::getAccessClassPermissions($oldvobject);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_DELETE) || !($sharedAccessClassPermissions & \OCP\PERMISSION_DELETE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to delete this event.'
					)
				);
			}
		}
		$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_objects` WHERE `id` = ?' );
		$stmt->execute(array($id));
		
        
		//DELETE SHARED ONLY EVENT
		if(\OCP\Share::unshareAll('event', $id)){
			//if($delId=Object::checkSharedEvent($id)){
				$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_objects` WHERE `org_objid` = ?' );
		        $stmt->execute(array($id));
			//}
		}
		
        Calendar::touchCalendar($oldobject['calendarid']);
		
		\OCP\Util::emitHook('OC_Calendar', 'deleteEvent', $id);

		App::getVCategories()->purgeObject($id);

		return true;
	}

	/**
	 * @brief deletes an  object with the data provided by \Sabredav
	 * @param integer $cid calendar id
	 * @param string $uri the uri of the object
	 * @return boolean
	 */
	public static function deleteFromDAVData($cid,$uri) {
		$oldobject = self::findWhereDAVDataIs($cid, $uri);
		$calendar = Calendar::find($cid);
		if ($calendar['userid'] != \OCP\User::getUser()) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $cid);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_DELETE)) {
				throw new \OC_VObject_DAV_Exception_Forbidden(
					App::$l10n->t(
						'You do not have the permissions to delete this event.'
					)
				);
			}
		}
		$stmt = \OCP\DB::prepare( 'DELETE FROM `*PREFIX*clndr_objects` WHERE `calendarid`= ? AND `uri`=?' );
		$stmt->execute(array($cid,$uri));
		Calendar::touchCalendar($cid);
		\OCP\Util::emitHook('OC_Calendar', 'deleteEvent', $oldobject['id']);

		return true;
	}

	public static function moveToCalendar($id, $calendarid) {
		$calendar = Calendar::find($calendarid);
		if ($calendar['userid'] != \OCP\User::getUser()) {
			$sharedCalendar = \OCP\Share::getItemSharedWithBySource('calendar', $calendarid);
			if (!$sharedCalendar || !($sharedCalendar['permissions'] & \OCP\PERMISSION_DELETE)) {
				throw new \Exception(
					App::$l10n->t(
						'You do not have the permissions to add events to this calendar.'
					)
				);
			}
		}
		$stmt = \OCP\DB::prepare( 'UPDATE `*PREFIX*clndr_objects` SET `calendarid`=? WHERE `id`=?' );
		$stmt->execute(array($calendarid,$id));

		Calendar::touchCalendar($calendarid);
		\OCP\Util::emitHook('OC_Calendar', 'moveEvent', $id);

		return true;
	}

	/**
     * @brief Creates a UID
     * @return string
     */
    protected static function createUID() {
        return substr(md5(rand().time()),0,10);
    }

	/**
	 * @brief Extracts data from a vObject-Object
	 * @param \OC_VObject_VObject $object
	 * @return array
	 *
	 * [type, start, end, summary, repeating, uid]
	 */
	public static function extractData($object) {
		$return = array('',null,null,'',0,null,0);

		// Child to use
		$children = 0;
		$use = null;
		foreach($object->children as $property) {
			
			if($property->name == 'VEVENT') {
				$children++;
				$thisone = true;
               
				foreach($property->children as &$element) {
					if($element->name == 'VALARM') {
						$return[6] = 1;
					}
					if($element->name == 'RECURRENCE-ID') {
						$thisone = false;
					}
				} unset($element);

				if($thisone) {
					$use = $property;
				}
			}
			elseif($property->name == 'VTODO' || $property->name == 'VJOURNAL') {
				$return[0] = $property->name;
				foreach($property->children as &$element) {
						
					if($element->name == 'VALARM') {
						$return[6] = 1;
					}
						
					if($element->name == 'SUMMARY') {
						$return[3] = $element->value;
					}
					elseif($element->name == 'UID') {
						$return[5] = $element->value;
					}
					if($element->name == 'DUE') {
						$return[1] = self::getUTCforMDB($element->getDateTime());
					}
					
				};

				// Only one VTODO or VJOURNAL per object
				// (only one UID per object but a UID is required by a VTODO =>
				//    one VTODO per object)
				break;
			}
		}

		// find the data
		if(!is_null($use)) {
			$return[0] = $use->name;
			foreach($use->children as $property) {
				if($property->name == 'DTSTART') {
					$return[1] = self::getUTCforMDB($property->getDateTime());
				}
				elseif($property->name == 'DTEND') {
					$return[2] = self::getUTCforMDB($property->getDateTime());
				}
				elseif($property->name == 'SUMMARY') {
					$return[3] = $property->value;
				}
				elseif($property->name == 'RRULE') {
					$return[4] = 1;
				}
				elseif($property->name == 'UID') {
					$return[5] = $property->value;
				}
				
			}
		}

		// More than one child means reoccuring!
		if($children > 1) {
			$return[4] = 1;
		}
		return $return;
	}

	/**
	 * @brief DateTime to UTC string
	 * @param DateTime $datetime The date to convert
	 * @returns date as YYYY-MM-DD hh:mm
	 *
	 * This function creates a date string that can be used by MDB2.
	 * Furthermore it converts the time to UTC.
	 */
	public static function getUTCforMDB($datetime) {
		return date('Y-m-d H:i', $datetime->format('U'));
	}

	/**
	 * @brief returns the DTEND of an $vevent object
	 * @param object $vevent vevent object
	 * @return object
	 */
	public static function getDTEndFromVEvent($vevent) {
		if ($vevent->DTEND) {
			$dtend = $vevent->DTEND;
		}else{
			$dtend = clone $vevent->DTSTART;
			// clone creates a shallow copy, also clone DateTime
			$dtend->setDateTime(clone $dtend->getDateTime(), $dtend->getDateType());
			if ($vevent->DURATION) {
				$duration = strval($vevent->DURATION);
				$invert = 0;
				if ($duration[0] == '-') {
					$duration = substr($duration, 1);
					$invert = 1;
				}
				if ($duration[0] == '+') {
					$duration = substr($duration, 1);
				}
				$interval = new \DateInterval($duration);
				$interval->invert = $invert;
				$dtend->getDateTime()->add($interval);
			}
		}
		return $dtend;
	}

	/**
	 * @brief Remove all properties which should not be exported for the AccessClass Confidential
	 * @param string $id Event ID
	 * @param \OC_VObject_VObject $vobject Sabre VObject
	 * @return object
	 */
	public static function cleanByAccessClass($id, $vobject) {

		// Do not clean your own calendar
		if(Object::getowner($id) === \OCP\USER::getUser()) {
			return $vobject;
		}

		if(isset($vobject->VEVENT)) {
			$velement = $vobject->VEVENT;
		}
		elseif(isset($vobject->VJOURNAL)) {
			$velement = $vobject->VJOURNAL;
		}
		elseif(isset($vobject->VTODO)) {
			$velement = $vobject->VTODO;
		}

		if(isset($velement->CLASS) && $velement->CLASS->value == 'CONFIDENTIAL') {
			foreach ($velement->children as &$property) {
				switch($property->name) {
					case 'CREATED':
					case 'DTSTART':
					case 'RRULE':
					case 'DURATION':
					case 'DTEND':
					case 'CLASS':
					case 'UID':
						break;
					case 'SUMMARY':
						$property->value = App::$l10n->t('Busy');
						break;
					default:
						$velement->__unset($property->name);
						unset($property);
						break;
				}
			}
		}
		return $vobject;
	}

	/**
	 * @brief Get the permissions determined by the access class of an event/todo/journal
	 * @param Sabre_VObject $vobject Sabre VObject
	 * @return (int) $permissions - CRUDS permissions
	 * @see \OCP\Share
	 */
	public static function getAccessClassPermissions($vobject) {
		$velement='';	
		if(isset($vobject->VEVENT)) {
			$velement = $vobject->VEVENT;
		}
		elseif(isset($vobject->VJOURNAL)) {
			$velement = $vobject->VJOURNAL;
		}
		elseif(isset($vobject->VTODO)) {
			$velement = $vobject->VTODO;
		}

		if($velement!='') {
			$accessclass = $velement->getAsString('CLASS');
		   return App::getAccessClassPermissions($accessclass);
		}else return false;
	}

	/**
	 * @brief returns the options for the access class of an event
	 * @return array - valid inputs for the access class of an event
	 */
	public static function getAccessClassOptions($l10n) {
		return array(
			'PUBLIC'       => (string)$l10n->t('Public'),
			'PRIVATE'      => (string)$l10n->t('Private'),
			'CONFIDENTIAL' => (string)$l10n->t('Confidential')
		);
	}

	/**
	 * @brief returns the options for the repeat rule of an repeating event
	 * @return array - valid inputs for the repeat rule of an repeating event
	 */
	public static function getRepeatOptions($l10n) {
		return array(
			'doesnotrepeat' => (string)$l10n->t('Does not repeat'),
			'daily'         => (string)$l10n->t('Daily'),
			'weekly'        => (string)$l10n->t('Weekly'),
			'weekday'       => (string)$l10n->t('Every Weekday'),
			'biweekly'      => (string)$l10n->t('Bi-Weekly'),
			'monthly'       => (string)$l10n->t('Monthly'),
			'yearly'        => (string)$l10n->t('Yearly')
		);
	}

	/**
	 * @brief returns the options for the end of an repeating event
	 * @return array - valid inputs for the end of an repeating events
	 */
	public static function getEndOptions($l10n) {
		return array(
			'never' => (string)$l10n->t('never'),
			'count' => (string)$l10n->t('by occurrences'),
			'date'  => (string)$l10n->t('by date')
		);
	}

	/**
	 * @brief returns the options for an monthly repeating event
	 * @return array - valid inputs for monthly repeating events
	 */
	public static function getMonthOptions($l10n) {
		return array(
			'monthday' => (string)$l10n->t('by monthday'),
			'weekday'  => (string)$l10n->t('by weekday')
		);
	}

	/**
	 * @brief returns the options for an weekly repeating event
	 * @return array - valid inputs for weekly repeating events
	 */
	public static function getWeeklyOptions($l10n) {
		return array(
			'MO' => (string)$l10n->t('Monday'),
			'TU' => (string)$l10n->t('Tuesday'),
			'WE' => (string)$l10n->t('Wednesday'),
			'TH' => (string)$l10n->t('Thursday'),
			'FR' => (string)$l10n->t('Friday'),
			'SA' => (string)$l10n->t('Saturday'),
			'SU' => (string)$l10n->t('Sunday')
		);
	}
	
    public static function getWeeklyOptionsCheck($sWeekDay) {
		 $checkArray=array(
			'Mon' =>'MO',
			'Tue' => 'TU',
			'Wen' => 'WE',
			'Thu' =>'TH',
			'Fri' => 'FR',
			'Sat' =>'SA',
			'Sun' => 'SU'
		);
		return $checkArray[$sWeekDay];
	}
	/**
	 * @brief returns the options for an monthly repeating event which occurs on specific weeks of the month
	 * @return array - valid inputs for monthly repeating events
	 */
	public static function getWeekofMonth($l10n) {
		return array(
			'auto' => (string)$l10n->t('events week of month'),
			'1' => (string)$l10n->t('first'),
			'2' => (string)$l10n->t('second'),
			'3' => (string)$l10n->t('third'),
			'4' => (string)$l10n->t('fourth'),
			'5' => (string)$l10n->t('fifth'),
			'-1' => (string)$l10n->t('last')
		);
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific days of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByYearDayOptions() {
		$return = array();
		foreach(range(1,366) as $num) {
			$return[(string) $num] = (string) $num;
		}
		return $return;
	}

	/**
	 * @brief returns the options for an yearly or monthly repeating event which occurs on specific days of the month
	 * @return array - valid inputs for yearly or monthly repeating events
	 */
	public static function getByMonthDayOptions() {
		$return = array();
		foreach(range(1,31) as $num) {
			$return[(string) $num] = (string) $num;
		}
		return $return;
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific month of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByMonthOptions($l10n) {
		return array(
			'1'  => (string)$l10n->t('January'),
			'2'  => (string)$l10n->t('February'),
			'3'  => (string)$l10n->t('March'),
			'4'  => (string)$l10n->t('April'),
			'5'  => (string)$l10n->t('May'),
			'6'  => (string)$l10n->t('June'),
			'7'  => (string)$l10n->t('July'),
			'8'  => (string)$l10n->t('August'),
			'9'  => (string)$l10n->t('September'),
			'10' => (string)$l10n->t('October'),
			'11' => (string)$l10n->t('November'),
			'12' => (string)$l10n->t('December')
		);
	}

	/**
	 * @brief returns the options for an yearly repeating event
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getYearOptions($l10n) {
			/*'byweekno'  => (string)$l10n->t('by weeknumber(s)'),*/
		return array(
			'bydate' => (string)$l10n->t('by events date'),
			'byyearday' => (string)$l10n->t('by yearday(s)'),
			'bydaymonth'  => (string)$l10n->t('by day and month')
		);
	}

	/**
	 * @brief returns the options for an yearly repeating event which occurs on specific week numbers of the year
	 * @return array - valid inputs for yearly repeating events
	 */
	public static function getByWeekNoOptions() {
		return range(1, 52);
	}

     /**
	 * @brief returns the options for reminder choose
	 * @return array - valid inputs for reminder options
	 */
	public static function getReminderOptions($l10n) {
		/*'messageaudio'  => (string)$l10n->t('messageaudio'),*/	
		return array(
			'none' => (string)$l10n->t('None'),
			'DISPLAY' => (string)$l10n->t('Message'),
			'EMAIL'  => (string)$l10n->t('Email')
		);
	}
	
     /**
	 * @brief returns the options for reminder timing choose
	 * @return array - valid inputs for reminder timing options
	 */
	public static function getReminderTimeOptions($l10n) {
		return array(
			'minutesbefore' => (string)$l10n->t('Minutes before'),
			'hoursbefore'  => (string)$l10n->t('Hours before'),
			'daysbefore'  => (string)$l10n->t('Days before'),
			'minutesafter' => (string)$l10n->t('Minutes after'),
			'hoursafter'  => (string)$l10n->t('Hours after'),
			'daysafter'  => (string)$l10n->t('Days after'),
			'ondate'  => (string)$l10n->t('on'),
		);
	}

     /**
	 * @brief returns the options for reminder timing choose
	 * @return array - valid inputs for reminder timing options
	 */
	public static function getReminderTimeParsingOptions() {
		return array(
			'minutesbefore' =>array('timedescr'=>'M','timehistory'=>'-PT'),
			'hoursbefore'  => array('timedescr'=>'H','timehistory'=>'-PT'),
			'daysbefore'  => array('timedescr'=>'D','timehistory'=>'-PT'),
			'minutesafter' => array('timedescr'=>'M','timehistory'=>'+PT'),
			'hoursafter'  => array('timedescr'=>'H','timehistory'=>'+PT'),
			'daysafter'  => array('timedescr'=>'D','timehistory'=>'+PT'),
			'ondate'  =>array('timedescr'=>'D','timehistory'=>'+PT'),
		);
	}
	/**
	 * @brief validates a request
	 * @param array $request
	 * @return mixed (array / boolean)
	 */
	public static function validateRequest($request) {
		$errnum = 0;
		$errarr = array('title'=>'false', 'cal'=>'false', 'from'=>'false', 'fromtime'=>'false', 'to'=>'false', 'totime'=>'false', 'endbeforestart'=>'false');
		if($request['title'] == '') {
			$errarr['title'] = 'true';
			$errnum++;
		}

		$fromday = substr($request['from'], 0, 2);
		$frommonth = substr($request['from'], 3, 2);
		$fromyear = substr($request['from'], 6, 4);
		if(!checkdate($frommonth, $fromday, $fromyear)) {
			$errarr['from'] = 'true';
			$errnum++;
		}
		$allday = isset($request['allday']);
		if(!$allday && self::checkTime(urldecode($request['fromtime']))) {
			$errarr['fromtime'] = 'true';
			$errnum++;
		}

		$today = substr($request['to'], 0, 2);
		$tomonth = substr($request['to'], 3, 2);
		$toyear = substr($request['to'], 6, 4);
		if(!checkdate($tomonth, $today, $toyear)) {
			$errarr['to'] = 'true';
			$errnum++;
		}
		if($request['repeat'] != 'doesnotrepeat') {
			if(is_nan($request['interval']) && $request['interval'] != '') {
				$errarr['interval'] = 'true';
				$errnum++;
			}
			if(array_key_exists('repeat', $request) && !array_key_exists($request['repeat'], self::getRepeatOptions(App::$l10n))) {
				$errarr['repeat'] = 'true';
				$errnum++;
			}
			if(array_key_exists('advanced_month_select', $request) && !array_key_exists($request['advanced_month_select'], self::getMonthOptions(App::$l10n))) {
				$errarr['advanced_month_select'] = 'true';
				$errnum++;
			}
			if(array_key_exists('advanced_year_select', $request) && !array_key_exists($request['advanced_year_select'], self::getYearOptions(App::$l10n))) {
				$errarr['advanced_year_select'] = 'true';
				$errnum++;
			}
			if(array_key_exists('weekofmonthoptions', $request) && !array_key_exists($request['weekofmonthoptions'], self::getWeekofMonth(App::$l10n))) {
				$errarr['weekofmonthoptions'] = 'true';
				$errnum++;
			}
			if($request['end'] != 'never') {
				if(!array_key_exists($request['end'], self::getEndOptions(App::$l10n))) {
					$errarr['end'] = 'true';
					$errnum++;
				}
				if($request['end'] == 'count' && is_nan($request['byoccurrences'])) {
					$errarr['byoccurrences'] = 'true';
					$errnum++;
				}
				if($request['end'] == 'date') {
					list($bydate_day, $bydate_month, $bydate_year) = explode('-', $request['bydate']);
					if(!checkdate($bydate_month, $bydate_day, $bydate_year)) {
						$errarr['bydate'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('weeklyoptions', $request)) {
				foreach($request['weeklyoptions'] as $option) {
					if(!in_array($option, self::getWeeklyOptions(App::$l10n))) {
						$errarr['weeklyoptions'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('byyearday', $request)) {
				foreach($request['byyearday'] as $option) {
					if(!array_key_exists($option, self::getByYearDayOptions())) {
						$errarr['byyearday'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('weekofmonthoptions', $request)) {
				if(is_nan((double)$request['weekofmonthoptions'])) {
					$errarr['weekofmonthoptions'] = 'true';
					$errnum++;
				}
			}
			if(array_key_exists('bymonth', $request)) {
				foreach($request['bymonth'] as $option) {
					if(!in_array($option, self::getByMonthOptions(App::$l10n))) {
						$errarr['bymonth'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('byweekno', $request)) {
				foreach($request['byweekno'] as $option) {
					if(!array_key_exists($option, self::getByWeekNoOptions())) {
						$errarr['byweekno'] = 'true';
						$errnum++;
					}
				}
			}
			if(array_key_exists('bymonthday', $request)) {
				foreach($request['bymonthday'] as $option) {
					if(!array_key_exists($option, self::getByMonthDayOptions())) {
						$errarr['bymonthday'] = 'true';
						$errnum++;
					}
				}
			}
		}
		if(!$allday && self::checkTime(urldecode($request['totime']))) {
			$errarr['totime'] = 'true';
			$errnum++;
		}
		if($today < $fromday && $frommonth == $tomonth && $fromyear == $toyear) {
			$errarr['endbeforestart'] = 'true';
			$errnum++;
		}
		if($today == $fromday && $frommonth > $tomonth && $fromyear == $toyear) {
			$errarr['endbeforestart'] = 'true';
			$errnum++;
		}
		if($today == $fromday && $frommonth == $tomonth && $fromyear > $toyear) {
			$errarr['endbeforestart'] = 'true';
			$errnum++;
		}
		if(!$allday && $fromday == $today && $frommonth == $tomonth && $fromyear == $toyear) {
			list($tohours, $tominutes) = explode(':', $request['totime']);
			list($fromhours, $fromminutes) = explode(':', $request['fromtime']);
			if($tohours < $fromhours) {
				$errarr['endbeforestart'] = 'true';
				$errnum++;
			}
			if($tohours == $fromhours && $tominutes < $fromminutes) {
				$errarr['endbeforestart'] = 'true';
				$errnum++;
			}
		}
		if ($errnum)
		{
			return $errarr;
		}
		return false;
	}

	/**
	 * @brief validates time
	 * @param string $time
	 * @return boolean
	 */
	protected static function checkTime($time) {
		if(strpos($time, ':') === false ) {
			return true;
		}
		list($hours, $minutes) = explode(':', $time);
		return empty($time)
			|| $hours < 0 || $hours > 24
			|| $minutes < 0 || $minutes > 60;
	}

	/**
	 * @brief creates an VCalendar Object from the request data
	 * @param array $request
	 * @return object created $vcalendar
	 */	public static function createVCalendarFromRequest($request) {
		$vcalendar = new \OC_VObject('VCALENDAR');
		$vcalendar->add('PRODID', 'ownCloud Calendar');
		$vcalendar->add('VERSION', '2.0');

		$vevent = new \OC_VObject('VEVENT');
		$vcalendar->add($vevent);

		$vevent->setDateTime('CREATED', 'now', \Sabre\VObject\Property\DateTime::UTC);

		$vevent->setUID();
		return self::updateVCalendarFromRequest($request, $vcalendar);
	}

	/**
	 * @brief updates an VCalendar Object from the request data
	 * @param array $request
	 * @param object $vcalendar
	 * @return object updated $vcalendar
	 */
	public static function updateVCalendarFromRequest($request, $vcalendar) {
		$accessclass = $request["accessclass"];
		$title = $request["title"];
		$location = $request["location"];
		$categories = $request["categories"];
		$allday = isset($request["allday"]);
		$from = $request["from"];
		$to  = $request["to"];
		
		$checkDateFrom=strtotime($from);
		$checkWeekDay=date("D",$checkDateFrom);
		$weekDay=self::getWeeklyOptionsCheck($checkWeekDay);
		
		if (!$allday) {
			$fromtime = $request['fromtime'];
			$totime = $request['totime'];
		}
		
		$vevent = $vcalendar->VEVENT;
		/*REMINDER NEW*/
		if($request['reminder']!='none'){
			$aTimeTransform=self::getReminderTimeParsingOptions();	
			if($vevent -> VALARM){
				$valarm=$vevent -> VALARM;
			}else{
				$valarm = new \OC_VObject('VALARM');
                $vevent->add($valarm);
			}
			if($request['reminder']=='DISPLAY' || $request['reminder']=='EMAIL'){
				
				$valarm->setString('ATTENDEE','');
					
				if($request['remindertimeselect']!='ondate') {
					$tTime=$aTimeTransform[$request['remindertimeselect']]['timehistory'].intval($request['remindertimeinput']).$aTimeTransform[$request['remindertimeselect']]['timedescr']	;
				    $valarm->setString('TRIGGER',$tTime);
				}
				if($request['remindertimeselect']=='ondate') {
					$timezone = App::getTimezone();
	                $timezone = new \DateTimeZone($timezone);
	                $ReminderTime = new \DateTime($request['reminderdate'].' '.$request['remindertime'], $timezone);
	                $valarm->setDateTime('TRIGGER', $ReminderTime, \Sabre\VObject\Property\DateTime::LOCALTZ);
				}
				if($request['reminder']=='EMAIL'){
					//ATTENDEE:mailto:sebastian.doell@libasys.de
					$valarm->setString('ATTENDEE','mailto:'.$request['reminderemailinput']);
				}
			}
			$valarm->setString('DESCRIPTION', 'owncloud');
			$valarm->setString('ACTION', $request['reminder']);
		}
		if($request['reminder']=='none'){
			if($vevent -> VALARM){
				$vevent->setString('VALARM','');
			}
		}
		//$email='sebastian.doell@libasys.de';
		/*BEGIN:VALARM
			TRIGGER:-PT45M
			ACTION:DISPLAY
			DESCRIPTION:Open-XChange
		 * TRIGGER;VALUE=DATE-TIME:20131126T200000Z
			END:VALARM*/
		/*	
        $valarm = new \OC_VObject('VALARM');
        $vevent->add($valarm);
		$valarm->addProperty('TRIGGER','-PT45M');
		$valarm->addProperty('ACTION','DISPLAY');
		$valarm->addProperty('DESCRIPTION','owncloud alarm');*/
		
		//ORGANIZER;CN=email@email.com;EMAIL=email@email.com:MAILTO:email@email.com
		//$vevent->addProperty('ORGANIZER;CN='.$email.';EMAIL='.$email,'MAILTO:'.$email);
		//ATTENDEE;CN="Ryan Gr�nborg";CUTYPE=INDIVIDUAL;EMAIL="ryan@tv-glad.org";PARTSTAT=ACCEPTED:mailto:ryan@tv-glad.org
		//ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE; CN="Full Name":MAILTO:user@domain.com
		//ATTENDEE;CN="admin";CUTYPE=INDIVIDUAL;PARTSTAT=ACCEPTED:/oc50/remote.php/caldav/principals/admin/
		//$vevent->addProperty('ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN=admin','MAILTO:'.$email);
		//$vevent->addProperty('ATTENDEE;CN="admin";CUTYPE=INDIVIDUAL;PARTSTAT=ACCEPTED','http://127.0.0.1/oc50/remote.php/caldav/principals/admin/');
		//$vevent->addProperty('ATTENDEE;CN="sebastian";CUTYPE=INDIVIDUAL;PARTSTAT=ACCEPTED','http://127.0.0.1/oc50/remote.php/caldav/principals/sebastian/');
		
		$description = $request["description"];
		$repeat = $request["repeat"];
		if($repeat != 'doesnotrepeat') {
			$rrule = '';
			$interval = $request['interval'];
			$end = $request['end'];
			$byoccurrences = $request['byoccurrences'];
			
			switch($repeat) {
				case 'daily':
					$rrule .= 'FREQ=DAILY';
					break;
				case 'weekly':
					$rrule .= 'FREQ=WEEKLY';
					if(array_key_exists('weeklyoptions', $request)) {
						$byday = '';
						$daystrings = array_flip(self::getWeeklyOptions(App::$l10n));
						foreach($request['weeklyoptions'] as $days) {
							if($byday == '') {
								$byday .= $daystrings[$days];
							}else{
								$byday .= ',' .$daystrings[$days];
							}
						}
						$rrule .= ';BYDAY=' . $byday;
					}
					break;
				case 'weekday':
					$rrule .= 'FREQ=WEEKLY';
					$rrule .= ';BYDAY=MO,TU,WE,TH,FR';
					break;
				case 'biweekly':
					$rrule .= 'FREQ=WEEKLY';
					$interval = $interval * 2;
					break;
				case 'monthly':
					$rrule .= 'FREQ=MONTHLY';
					if($request['advanced_month_select'] == 'monthday') {
						if(array_key_exists('bymonthday', $request)) {
							$bymonthday = '';
							foreach($request['bymonthday'] as $monthday) {
								if($bymonthday == '') {
								      $bymonthday .= $monthday;
								}else{
								      $bymonthday .= ',' . $monthday;
								}
							}
							$rrule .= ';BYMONTHDAY=' . $bymonthday;

						}
					}elseif($request['advanced_month_select'] == 'weekday') {
						if($request['weekofmonthoptions'] == 'auto') {
							list($_day, $_month, $_year) = explode('-', $from);
							$weekofmonth = floor($_day/7);
						}else{
							$weekofmonth = $request['weekofmonthoptions'];
						}
						$days = array_flip(self::getWeeklyOptions(App::$l10n));
						$byday = '';
						foreach($request['weeklyoptions'] as $day) {
							if($byday == '') {
								$byday .= $weekofmonth . $days[$day];
							}else{
								$byday .= ',' . $weekofmonth . $days[$day];
							}
						}
						if($byday == '') {
							$byday = 'MO,TU,WE,TH,FR,SA,SU';
						}
						$rrule .= ';BYDAY=' . $byday;
						
						
						
					}
					break;
				case 'yearly':
					$rrule .= 'FREQ=YEARLY';
					if($request['advanced_year_select'] == 'bydate') {

					}elseif($request['advanced_year_select'] == 'byyearday') {
						list($_day, $_month, $_year) = explode('-', $from);
						$byyearday = date('z', mktime(0,0,0, $_month, $_day, $_year)) + 1;
						if(array_key_exists('byyearday', $request)) {
							foreach($request['byyearday'] as $yearday) {
								$byyearday .= ',' . $yearday;
							}
						}
						$rrule .= ';BYYEARDAY=' . $byyearday;
					}elseif($request['advanced_year_select'] == 'byweekno') {
						//list($_day, $_month, $_year) = explode('-', $from);
						//Fix
						$days = array_flip(self::getWeeklyOptions(App::$l10n));
						$byweekno = '';
						foreach($request['byweekno'] as $weekno) {
							if($byweekno == '') {
								$byweekno = $weekno;
							}else{
								$byweekno .= ',' . $weekno;
							}
						}
						$rrule .= ';BYWEEKNO=' . $byweekno;
						$byday = '';
							foreach($request['weeklyoptions'] as $day) {
								if($byday == '') {
								      $byday .= $days[$day];
								}else{
								      $byday .= ',' . $days[$day];
								}
							}
							$rrule .= ';BYDAY=' . $byday;
						
						
					}elseif($request['advanced_year_select'] == 'bydaymonth') {
						//FIXED Removed Weekly Options
						
						if(array_key_exists('bymonth', $request)) {
							$monthes = array_flip(self::getByMonthOptions(App::$l10n));
							$bymonth = '';
							foreach($request['bymonth'] as $month) {
								if($bymonth == '') {
								      $bymonth .= $monthes[$month];
								}else{
								      $bymonth .= ',' . $monthes[$month];
								}
							}
							$rrule .= ';BYMONTH=' . $bymonth;

						}
						if(array_key_exists('bymonthday', $request)) {
							$bymonthday = '';
							foreach($request['bymonthday'] as $monthday) {
								if($bymonthday == '') {
								      $bymonthday .= $monthday;
								}else{
								      $bymonthday .= ',' . $monthday;
								}
							}
							$rrule .= ';BYMONTHDAY=' . $bymonthday;

						}
					}
					break;
				default:
					break;
			}
			if($interval != '') {
				$rrule .= ';INTERVAL=' . $interval;
			}
			if($end == 'count') {
				$rrule .= ';COUNT=' . $byoccurrences;
			}
			if($end == 'date') {
				list($bydate_day, $bydate_month, $bydate_year) = explode('-', $request['bydate']);
				$rrule .= ';UNTIL=' . $bydate_year . $bydate_month . $bydate_day;
			}
			$vevent->setString('RRULE', $rrule);
			$repeat = "true";
		}else{
			$repeat = "false";
		}
         if($request["repeat"] == 'doesnotrepeat') {
         	$vevent->setString('RRULE', '');
         }

		$vevent->setDateTime('LAST-MODIFIED', 'now', \Sabre\VObject\Property\DateTime::UTC);
		$vevent->setDateTime('DTSTAMP', 'now', \Sabre\VObject\Property\DateTime::UTC);
		$vevent->setString('SUMMARY', $title);
        
        $oldStartTime=$vevent->DTSTART;
		
	 //  if($request["repeat"] == 'doesnotrepeat') {
			if($allday) {
	                $start = new \DateTime($from);
	                $end = new \DateTime($to.' +1 day');
	                $vevent->setDateTime('DTSTART', $start, \Sabre\VObject\Property\DateTime::DATE);
	                $vevent->setDateTime('DTEND', $end, \Sabre\VObject\Property\DateTime::DATE);
	        }else{
	                $timezone = App::getTimezone();
	                $timezone = new \DateTimeZone($timezone);
	                $start = new \DateTime($from.' '.$fromtime, $timezone);
	                $end = new \DateTime($to.' '.$totime, $timezone);
	                $vevent->setDateTime('DTSTART', $start, \Sabre\VObject\Property\DateTime::LOCALTZ);
	                $vevent->setDateTime('DTEND', $end, \Sabre\VObject\Property\DateTime::LOCALTZ);
	        }
	   //}else{
	   	
	   //}
        
		if($vevent->EXDATE){
			$calcStartOld=$oldStartTime->getDateTime()->format('U');	
			$calcStartNew= $start->format('U');
			$timeDiff=$calcStartNew-$calcStartOld;
			if($timeDiff!=0){
					$delta = new \DateInterval('P0D');	
					
					$dMinutes=(int)($timeDiff/60);
					//$dTage=(int) ($dMinutes/(3600*24));
					//$delta->d = $dTage;
					$delta->i = $dMinutes;
					
					\OCP\Util::writeLog('calendar','edit: ->'.$dMinutes, \OCP\Util::DEBUG);
					
					
					if ($allday) {
						$start_type = \Sabre\VObject\Property\DateTime::DATE;
					}else{
						$start_type = \Sabre\VObject\Property\DateTime::LOCALTZ;
					}	
					$calcStart=new \DateTime($oldStartTime);
					$aExt=$vevent->EXDATE;
					$vevent->setString('EXDATE','');
					 $timezone = App::getTimezone();
					foreach($aExt as $param){
						$dateTime = new \DateTime($param->value);
						$datetime_element = new \Sabre\VObject\Property\DateTime('EXDATE');
						$datetime_element -> setDateTime($dateTime->add($delta),$start_type);
					    $vevent->addProperty('EXDATE;TZID='.$timezone,(string) $datetime_element);
						//$output.=$dateTime->format('Ymd\THis').':'.$datetime_element.'success';
					}
			}
			
		}
		
		
		unset($vevent->DURATION);

		$vevent->setString('CLASS', $accessclass);
		$vevent->setString('LOCATION', $location);
		$vevent->setString('DESCRIPTION', $description);
		$vevent->setString('CATEGORIES', $categories);

		/*if($repeat == "true") {
			$vevent->RRULE = $repeat;
		}*/

		return $vcalendar;
	}

	/**
	 * @brief returns the owner of an object
	 * @param integer $id
	 * @return string
	 */
	public static function getowner($id) {
		$event = self::find($id);
		$cal = Calendar::find($event['calendarid']);
		//\OCP\Util::writeLog('calendar','Access Class'.$cal, \OCP\Util::DEBUG);
		if($cal === false || is_array($cal) === false){
			return null;
		}
		if(array_key_exists('userid', $cal)){
			return $cal['userid'];
		}else{
			return null;
		}
	}

	/**
	 * @brief returns the calendarid of an object
	 * @param integer $id
	 * @return integer
	 */
	public static function getCalendarid($id) {
		$event = self::find($id);
		return $event['calendarid'];
	}

	/**
	 * @brief checks if an object is repeating
	 * @param integer $id
	 * @return boolean
	 */
	public static function isrepeating($id) {
		$event = self::find($id);
		return ($event['repeating'] == 1)?true:false;
	}

	/**
	 * @brief converts the start_dt and end_dt to a new timezone
	 * @param object $dtstart
	 * @param object $dtend
	 * @param boolean $allday
	 * @param string $tz
	 * @return array
	 */
	public static function generateStartEndDate($dtstart, $dtend, $allday, $tz) {
		$start_dt = $dtstart->getDateTime();
		$end_dt = $dtend->getDateTime();
		//\OCP\Util::writeLog('calendar','TZ: ->'.$tz, \OCP\Util::DEBUG);
		$return = array();
		if($allday) {
			$return['start'] = $start_dt->format('Y-m-d');
			$end_dt->modify('-1 minute');
			while($start_dt >= $end_dt) {
				$end_dt->modify('+1 day');
			}
			$return['end'] = $end_dt->format('Y-m-d');
		}else{
			$start_dt->setTimezone(new \DateTimeZone($tz));
			$end_dt->setTimezone(new \DateTimeZone($tz));
			$return['start'] = $start_dt->format('Y-m-d H:i:s');
			$return['end'] = $end_dt->format('Y-m-d H:i:s');
		}
		return $return;
	}
}
