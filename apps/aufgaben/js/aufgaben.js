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

OC.Aufgaben={
	   firstLoading:true,
	   sendmail: function(eventId, emails){
			//Calendar.UI.loading(true);
			$.post(
			OC.filePath('calendar','ajax/event','sendmail.php'),
			{
				eventId:eventId,
				emails:emails,
				
			},
			function(result){
				if(result.status == 'success'){
					OC.dialogs.alert('E-Mails an: '+emails+' erfolgreich versendet.', 'Email erfolgreich versendet');
					$('#inviteEmails').val('');
				}else{
					OC.dialogs.alert(result.data.message, 'Error sending mail');
				}
				Calendar.UI.loading(false);
			}
		);
		},
	  taskRendering:function(taskSingleArray){
	  	
	  	if(typeof taskSingleArray.id!='undefined'){
	  	 var tmpTask=$('<div class="task" data-id="'+taskSingleArray.id+'">');
	  	   if(taskSingleArray.orgevent){
	  	   	  tmpTask=$('<div class="task" style="border:2px dotted #000;" data-id="'+taskSingleArray.id+'">');
	  	   }
	  	   
	  	    $('#tasks_list').append(tmpTask);
	  	   
	  	    if(taskSingleArray.permissions & OC.PERMISSION_UPDATE){
		  	    var checkbox = $('<input type="checkbox">').click(OC.Aufgaben.completedHandler);
						
					if (taskSingleArray.completed) {
						checkbox.attr('checked', 'checked');
						tmpTask.addClass('done');
					}
				$('<div>').addClass('completed').append(checkbox).prependTo(tmpTask);
			}else{
				if (taskSingleArray.completed) {
					tmpTask.addClass('done');
				}
			}
			
			var priority = taskSingleArray.priority;
				
			$('<div>')
				.addClass('tag')
				.addClass('priority')
				.addClass('priority-'+(priority?priority:'n'))
				.text(priority)
				.prependTo(tmpTask);	
				$('<div>')
				.addClass('calCol').css('background-color',taskSingleArray.bgcolor).html('&nbsp;')
				.prependTo(tmpTask);
				//Div for the add Icons
				var iconDiv=$('<div>').addClass('icons');
				iconDiv.appendTo(tmpTask);
				var title='';
				if(taskSingleArray.description!=''){
					title=taskSingleArray.description;
				}
				if(taskSingleArray.rightsoutput!=''){
					title+=' ('+taskSingleArray.rightsoutput+')';
				}
				var imgReminder='';
			
				if(taskSingleArray.isalarm==1){
					imgReminder=' <img style="margin-top:4px;margin-bottom:-4px;" title="reminder" src="'+ OC.imagePath('core', 'actions/clock.svg') +'" width="14">';
					$('<div style="width:14px;float:left;height:14px;">'+imgReminder+' &nbsp;</div>').appendTo(iconDiv);
			    }
			    var imgShare='';
				if(taskSingleArray.shared){
					imgShare=' <img style="margin-left:2px;margin-top:4px;margin-bottom:-4px;" title="'+ t('core', 'Shared')+'" src="'+ OC.imagePath('core', 'actions/shared.svg') +'" width="14">';
					$('<div style="width:14px;float:left;height:14px;">'+imgShare+' &nbsp;</div>').appendTo(iconDiv);
				}
				
				var imgPrivate='';
			
				if(taskSingleArray.privat=='private'){
					imgPrivate=' <img style="margin-left:2px;margin-top:2px;margin-bottom:-2px;" title="privat" src="'+ OC.imagePath('core', 'actions/lock.svg') +'" width="12">';
					$('<div style="width:14px;float:left;height:14px;">'+imgPrivate+' &nbsp;</div>').appendTo(iconDiv);
				}
				
				if(taskSingleArray.privat=='confidential'){
					imgPrivate=' <img title="confidential" src="'+ OC.imagePath('core', 'actions/toggle.svg') +'" width="12">';
					$('<div style="width:14px;float:left;height:14px;">'+imgPrivate+' &nbsp;</div>').appendTo(iconDiv);
				}
				
	  	    //summary
	  	    $('<div>').addClass('summary').attr('title',title).text(taskSingleArray.summary).appendTo(tmpTask);
	  	    
	  	        
	  	    
	  	    //Date
	  	      if(taskSingleArray.due!=''){
					 $('<div>').addClass('due').text(taskSingleArray.due).appendTo(tmpTask);
				}else{
					$('<div>').addClass('due').html('&nbsp;').appendTo(tmpTask);
				} 
	  	    
	  	    //Categories
	  	    var $categories = $('<div>').addClass('categories').appendTo(tmpTask);
				
	  	    $(taskSingleArray.categories).each(function(i, category){
				$categories.append($('<a>').addClass('tag').text(category));
			});
			//Location
			 var location = $('<div>').addClass('location').appendTo(tmpTask);
			if (taskSingleArray.location) {
			  $('<a>').addClass('tag').attr('data-geo','data-geo').attr('target','_blank').attr('href','http://open.mapquest.com/?q='+taskSingleArray.location+'&zoom=12').text(taskSingleArray.location).appendTo(location);
		   }
		  
		  if(taskSingleArray.permissions & OC.PERMISSION_UPDATE){
		   var editLink=$('<a>').addClass('button').html('<img class="svg" src="'+OC.imagePath('core', 'actions/rename.svg')+'" />').click(OC.Aufgaben.editHandler);
		  }
		  if(taskSingleArray.permissions & OC.PERMISSION_DELETE){
		   var delLink=$('<a>').addClass('button').html('<img class="svg" src="'+OC.imagePath('core', 'actions/delete.svg')+'" />').click(OC.Aufgaben.deleteHandler);
		  }
		 
		  if(taskSingleArray.isOnlySharedTodo){
		  	  var addLink=$('<a>').addClass('button').html('<img class="svg" width="16" src="'+OC.imagePath('core', 'actions/add.svg')+'" />').click(OC.Aufgaben.addSharedHandler);
		  }
		  
		   $('<span>').addClass('taskActions').append(addLink).append(editLink).append(delLink).prependTo(tmpTask);
            $('<span>').addClass('description').text(title).appendTo(tmpTask);
		   }
	  },
	  showEditTask:function(TaskId){
	  	   
	  	   $.ajax({
					type : 'POST',
					url : OC.filePath('aufgaben', 'ajax', 'edittask.php'),
					data:{tid:TaskId},
					success : function(data) {
						
						$("#dialog").html(data);
						$( "#edit-event" ).tabs({ selected: 0});
						$("#dialog").dialog({
							width : 500,
							height : 530,
							modal : true,
							title : t('aufgaben','Edit Task'),
							buttons: [
						 { text:t('calendar', 'Cancel'), click: function() { $( this ).dialog( "close" ); } },
						  { text:t('calendar', 'Save'), click: function() { 
						  	   if($('#tasksummary').val()!=''){
								     OC.Aufgaben.SubmitForm('edititTask', '#taskForm', '#dialog');
								    $(this).dialog("close");
								}else{
									OC.Aufgaben.showMeldung(t('aufgaben','Title is missing'));
								}
						  	 } 
						  },
						 ],
						});
						 
				  $('#reminderdate').datepicker({dateFormat: "dd.mm.yy"});
				  $('#remindertime').timepicker({showPeriodLabels:false});	 
				  
		           OC.Aufgaben.reminder('init');
					$('#reminder').change(function(){
						OC.Aufgaben.reminder('reminder');
					});
					$('#remindertimeselect').change(function(){
						OC.Aufgaben.reminder('remindertime');
					});
						  $('#sWV').datepicker({dateFormat: "dd.mm.yy"});
						  $('#sWV_time').timepicker({showPeriodLabels:false});
						  $('#taskcategories').multiple_autocomplete({source: categories});
						  $('#sendemailbutton').click(function() {
								if($('#inviteEmails').val()!==''){
									  OC.Aufgaben.sendmail($(this).attr('data-eventid'),$('#inviteEmails').val());
								}
						 });
			             OC.Aufgaben.Share.init();
			             
					}
				});
				 return false; 
	  },
	  editHandler:function(event){
	  	  $Task=$(this).closest('.task');
	  	  TaskId=$Task.attr('data-id');
	  	
	  	  OC.Aufgaben.showEditTask(TaskId);
				
	  },
	  addSharedHandler:function(event){
	  	  $Task=$(this).closest('.task');
	  	  TaskId=$Task.attr('data-id');
	  	 
	  	  OC.Aufgaben.openShareDialog(TaskId);
	  },
	  completedHandler:function(event){
	  	  $Task=$(this).closest('.task');
	  	  TaskId=$Task.attr('data-id');
	  	  checked = $(this).is(':checked');
	  	 
	  	  $.post(OC.filePath('aufgaben', 'ajax', 'completed.php'),{id:TaskId,checked:checked?1:0},function(jsondata){
			if(jsondata.status == 'success'){
				task = jsondata.data;
				OC.Aufgaben.rebuildLeftTaskView();
				//$Task.data('task', task)
				if (task.completed) {
					$Task.addClass('done');
				}
				else {
					$Task.removeClass('done');
				}
			}
			else{
				alert(jsondata.data.message);
			}
		});
		
	  },
	   deleteHandler:function(event){
	  	   $Task=$(this).closest('.task');
	  	  TaskId=$Task.attr('data-id');
	  	  $( "#dialog" ).html(t('aufgaben','Are you sure')+'?');
	  	 
	  	  $( "#dialog" ).dialog({
			resizable: false,
			title : t('aufgaben', 'Delete Task'),
			width:200,
			height:140,
			modal: true,
			buttons: [
						 { text:t('aufgaben', 'No'), click: function() { $( this ).dialog( "close" ); } },
						  { text:t('aufgaben', 'Yes'), click: function() { 
						  	  var oDialog=$(this);
								 $.post(OC.filePath('aufgaben', 'ajax', 'delete.php'),{'id':TaskId},function(jsondata){
										if(jsondata.status == 'success'){
											oDialog.dialog( "close" );
											$Task.remove();
											OC.Aufgaben.rebuildLeftTaskView();
											
										}
										else{
											alert(jsondata.data.message);
										}
							        });
						  	 } }  
						 ],
		});
  	 
		return false;
	  },
	  openShareDialog:function(TaskId){
	  	  
	  	 var selCal=$('<select name="calendar" id="calendarAdd"></select>');
	  	   $.each(mycalendars, function(i, elem) {
				var option = $('<option value="' + elem['id'] + '">' +elem['name'] + '</option>');
				selCal.append(option);
				});
	  
	  	 $('<p>'+t('calendar','Please choose a calendar')+'</p>').appendTo("#dialogmore");
	  	 selCal.appendTo("#dialogmore");
	  
	  	
	  	  $( "#dialogmore" ).dialog({
			resizable: false,
			title : t('aufgaben', 'Add Task'),
			width:350,
			height:200,
			modal: true,
			buttons: 
			[ 
				{ text:t('core', 'Add'), click: function() {
					 var oDialog=$(this);
					 var CalId=$('#calendarAdd option:selected').val();
					
					 $.post(OC.filePath('aufgaben', 'ajax', 'addsharedevent.php'),{'taskid':TaskId,'calid':CalId},function(jsondata){
							if(jsondata.status == 'success'){
								 OC.Aufgaben.updateList(0);
				                 OC.Aufgaben.rebuildLeftTaskView();
				                  $( "#dialogmore" ).html('');
								oDialog.dialog( "close" );
								
							}
							else{
								alert(jsondata.data.message);
							}
				        });
					 } 
			    },
			    { text:t('calendar', 'Cancel'), click: function() {
			    	$( this ).dialog( "close" );
			    	  $( "#dialogmore" ).html('');
			    }
			    } 
			],
	
		});
  	 
		return false;
	  },
	  newTask:function(){
	  	
	  	$.ajax({
				type : 'POST',
				url : OC.filePath('aufgaben', 'ajax', 'newtask.php'),
				
				success : function(data) {
					$("#dialog").html(data);
					$( "#new-event" ).tabs({ selected: 0});
					$("#dialog").dialog({
						width : 500,
						height : 530,
						modal : true,
						title : t('aufgaben','Add Task'),
						buttons: [
						 { text:t('calendar', 'Cancel'), click: function() { $( this ).dialog( "close" ); } },
						  { text:t('core', 'Add'), click: function() { 
						  	   if($('#tasksummary').val()!=''){
								    OC.Aufgaben.SubmitForm('newitTask', '#taskForm', '#dialog');
								    $(this).dialog("close");
								}else{
									OC.Aufgaben.showMeldung(t('aufgaben','Title is missing'));
								}
						  	 } }  
						 ],
				   });
				  
		           
		           $('#reminderdate').datepicker({dateFormat: "dd.mm.yy"});
				  $('#remindertime').timepicker({showPeriodLabels:false});	 
				  
		           OC.Aufgaben.reminder('init');
					$('#reminder').change(function(){
						OC.Aufgaben.reminder('reminder');
					});
					$('#remindertimeselect').change(function(){
						OC.Aufgaben.reminder('remindertime');
					});
				  $('#sWV').datepicker({dateFormat: "dd.mm.yy"});
				  $('#sWV_time').timepicker({showPeriodLabels:false});	  
		          $('#taskcategories').multiple_autocomplete({source: categories});
				}
			});
			 return false; 
	  },
	  SubmitForm: function(VALUE, FormId, UPDATEAREA) {
		
		         actionFile='newtask';
		         if (VALUE == 'newitTask') {
		         	 actionFile='newtask';
		         }
		          if (VALUE == 'edititTask') {
		         	 actionFile='edittask';
		         }
				$(FormId + ' input[name=hiddenfield]').attr('value', VALUE);
				$.ajax({
					type : 'POST',
					url : OC.filePath('aufgaben', 'ajax', actionFile+'.php'),
					data : $(FormId).serialize(),
					success : function(data) {
						$(UPDATEAREA).html(data);
		               
						if (VALUE == 'newitTask') {
								  OC.Aufgaben.showMeldung(t('aufgaben','Task creating success!'));
						}
						if (VALUE == 'edititTask') {
					         	  OC.Aufgaben.showMeldung(t('aufgaben','Update success!'));
				         }
				         
				         OC.Aufgaben.updateList(0);
				         OC.Aufgaben.rebuildLeftTaskView();
						
					}
				});
		
			
		
		},
		showMeldung: function(TXT) {

			var leftMove = ($(window).width() / 2) - 150;
			var myMeldungDiv = $('<div id="iMeldung" style="left:' + leftMove + 'px"></div>');
			$('#content').append(myMeldungDiv);
			$('#iMeldung').html(TXT);
		
			$('#iMeldung').animate({
				top : 200
			}).delay(3000).animate({
				top : '-300'
			}, function() {
				$('#iMeldung').remove();
			});
		
		},
		filter:function(tagText){
			//$Task=$(this).closest('.task');
	  	    //TaskId=$Task.attr('data-id');
	  	    var saveArray=[];
			$('#tasks_list .task .categories').find('a.tag').each(function(i,el){
				 if($(el).text()==tagText){
				 	$Task=$(this).closest('.task');
	  	             TaskId=$Task.attr('data-id');
	  	             saveArray[TaskId]=1;
				 }
			});
			$('#tasks_list .task').each(function(i,el){
				 if(saveArray[$(el).attr('data-id')]){
				 	$(el).addClass('active');
				 }else{
				 	$(el).addClass('hidden');
				 }
			});
			
		},
		updateList:function(CID){
			 $.post(OC.filePath('aufgaben', 'ajax', 'gettasks.php'),{calid:CID},function(jsondata){
				       $('#tasks_list').empty();
					  $(jsondata).each(function(i, task) {
					  	OC.Aufgaben.taskRendering(task);
					  });
					  $('.showToolTip').tipsy({gravity: $.fn.tipsy.autoNS});
					  
					  $('.task .categories a').each(function(i,el){
					  	   $(el).on('click',function(){
					  	   	  	$Task=$(this).closest('.task');
					  	   	  	if($Task.hasClass('active')){
					  	   	  		$Task.removeClass('active');
					  	   	  		$('.task').removeClass('hidden');
					  	   	  	}else{
					  	   	      OC.Aufgaben.filter($(this).text());
					  	   	  }
					  	   });
					  });
					  
					   $('.task .location').each(function(i,el){
						  $(el).tooltip({
								items: "[data-geo], [title]",
								
								content: function() {
								var element = $( this );
								if ( element.is( "[data-geo]" ) ) {
								var text = element.text();
								return "<img class='map' alt='" + text +
								"' src='http://maps.google.com/maps/api/staticmap?" +
								"zoom=14&size=350x350&maptype=terrain&sensor=false&center=" +
								text + "'>";
								}
								if ( element.is( "[title]" ) ) {
								return element.attr( "title" );
								}
								if ( element.is( "img" ) ) {
								return element.attr( "alt" );
								}
								}
							});
						});
					 
					 if($(window).width()<1250){
					    	$('.task').css({'width':'85%','height':'60px'});
					    }
			    if(OC.Aufgaben.firstLoading==true){
				    OC.Aufgaben.checkShowEventHash();
				    OC.Aufgaben.firstLoading=false;
				}		    
				
		});
			
		},
		updateListByPeriod:function(MODE){
			 $.post(OC.filePath('aufgaben', 'ajax', 'gettasks.php'),{mode:MODE},function(jsondata){
				       $('#tasks_list').empty();
					  $(jsondata).each(function(i, task) {
					  	OC.Aufgaben.taskRendering(task);
					  });
					  $('.showToolTip').tipsy({gravity: $.fn.tipsy.autoNS});
					  
					  $('.task .categories a').each(function(i,el){
					  	   $(el).on('click',function(){
					  	   	  	$Task=$(this).closest('.task');
					  	   	  	if($Task.hasClass('active')){
					  	   	  		$Task.removeClass('active');
					  	   	  		$('.task').removeClass('hidden');
					  	   	  	}else{
					  	   	      OC.Aufgaben.filter($(this).text());
					  	   	  }
					  	   });
					  });
					 
					  $('.task .location').each(function(i,el){
						  $(el).tooltip({
								items: "[data-geo], [title]",
								
								content: function() {
								var element = $( this );
								if ( element.is( "[data-geo]" ) ) {
								var text = element.text();
								return "<img class='map' alt='" + text +
								"' src='http://maps.google.com/maps/api/staticmap?" +
								"zoom=14&size=350x350&maptype=terrain&sensor=false&center=" +
								text + "'>";
								}
								if ( element.is( "[title]" ) ) {
								return element.attr( "title" );
								}
								if ( element.is( "img" ) ) {
								return element.attr( "alt" );
								}
								}
							});
						});
					 
					 //  if($(window).width()<1250){
					   // 	$('.task').css({'width':'85%','height':'60px'});
					    //}
				
		});
			
		},
		reminder:function(task){
			if(task=='init'){
				$('#reminderemailinputTable').css('display', 'none');
				 $('#reminderdateTable').css('display', 'none');
				  $('#reminderTable').css('display', 'none');
				  
				OC.Aufgaben.reminder('reminder');
				OC.Aufgaben.reminder('remindertime');
			}
			if(task == 'reminder'){
				$('#reminderemailinputTable').css('display', 'none');
				 $('#reminderdateTable').css('display', 'none');
				 
				if($('#reminder option:selected').val() == 'none'){
					$('#reminderemailinputTable').css('display', 'none');
					$('#reminderdateTable').css('display', 'none');
					 $('#reminderTable').css('display', 'none');
				}
				if($('#reminder option:selected').val() == 'DISPLAY'){
					$('#reminderemailinputTable').css('display', 'none');
					 $('#reminderTable').css('display', 'block');
					 $('#remindertimeinput').css('display', 'block');
				}
				if($('#reminder option:selected').val() == 'EMAIL'){
					$('#reminderemailinputTable').css('display', 'block');
					 $('#reminderTable').css('display', 'block');
					 $('#remindertimeinput').css('display', 'block');
				}
			}
			if(task == 'remindertime'){
				 $('#reminderdateTable').css('display', 'none');
				 $('#remindertimeinput').css('display', 'block');
				// $('#reminderemailinputTable').css('display', 'none');
				if($('#remindertimeselect option:selected').val() == 'ondate'){
					$('#reminderdateTable').css('display', 'block');
					$('#remindertimeinput').css('display', 'none');
				}
			}
		},
		rebuildLeftTaskView:function(){
			
	  	 $.post(OC.filePath('aufgaben', 'ajax', 'rebuild.php'),function(data){
			   $('#tasks_lists').html(data);
			  
			   $('.calListen').each(function(i,el){
		
					$(el).on('click',function(){
						 $('.taskstimerow').removeClass('active');
						 $('.calListen').removeClass('active');
						 $(el).addClass('active');
						 $('#taskmanagertitle').text($(el).attr('title'));
						 OC.Aufgaben.updateList($(el).attr('data-id'));
						  
					});
				});
				
				$('.taskstimerow').each(function(i,el){
					
					$(el).on('click',function(){
						 $('.taskstimerow').removeClass('active');
						  $('.calListen').removeClass('active');
						 $(el).addClass('active');
						 $('#taskmanagertitle').text($(el).attr('title'));
						 OC.Aufgaben.updateListByPeriod($(el).attr('data-id'));
						  
					});
				});
			  
		});
	  },
	  checkShowEventHash:function(){
			 var id = parseInt(window.location.hash.substr(1));
			 if(id) {
				/*var calEvent={};
					  calEvent['id']=id;
				Calendar.UI.showEvent(calEvent,'','');*/
				OC.Aufgaben.showEditTask(id);
				
			 }
		},
	  Share:{
			init:function(){
				if(typeof OC.Share !== typeof undefined){
					var itemShares = [OC.Share.SHARE_TYPE_USER, OC.Share.SHARE_TYPE_GROUP];
					
					$('#sharewith').autocomplete({minLength: 2, source: function(search, response) {
						$.get(OC.filePath('core', 'ajax', 'share.php'), { fetch: 'getShareWith', search: search.term, itemShares: itemShares }, function(result) {
							if (result.status == 'success' && result.data.length > 0) {
								response(result.data);
							}
						});
					},
					focus: function(event, focused) {
						event.preventDefault();
					},
					select: function(event, selected) {
						var itemType = 'todo';
						var itemSource = $('#sharewith').data('item-source');
						var shareType = selected.item.value.shareType;
						var shareWith = selected.item.value.shareWith;
						$(this).val(shareWith);
						// Default permissions are Read and Share
						var permissions = OC.PERMISSION_READ | OC.PERMISSION_SHARE;
						
						OC.Share.share(itemType, itemSource, shareType, shareWith, permissions, function(data) {
							var newitem = '<li data-item-type="todo"'
								+ 'data-share-with="'+shareWith+'" '
								+ 'data-item="'+itemSource+'" '
								+ 'data-permissions="'+permissions+'" '
								+ 'data-share-type="'+shareType+'">'+shareWith+' ('+(shareType == OC.Share.SHARE_TYPE_USER ? t('core', 'user') : t('core', 'group'))+') '
								+ '<span class="shareactions">'
								+ t('core', 'can edit')+' <input class="update" type="checkbox" > '
								+ t('core', 'share')+' <input class="share" type="checkbox"  checked="checked"> '
								+ t('core', 'delete')+' <input class="delete" type="checkbox" > '
								+ '<img style="cursor: pointer;" class="svg action unshare" title="'+ t('core', 'Unshare')+'" src="'+ OC.imagePath('core', 'actions/delete.svg') +'"></span></li>';
							$('.sharedby.todolist').append(newitem);
							$('#sharedWithNobody').remove();
							$('#sharewith').val('');
							OC.Aufgaben.Share.buildShareEvents(shareWith);
							
						});
						
						return false;
					}
				});
	              OC.Aufgaben.Share.buildShareEvents('');
	              
				}	
			},
			
			buildShareEvents:function(shareWithSingle){
				
				
				var selector='.shareactions > ';
				if(shareWithSingle!=''){
					var selector='li[data-share-with='+shareWithSingle+'] .shareactions > ';
				}
				$(selector+'input:checkbox').on('change',function() {
						var container = $(this).parents('li').first();
						if(shareWithSingle!=''){
							container = $('li[data-share-with='+shareWithSingle+']');
						}
						var permissions = parseInt(container.data('permissions'));
						
						var oldpermissions=permissions;
						var itemType = container.data('item-type');
						var shareType = container.data('share-type');
						var itemSource = container.data('item');
						var shareWith = container.data('share-with');
						var permission = null;
						
						if($(this).hasClass('update')) {
							permission = OC.PERMISSION_UPDATE;
						} else if($(this).hasClass('share')) {
							permission = OC.PERMISSION_SHARE;
						} else if($(this).hasClass('delete')) {
							permission = OC.PERMISSION_DELETE;
						}
						// This is probably not the right way, but it works :-P
						if($(this).is(':checked')) {
							permissions += permission;
						} else {
							permissions -= permission;
						}
						
						container.data('permissions',permissions);
						OC.Share.setPermissions(itemType, itemSource, shareType, shareWith, permissions);
					});
	
					$(selector+' .unshare').click(function() {
						var container = $(this).parents('li').first();
						var itemType = container.data('item-type');
						var shareType = container.data('share-type');
						var itemSource = container.data('item');
						var shareWith = container.data('share-with');
						OC.Share.unshare(itemType, itemSource, shareType, shareWith, function() {
							container.remove();
						});
					});
				
			},
		},
	 
}

$(window).bind('hashchange', function() {
		    OC.Aufgaben.checkShowEventHash();
	});

$(document).ready(function(){
	
	$('#addnewtask').on('click',function(){
		OC.Aufgaben.newTask();
	});
	
   
	
	$('.calListen').each(function(i,el){
		
		$(el).on('click',function(){
			 $('.taskstimerow').removeClass('active');
			 $('.calListen').removeClass('active');
			 $(el).addClass('active');
			 $('#taskmanagertitle').text($(el).attr('title'));
			 OC.Aufgaben.updateList($(el).attr('data-id'));
			  
		});
	});
	
	$('.taskstimerow').each(function(i,el){
		
		$(el).on('click',function(){
			 $('.taskstimerow').removeClass('active');
			  $('.calListen').removeClass('active');
			 $(el).addClass('active');
			
			 $('#taskmanagertitle').text($(el).attr('title'));
			 OC.Aufgaben.updateListByPeriod($(el).attr('data-id'));
			  
		});
	});
	
	 OC.Aufgaben.updateList(0);
	 $('.calListen[data-id=0]').addClass('active');
	 
	 
	
});