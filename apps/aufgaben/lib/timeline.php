<?php

namespace OCA\Aufgaben;


Timeline::$l10n = new \OC_L10N('aufgaben');
class Timeline{
	
	private $nowTime=0;
	private $aCalendar=array();
	public static $l10n;
	private $sMode='';
	private $iCalId=0;
	
	private $aSharedTasks=array();
	private $aTasks=array();
	private $aTasksOutput=array();
	private $dUserTimeZone = '';
	private $iCalPermissions = 0;
	private $taskOutPutbyTime=array('today','tomorrow','week','missed','notermin','commingsoon');
	
	public function __construct(){
		$this->nowTime=time();
		$this->dUserTimeZone=\OCA\Calendar\App::getTimezone();
	}
	
	public function setCalendars($aCalendar){
		//	$aCalendarNew='';
		foreach($aCalendar as $cal){
			$this->aCalendar[$cal['id']]=$cal;
			//\OCP\Util::writeLog('calendar','AlarmDB ID :'.$cal['id'] ,\OCP\Util::DEBUG);	
		}	
			
		//$this->aCalendar=$aCalendarNew;
	}
	public function setCalendarPermissions($iPermissions){
		$this->iCalPermissions=$iPermissions;
	}
	
	public function setSharedTasks($aShareTasks){
		$this->aSharedTasks=$aShareTasks;
	}
	
	public function setTasks($aTasks){
		$this->aTasks=$aTasks;
	}
	
	public function setCalendarId($iCalId){
		$this->iCalId=$iCalId;
	}
	
	public function setTimeLineMode($sMode){
		$this->sMode=$sMode;
	}
	
	public function getToday(){
		  return date('d.m.Y',$this->nowTime);
	}
	
	public function getTommorow(){
		  return date('d.m.Y',$this->nowTime+(24*3600));
	}
	
	public function getStartofTheWeek(){
		   $iTagAkt=date("w",$this->nowTime);
   	       $firstday=1;
     	   $iBackCalc=(($iTagAkt-$firstday)*24*3600);
	     
	       $getStartdate=$this->nowTime-$iBackCalc;
		   
		   return date('d.m.Y',$getStartdate);
	}
	
	public function getEndofTheWeek(){
		    	
		    $iForCalc=(6*24*3600);
		    $getEnddate=strtotime($this->getStartofTheWeek())+$iForCalc;
		   
		   return date('d.m.Y',$getEnddate);
	}
	
	public function generateAddonCalendarTodo(){
	
		 $today=strtotime($this->getToday());
		 $tomorrow = strtotime($this->getTommorow()); 
		 $beginnWeek = strtotime($this->getStartofTheWeek()); 
		 $endWeek = strtotime($this->getEndofTheWeek()); 
		
		$taskOutput='';
		
		// foreach( $this->aCalendar as $cal ) {
		 	 $calendar_tasks = App::all($this->aCalendar);
			
			 foreach( $calendar_tasks as $taskInfo ) {
				  
					$taskOutput='';
					  	
				  	  if($taskInfo['objecttype']!='VTODO') {
			                        continue;
			            }
					
					$calId=(string)$taskInfo['calendarid'];
					   
					$object = \OC_VObject::parse($taskInfo['calendardata']);
					$vtodo = $object->VTODO; 
					$completed = $vtodo->COMPLETED;
					$accessclass=$vtodo->getAsString('CLASS');
					if($this->aCalendar[$calId]['userid']!=\OCP\USER::getUser()){
						if($accessclass!='' && $accessclass!='PUBLIC'){
							 continue;
						}
					}
					if(!$completed){
				
					$due = $vtodo->DUE;
					
					$addPrivateImg='';
					if ($accessclass!='' && ($accessclass === 'PRIVATE')){
						
						$addPrivateImg='<img title="private" style="margin-top:3px;margin-bottom:-3px;margin-right:2px;" width="12" class="svg" src="'.\OCP\Util::imagePath( 'core', 'actions/lock.svg' ).'" />';
	
					}
					if ($accessclass!='' && ($accessclass === 'CONFIDENTIAL')){
						$addPrivateImg='<img title="confidential" style="margin-top:3px;margin-bottom:-3px;margin-right:2px;" class="svg" src="'.\OCP\Util::imagePath( 'core', 'actions/toggle.svg' ).'" />';
					}
					
					$Summary=$vtodo->getAsString('SUMMARY');
					$addShareImg='';
					 if($taskInfo['shared']) {
					 	$addShareImg='<img title="shared" style="margin-top:3px;margin-bottom:-3px;margin-right:2px;" class="svg" src="'.\OCP\Util::imagePath( 'core', 'actions/shared.svg' ).'" />';
					 }
					 $addAlarmImg='';
					 if($taskInfo['isalarm']) {
					 	$addAlarmImg='<img title="reminder" style="margin-top:3px;margin-bottom:-3px;margin-right:2px;" class="svg" src="'.\OCP\Util::imagePath( 'core', 'actions/clock.svg' ).'" />';
					 }
					$dateTask=$addPrivateImg.$addAlarmImg.$addShareImg.$Summary;
		
					if($due!=''){
						$dateTask=$due->getDateTime()->format('d.m.Y H:i').'<br  /><span>'.$addPrivateImg.$addAlarmImg.$addShareImg.$Summary.'</span>';
					}
					
					
					 
			        $taskOutput.='<li class="taskListRow" data-taskid="'.$taskInfo['id'].'">
			                              <span class="colorCal" style="margin-top:6px;background-color:'.$this->aCalendar[$taskInfo['calendarid']]['calendarcolor'].';">&nbsp;</span>
			                              <input class="inputTasksRow" type="checkbox" /> '.$dateTask.'
			                              </li>';
					
					if($due){
						$dueTmp = $due->getDateTime()->format('d.m.Y');
						$due = strtotime($dueTmp); 
						$bCheck=false;
						
							if($due==$today){
								$this->taskOutPutbyTime['today'].=$taskOutput;
								$bCheck=true;
							}
							
							if($due==$tomorrow){
								$this->taskOutPutbyTime['tomorrow'].=$taskOutput;
								$bCheck=true;
							}
							if($due>=$beginnWeek && $due<=$endWeek && !$bCheck){
								$this->taskOutPutbyTime['week'].=$taskOutput;
							}
							
							if($due<$today){
								$this->taskOutPutbyTime['missed'].=$taskOutput;
							}
							
							if($due>$endWeek){
								$this->taskOutPutbyTime['commingsoon'].=$taskOutput;
							}
					   }else{
					        //OhneTermin
							$this->taskOutPutbyTime['notermin'].=$taskOutput;
						}					  
			       }
              }
		 //}
		 return $this->taskOutPutbyTime;
		 
	}
	
	
	public function generateTodoOutput(){
			
			 $aCountCalEvents=array();
			 $aReturnArray=array();
			 $aTaskTime=array();
			 
			 $tasksCount = array('today'=>0,'tomorrow'=>0,'actweek'=>0,'withoutdate'=>0,'missedactweek'=>0,'alltasks'=>0,'alltasksdone'=>0,'sharedtasks'=>0,'comingsoon'=>0);
			  		
		     $today=strtotime($this->getToday());
			 $aTaskTime['today']=$this->getToday();
			 $tomorrow = strtotime($this->getTommorow()); 
			 $aTaskTime['tomorrow']=$this->getTommorow();
			 $beginnWeek = strtotime($this->getStartofTheWeek()); 
			 $endWeek = strtotime($this->getEndofTheWeek()); 
			 $aTaskTime['actweek']=$this->getStartofTheWeek().' - '.$this->getEndofTheWeek();
			
				//foreach( $this->aCalendar as $cal ) {
				   $calendar_tasks = App::all($this->aCalendar);
				   //$aCountCalEvents[$cal['id']]=0;
				   
				  foreach( $calendar_tasks as $task ) {
				  	  if($task['objecttype']!='VTODO') {
			                        continue;
			            }
				   
					    $calId=(string)$task['calendarid'];
					    $aCountCalEvents[$calId]+=1;
					   
					   $object = \OC_VObject::parse($task['calendardata']);
					   $vtodo = $object->VTODO;
					   $due = $vtodo->DUE;
					   $completed = $vtodo->COMPLETED;
					   $tasksCount['alltasks']+=1;
					   
					   
						
						if($this->aCalendar[$calId]['userid']!=\OCP\USER::getUser()){
							  $accessclass = $vtodo -> getAsString('CLASS');
							 if($accessclass!='' && $accessclass!='PUBLIC'){
						    	if($aCountCalEvents[$calId]>0) $aCountCalEvents[$calId]-=1;
						     }
						}
						
						
						if($completed){
							$tasksCount['alltasksdone']+=1;
						}
					  
						if ($due) {
							
							$dueTmp = $due->getDateTime()->format('d.m.Y');
							$due = strtotime($dueTmp); 
								
							if($due==$today && !$completed){
								$tasksCount['today']+=1;
								
							}
							
							if($due==$tomorrow && !$completed){
								$tasksCount['tomorrow']+=1;
								
							}
							
							if($due>=$beginnWeek && $due<=$endWeek && !$completed){
								$tasksCount['actweek']+=1;
							}
							if($due>=$endWeek  && !$completed){
								$tasksCount['comingsoon']+=1;
							}

							if($due<$today && !$completed){
									
								$tasksCount['missedactweek']+=1;
								
							}
							
							
						}else{
							//OhneTermin
							if( !$completed) $tasksCount['withoutdate']+=1;
						}
				  }
			//}

	       $singletodos = \OCP\Share::getItemsSharedWith('todo', Share_Backend_Vtodo::FORMAT_TODO);

           $tasksCount['sharedtasks']=count($singletodos);
		   $aReturnArray=array('tasksCount'=>$tasksCount,'aCountCalEvents'=>$aCountCalEvents,'aTaskTime'=>$aTaskTime);
        
		return $aReturnArray;
	}
   
   public function getCalendarPermissions(){
   	
	   $this->iCalPermissions = \OCA\Calendar\Calendar::find($this->iCalId);
	
   }
   
   public function getCalendarAllTasksData(){
   
	   $this->aTasks = App::all($this->aCalendar);
	
   }
   
    public function getCalendarAllInPeriodTasksData(){
   
	   $this->aTasks = App::allInPeriodCalendar($this->aCalendar,$this->sMode);
	
   }
	
	public function getTaskData(){
		if($this->sMode!='' && $this->sMode!='alltasksdone')  $this->getCalendarAllInPeriodTasksData();
		else $this->getCalendarAllTasksData();
	}
   
   public function generateCalendarSingleOutput(){
    	     $this->getCalendarPermissions();
			  $this->aCalendar[$this->iCalId]=$this->iCalPermissions;
	          $this->getTaskData();
			  $this->generateTasksToCalendarOutput();
			  if(is_array($this->aTasksOutput)) return $this->aTasksOutput;
	          else return false;
    }
   
   public function generateTasksAllOutput(){
   	   $aReturnTasks='';
	  // foreach($this->aCalendar as $calendar ) {
	   	//      $this->setCalendarId($calendar['id']);
	   	  //    $this->getCalendarPermissions();
	          $this->getTaskData();
			  $this->generateTasksToCalendarOutput();
	  // }
	   
	    if(is_array($this->aTasksOutput)) return $this->aTasksOutput;
	    else return false;
   }
   
   public function generateTasksToCalendarOutput(){
   	
	
	
	 foreach( $this->aTasks as $task ) {
	                if($task['objecttype']!='VTODO') {
	                        continue;
	                }
	                if(is_null($task['summary'])) {
	                        continue;
	                }
			$object = \OC_VObject::parse($task['calendardata']);
			$vtodo = $object->VTODO;
			$isCompleted=$vtodo->COMPLETED;
			try {
				if($this->sMode!='' && $this->sMode!='alltasksdone'){
						if(!$isCompleted){
							$this->aTasksOutput[] = App::arrayForJSON($task['id'], $vtodo, $this->dUserTimeZone,$this->aCalendar[$task['calendarid']],$task);
						}	
				}elseif($this->sMode!='' && $this->sMode=='alltasksdone'){
						if($isCompleted){
							$this->aTasksOutput[] = App::arrayForJSON($task['id'], $vtodo, $this->dUserTimeZone,$this->aCalendar[$task['calendarid']],$task);
						}	
				}
				else{
				    	$this->aTasksOutput[] = App::arrayForJSON($task['id'], $vtodo, $this->dUserTimeZone,$this->aCalendar[$task['calendarid']],$task);
				}
				
			} catch(Exception $e) {
	                        \OCP\Util::writeLog('aufgaben', $e->getMessage(), \OCP\Util::ERROR);
	                }
	        }
	 
	 
   }
   
   public function generateTasksTimeLineOutput(){
   	
	
   }
   
   
   public function getStartDayDB($iTime){
   	   
	   return date('Y-m-d 00:00:00',$iTime);
   }
   
   public function getEndDayDB($iTime){
   	   
	   return date('Y-m-d 23:59:59',$iTime);
   }
   
   public function getTimeLineDB(){
   	        
			
			 $whereSQL='';
			 $aExec=array();
			 	
   	         switch($this->sMode){
				case 'today':
					  $start=$this->getStartDayDB($this->nowTime);
		              $end=$this->getEndDayDB($this->nowTime);
					  $whereSQL='AND (`startdate` >= ? AND `startdate` <= ? AND `repeating` = 0) ';
					  $aExec=array('VTODO',	$start, $end);
					break;
					
				case 'tomorrow':
					  $timeTomorrow=strtotime($this->getTommorow());
					  $start=$this->getStartDayDB($timeTomorrow);
		              $end=$this->getEndDayDB($timeTomorrow);
					  
					  $whereSQL='AND (`startdate` >= ? AND `startdate` <= ? AND `repeating` = 0) ';
					  $aExec=array('VTODO',	$start, $end);
					break;
					
				case 'actweek':
					  
					  $getStartdate=strtotime($this->getStartofTheWeek());
					  $getEnddate=strtotime($this->getEndofTheWeek());
					  $start=$this->getStartDayDB($getStartdate);
		              $end=$this->getEndDayDB($getEnddate);
					  
					   $whereSQL='AND (`startdate` >= ? AND `startdate` <= ? AND `repeating` = 0) ';
					   $aExec=array('VTODO',$start, $end);
					break;
				 
				 case 'withoutdate':
					  $whereSQL='AND ( `startdate` IS NULL AND `repeating` = 0)';
					  $aExec=array('VTODO');
					break;
					
				case 'missedactweek':
					   
					   $start=$this->getStartDayDB($this->nowTime);
					   $whereSQL='AND ( `startdate` < ? AND `repeating` = 0) ';
					   $aExec=array('VTODO',$start);
					break;
				case 'comingsoon':
					  
					 // $getStartdate=strtotime($this->getStartofTheWeek());
					  $getEnddate=strtotime($this->getEndofTheWeek());
					  //$start=$this->getStartDayDB($getStartdate);
		              $end=$this->getEndDayDB($getEnddate);
					  
					   $whereSQL='AND (`startdate` >= ?  AND `repeating` = 0) ';
					   $aExec=array('VTODO', $end);
					break;			
			}

           $addWhereSql='';
		    foreach($this->aCalendar as $calInfo){
				if($addWhereSql=='') {
					$addWhereSql="`calendarid` = ? ";
					array_push($aExec,$calInfo['id']);
				}else{
					$addWhereSql.="OR `calendarid` = ? ";
					array_push($aExec,$calInfo['id']);
				}
				//\OCP\Util::writeLog('calendar','AlarmDB ID :'.$calInfo['id'] ,\OCP\Util::DEBUG);
			}
            
			$whereSQL.=' AND ( '.$addWhereSql.' ) ';
		   
            $aReturnArray=array('wheresql'=>$whereSQL,'execsql'=>$aExec);
             
			 return $aReturnArray;
   }
	
}
