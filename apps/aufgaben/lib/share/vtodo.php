<?php


namespace OCA\Aufgaben;

class Share_Backend_Vtodo implements \OCP\Share_Backend {

	const FORMAT_TODO = 0;
	
	private static $vtodo;
	
	public function isValidSource($itemSource, $uidOwner) {
	     	
	     self::$vtodo = \OCA\Calendar\Object::find($itemSource);
		if (self::$vtodo) {
			return true;
		}
		return false;
		
		return true;
	}

	public function generateTarget($itemSource, $shareWith, $exclude = null) {
		if(!self::$vtodo) {
			self::$vtodo = \OCA\Calendar\Object::find($itemSource);
		}
	
		return self::$vtodo['summary'];
	}

	public function formatItems($items, $format, $parameters = null) {
		$vtodos = array();
		if ($format == self::FORMAT_TODO) {
			$user_timezone = \OCA\Calendar\App::getTimezone();
			foreach ($items as $item) {
				if(!App::checkSharedTodo($item['item_source'])){	
					$event = App::getEventObject( $item['item_source'] );
					$vcalendar = \OC_VObject::parse($event['calendardata']);
					$vtodo = $vcalendar->VTODO;
				    $accessclass = $vtodo -> getAsString('CLASS');
				    
					if($accessclass=='' || $accessclass=='PUBLIC'){
						$permissions['permissions'] =$item['permissions'];
						$permissions['calendarcolor'] ='#cccccc';
						$permissions['isOnlySharedTodo'] =true;
						$permissions['calendarowner'] =\OCA\Calendar\Object::getowner($item['item_source']);
						//\OCP\Util::writeLog('calendar','Cal Owner :'.$permissions['calendarowner'] ,\OCP\Util::DEBUG);
						$permissions['iscompleted'] =false;
						if($vtodo->COMPLETED) $permissions['iscompleted'] =true;
						
					     $vtodos[]=App::arrayForJSON($item['item_source'], $vtodo, $user_timezone,$permissions,$event);
					}
				}	
				//$vtodos[] = $vtodo;
			}
		}
		return $vtodos;
	}
	
}