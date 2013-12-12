/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */



Calendar={
	firstLoading:true,
	Util:{
		sendmail: function(eventId, emails){
			Calendar.UI.loading(true);
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
		dateTimeToTimestamp:function(dateString, timeString){
			dateTuple = dateString.split('-');
			timeTuple = timeString.split(':');
			
			var day, month, year, minute, hour;
			day = parseInt(dateTuple[0], 10);
			month = parseInt(dateTuple[1], 10);
			year = parseInt(dateTuple[2], 10);
			hour = parseInt(timeTuple[0], 10);
			minute = parseInt(timeTuple[1], 10);
			
			var date = new Date(year, month-1, day, hour, minute);
			
			return parseInt(date.getTime(), 10);
		},
		touchCal:function(EVENTID){
			$.post(OC.filePath('calendar', 'ajax/calendar', 'touch.php'),{eventid:EVENTID},function(jsondata){
				$('#fullcalendar').fullCalendar('refetchEvents');
			});
		},
		formatDate:function(year, month, day){
			if(day < 10){
				day = '0' + day;
			}
			if(month < 10){
				month = '0' + month;
			}
			return day + '-' + month + '-' + year;
		},
		formatTime:function(hour, minute){
			if(hour < 10){
				hour = '0' + hour;
			}
			if(minute < 10){
				minute = '0' + minute;
			}
			return hour + ':' + minute;
		}, 
		adjustDate:function(){
			var fromTime = $('#fromtime').val();
			var fromDate = $('#from').val();
			var fromTimestamp = Calendar.Util.dateTimeToTimestamp(fromDate, fromTime);

			var toTime = $('#totime').val();
			var toDate = $('#to').val();
			var toTimestamp = Calendar.Util.dateTimeToTimestamp(toDate, toTime);

			if(fromTimestamp >= toTimestamp){
				fromTimestamp += 30*60*1000;
				
				var date = new Date(fromTimestamp);
				movedTime = Calendar.Util.formatTime(date.getHours(), date.getMinutes());
				movedDate = Calendar.Util.formatDate(date.getFullYear(),
						date.getMonth()+1, date.getDate());

				$('#to').val(movedDate);
				$('#totime').val(movedTime);
			}
		},
		adjustTime:function(){
			var fromTime = $('#fromtime').val();
			var fromDate = $('#from').val();
			var fromTimestamp = Calendar.Util.dateTimeToTimestamp(fromDate, fromTime);

			var toTime = $('#totime').val();
			var toDate = $('#to').val();
			var toTimestamp = Calendar.Util.dateTimeToTimestamp(toDate, toTime);

			if(fromTimestamp >= toTimestamp){
				fromTimestamp += 30*60*1000;
				
				var date = new Date(fromTimestamp);
				movedTime = Calendar.Util.formatTime(date.getHours(), date.getMinutes());
				
				$('#totime').val(movedTime);
			}
		},
		completedTaskHandler:function(event){
	  	  $Task=$(this).closest('.taskListRow');
	  	  TaskId=$Task.attr('data-taskid');
	  	  checked = $(this).is(':checked');
	  	 
	  	  $.post(OC.filePath('calendar', 'ajax/tasks', 'completed.php'),{id:TaskId,checked:checked?1:0},function(jsondata){
			if(jsondata.status == 'success'){
				task = jsondata.data;
				//$Task.data('task', task)
				if (task.completed) {
					$Task.addClass('done');
					$Task.remove();
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
	  rebuildTaskView:function(){
	  	 $.post(OC.filePath('calendar', 'ajax/tasks', 'rebuild.php'),function(data){
			   $('#rightCalendarNav').html(data);
			   $('.inputTasksRow').each(function(i,el){
					  $(el).click(Calendar.Util.completedTaskHandler);
				});
			   Calendar.Util.rebuildWidthCalendar();
		});
	  },
	  rebuildCalView:function(){
	  	  $.post(OC.filePath('calendar', 'ajax/calendar', 'rebuild.php'),function(data){
			   $('#leftcontent').html(data);
			   
			   Calendar.Util.rebuildWidthCalendar();
			   Calendar.Util.calViewEventHandler();
		});
	  },
	  calViewEventHandler:function(){
	  	   $('.activeCalendarNav').on('change', function (event) {
				     event.stopPropagation();
				    
				     Calendar.UI.Calendar.activation(this,$(this).data('id'));
			     });
				$('.toolTip').tipsy({gravity: $.fn.tipsy.autoNS});
				
				$( "#datepickerNav" ).datepicker({
					
					onSelect: function(value, inst) {
						var date = inst.input.datepicker('getDate');
						
						$('#fullcalendar').fullCalendar('gotoDate', date);
						
						var view =$('#fullcalendar').fullCalendar('getView');
						
						if(view.name=='month'){
							$('td.fc-day').removeClass('activeDay');
							prettyDate=formatDatePretty(date,'yy-mm-dd');
							$('td[data-date='+prettyDate+']').addClass('activeDay');
							
							 
							
							/*
							prettyDateDay=formatDatePretty(date,'dd');
							prettyDateMonth=formatDatePretty(date,'mm');
							prettyDateYear=formatDatePretty(date,'yy');*/
							
							//alert($('td[data-handler="selectDay"]').find('a:contains("'+prettyDateDay+'")').getParent().addClass('active'));
							//$('td[data-handler="selectDay"]').find('a:contains("'+prettyDateDay+'")').parent('td').addClass('activeDay');
						}
					
					}
				});
	  },
	  rebuildWidthCalendar:function(){
	  	 
	  	  var calWidth=($(window).width()) - ($('#navigation').width() + $('#leftcontent').width() + $('#rightCalendarNav').width());
	  	 
	  	  $('#fullcalendar').width(calWidth);
	  	  $(window).trigger("resize");
	  },
	 setTimeline:function() {
			var curTime = new Date();
			if(curTime.getHours() == 0 && curTime.getMinutes() <= 5) // Because I am calling this function every 5 minutes
			{// the day has changed
				var todayElem = $(".fc-today");
				todayElem.removeClass("fc-today");
				todayElem.removeClass("fc-state-highlight");
				
				todayElem.next().addClass("fc-today");
				todayElem.next().addClass("fc-state-highlight");
			}
			
			var parentDiv = $(".fc-agenda-slots:visible").parent();
			var timeline = parentDiv.children(".timeline");
			if (timeline.length == 0) { //if timeline isn't there, add it
				timeline = $("<hr>").addClass("timeline");
				parentDiv.prepend(timeline);
			}
		
			var curCalView = $('#fullcalendar').fullCalendar("getView");
			if (curCalView.visStart < curTime && curCalView.visEnd > curTime) {
				timeline.show();
			} else {
				timeline.hide();
			}
		
			var curSeconds = (curTime.getHours() * 60 * 60) + (curTime.getMinutes() * 60) + curTime.getSeconds();
			var percentOfDay = curSeconds / 86400; //24 * 60 * 60 = 86400, # of seconds in a day
			var topLoc = Math.floor(parentDiv.height() * percentOfDay);
		
			timeline.css("top", topLoc + "px");
		
		},
		checkShowEventHash:function(){
			 var id = parseInt(window.location.hash.substr(1));
			 if(id) {
				var calEvent={};
					  calEvent['id']=id;
				Calendar.UI.showEvent(calEvent,'','');
			 }
		},
	},
	UI:{
	
		loading: function(isLoading){
			if (isLoading){
				$('#loading').show();
			}else{
				if(Calendar.firstLoading==true){
				    Calendar.Util.checkShowEventHash();
				}
				$('#loading').hide();
				Calendar.firstLoading=false;
			}
			
		},
		openShareDialog:function(url,EventId){
	  	  
	  	 var selCal=$('<select name="calendar" id="calendarAdd"></select>');
	  	   $.each(mycalendars, function(i, elem) {
				var option = $('<option value="' + elem['id'] + '">' +elem['name'] + '</option>');
				selCal.append(option);
				});
	  
	  	 $('<p>'+t('calendar','Please choose a calendar')+'</p>').appendTo("#dialog");
	  	 selCal.appendTo("#dialog");
	  
	  	
	  	  $( "#dialog" ).dialog({
			resizable: false,
			title : t('calendar', 'Add Event'),
			width:350,
			height:200,
			modal: true,
			buttons: 
			[ 
				{ text:t('core', 'Add'), click: function() {
					 var oDialog=$(this);
					 var CalId=$('#calendarAdd option:selected').val();
					
					 $.post(url,{'eventid':EventId,'calid':CalId},function(jsondata){
							if(jsondata.status == 'success'){
								oDialog.dialog( "close" );
								$('#fullcalendar').fullCalendar('refetchEvents');
							    $('#event').dialog('destroy').remove();
							}
							else{
								alert(jsondata.data.message);
							}
				        });
					 } 
			    },
			    { text:t('calendar', 'Cancel'), click: function() {
			    	$( this ).dialog( "close" );
			    }
			    } 
			],
	
		});
  	 
		return false;
	  },
		
		startShowEventDialog:function(){
			Calendar.UI.loading(false);
			
			$('#fullcalendar').fullCalendar('unselect');
			
		
		     Calendar.UI.lockTime();

			$('#closeDialog').on('click',function(){
					if($('#haveshareaction').val()=='1'){
						
							Calendar.Util.touchCal($('#eventid').val());
					}
					$('#event').dialog('destroy').remove();
			});
			
			
			$( "#event" ).tabs({ selected: 0});
			$('.tipsy').remove();
			$('#event').dialog({
				width : 450,
				height: 'auto',
				
				close : function(event, ui) {
					$(this).dialog('destroy').remove();
				}
			});
			
			$('.exdatelistrow').each(function(i,el){
					 
					 $(el).on('click',function(){
					 	   Calendar.UI.removeExdate($(el).data('exdate'));
					 });
				});
			
			Calendar.UI.Share.init();
			$('#sendemailbutton').click(function() {
				if($('#inviteEmails').val()!==''){
					
				  Calendar.Util.sendmail($(this).attr('data-eventid'),$('#inviteEmails').val());
				}
			});
			
			$('#editEvent-delete').on('click', function () {
				Calendar.UI.submitShowDeleteEventForm($(this).data('link'));
			});
			$('#editEvent-add').on('click', function () {
				Calendar.UI.openShareDialog($(this).data('link'),$('#eventid').val());
			});
			
			$('#editEventButton').on('click',function(){
				var calEvent={};
				calEvent['id']=$('#eventid').val();
				calEvent['start']=$('#choosendate').val();
				//alert($('#eventid').val());
				Calendar.UI.editEvent(calEvent,'','');
				
			});
			
			$( "#showLocation" ).tooltip({
					items: "img, [data-geo], [title]",
					position: { my: "left+15 center", at: "right center" },
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
			
		},
		startEventDialog:function(){
			Calendar.UI.loading(false);
			
			$('#fullcalendar').fullCalendar('unselect');
			
		
		 Calendar.UI.lockTime();

			$('#from').datetimepicker({ 
				    altField:'#fromtime',
					dateFormat : 'dd-mm-yy',
					stepMinute: 5,
					numberOfMonths: 2,
					addSliderAccess: true,
					sliderAccessArgs: { touchonly: false },
					showButtonPanel:false,
				   onClose: function(dateText, inst) {
						if ($('#to').val() != '') {
							var testStartDate = $('#from').datetimepicker('getDate');
			   		         var testEndDate =  $('#to').datetimepicker('getDate');
							
							if (testStartDate > testEndDate){
								$('#to').datetimepicker('setDate', $('#from').datetimepicker('getDate'));
							}
						}
						else {
							$('#to').val(dateText);
						}
						Calendar.Util.adjustTime();
					},
					onSelect: function (selectedDateTime){
						$('#to').datetimepicker('option', 'minDate', $('#from').datetimepicker('getDate') );
						
					}
			});
			
			 $('#to').datetimepicker({ 
					 altField:'#totime',
					dateFormat : 'dd-mm-yy',
					stepMinute: 5,
					numberOfMonths: 2,
					addSliderAccess: true,
					sliderAccessArgs: { touchonly: false },
					showButtonPanel:false,
				onClose: function(dateText, inst) {
					if ($('#from').val() != '') {
						var testStartDate = $('#from').datetimepicker('getDate');
			   		    var testEndDate =  $('#to').datetimepicker('getDate');
						if (testStartDate > testEndDate)
							$('#from').datetimepicker('setDate', testEndDate);
					}
					else {
						$('#from').val(dateText);
					}
					Calendar.Util.adjustTime();
				},
				onSelect: function (selectedDateTime){
					$('#from').datetimepicker('option', 'maxDate',  $('#to').datetimepicker('getDate') );
					
				}
			});
			
           //Reminder
           $('#reminderdate').datetimepicker({ 
				    altField:'#remindertime',
					dateFormat : 'dd-mm-yy',
					stepMinute: 5,
					numberOfMonths: 1,
					addSliderAccess: true,
					sliderAccessArgs: { touchonly: false },
					showButtonPanel:false,
				  
			});
           
           
           Calendar.UI.reminder('init');
			$('#reminder').change(function(){
				Calendar.UI.reminder('reminder');
			});
			$('#remindertimeselect').change(function(){
				Calendar.UI.reminder('remindertime');
			});
			
			$('#category').multiple_autocomplete({source: categories});
			Calendar.UI.repeat('init');
			$('#end').change(function(){
				Calendar.UI.repeat('end');
			});
			$('#repeat').change(function(){
				Calendar.UI.repeat('repeat');
			});
			$('#advanced_year').change(function(){
				Calendar.UI.repeat('year');
			});
			$('#advanced_month').change(function(){
				Calendar.UI.repeat('month');
			});
			$('#closeDialog').on('click',function(){
					$('#event').dialog('destroy').remove();
			});
			
			
			
			$( "#event" ).tabs({ selected: 0});
			$('.tipsy').remove();
			$('#event').dialog({
				width : 570,
				height: 'auto',
				
				close : function(event, ui) {
					$(this).dialog('destroy').remove();
				}
			});
			Calendar.UI.Share.init();
			$('#sendemailbutton').click(function() {
				Calendar.Util.sendmail($(this).attr('data-eventid'));
			});
			
			$('#editEventButton').on('click',function(){
				var calEvent={};
				calEvent['id']=$('#eventid').val();
				//alert($('#eventid').val());
				Calendar.UI.editEvent(calEvent,'','');
				
			});
		},
		newEvent:function(start, end, allday){
			
			start = Math.round(start.getTime()/1000);
			if (end){
				end = Math.round(end.getTime()/1000);
			}
			if($('#event').dialog('isOpen') == true){
				// TODO: save event
				$('#event').dialog('destroy').remove();
			}else{
				Calendar.UI.loading(true);
			
				$('#dialog_holder').load(OC.filePath('calendar', 'ajax/event', 'new.form.php'), {start:start, end:end, allday:allday?1:0}, Calendar.UI.startEventDialog);
			}
		},
		showEvent:function(calEvent, jsEvent, view){
			
			var id = calEvent.id;
			 var choosenDate ='';
			if(typeof calEvent.start!='undefined'){
			   choosenDate = Math.round(calEvent.start.getTime()/1000);
			}
			if($('#event').dialog('isOpen') == true){
				// TODO: save event
				$('#event').dialog('destroy').remove();
			}else{
				Calendar.UI.loading(true);
				$('#dialog_holder').load(OC.filePath('calendar', 'ajax/event', 'show.form.php'), {id: id,choosendate:choosenDate}, Calendar.UI.startShowEventDialog);
			}
		},
		
		editEvent:function(calEvent, jsEvent, view){
			
			
			
			var choosenDate = calEvent.start;
			/*
			if (calEvent.editable == false || calEvent.source.editable == false) {
				return;
			}*/
			var id = calEvent.id;
			//if($('#event').dialog('isOpen') == true){
				// TODO: save event
				$('#event').dialog('destroy').remove();
			//}else{
				Calendar.UI.loading(true);
				$('#dialog_holder').load(OC.filePath('calendar', 'ajax/event', 'edit.form.php'), {id: id,choosendate:choosenDate}, Calendar.UI.startEventDialog);
			//}
		},
		submitDeleteEventForm:function(url){
			var id = $('input[name="id"]').val();
			
			$("#errorbox").css('display','none').empty();
			Calendar.UI.loading(true);
			
			$.post(url, {id:id}, function(data){
					Calendar.UI.loading(false);
					if(data.status == 'success'){
						$('#fullcalendar').fullCalendar('removeEvents',id);
						
						$('#event').dialog('destroy').remove();
					} else {
						$("#errorbox").css('display','block').html(t('calendar', 'Deletion failed'));
					}

			}, "json");
		},
		submitShowDeleteEventForm:function(url){
			var id = $('input[name="eventid"]').val();
			
			$("#errorbox").css('display','none').empty();
			Calendar.UI.loading(true);
			
			$.post(url, {id:id}, function(data){
					Calendar.UI.loading(false);
					if(data.status == 'success'){
						$('#fullcalendar').fullCalendar('removeEvents',id);
						
						$('#event').dialog('destroy').remove();
					} else {
						$("#errorbox").css('display','block').html(t('calendar', 'Deletion failed'));
					}

			}, "json");
		},
		submitDeleteEventSingleForm:function(url){
			
			var id = $('input[name="id"]').val();
			var choosenDate = $('input[name="choosendate"]').val();
			var startTime=$('input[name="fromtime"]').val();
			var allDay=$('input[name="allday"]').is(':checked');
			
			$("#errorbox").css('display','none').empty();
			Calendar.UI.loading(true);
			
			$.post(url, {id:id,choosendate:choosenDate,starttime:startTime,allday:allDay}, function(data){
					Calendar.UI.loading(false);
				
					if(data.status == 'success'){
						$('#fullcalendar').fullCalendar('refetchEvents');
						$('#event').dialog('destroy').remove();
							//alert(data.message);
					} else {
						$("#errorbox").css('display','block').html(t('calendar', 'Deletion failed'));
						alert(data.message);
					}

			}, "json");
			
		},
		removeExdate:function(choosenDate){
			
			var id = $('input[name="eventid"]').val();
    			
			$.post(OC.filePath('calendar', 'ajax/event', 'delete-exdate.php'), {id:id,choosendate:choosenDate}, function(data){
					
					if(data.status == 'success'){
						$('li.exdatelistrow[data-exdate='+choosenDate+']').remove();
						
					} else {
						$("#errorbox").css('display','block').html(t('calendar', 'Deletion failed'));
						alert(data.message);
					}

			}, "json");
			
		},
		
		validateEventForm:function(url){
			var post = $( "#event_form" ).serialize();
			$("#errorbox").css('display','none').empty();
			Calendar.UI.loading(true);
			$.post(url, post,
				function(data){
					Calendar.UI.loading(false);
					//alert(data.message);
					if(data.status == "error"){
						
						
						var output = missing_field + ": <br />";
						
						if(data.title == "true"){
							output = output + missing_field_title + "<br />";
						}
						if(data.cal == "true"){
							output = output + missing_field_calendar + "<br />";
						}
						if(data.from == "true"){
							output = output + missing_field_fromdate + "<br />";
						}
						if(data.fromtime == "true"){
							output = output + missing_field_fromtime + "<br />";
						}
						if(data.to == "true"){
							output = output + missing_field_todate + "<br />";
						}
						if(data.totime == "true"){
							output = output + missing_field_totime + "<br />";
						}
						if(data.endbeforestart == "true"){
							output = output + missing_field_startsbeforeends + "!<br/>";
						}
						if(data.dberror == "true"){
							output = "There was a database fail!";
						}
						$("#errorbox").css('display','block').html(output);
					} else
					if(data.status == 'success'){
						$('#event').dialog('destroy').remove();
						$('#fullcalendar').fullCalendar('refetchEvents');
					}
				},"json");
		},
		moveEvent:function(event, dayDelta, minuteDelta, allDay, revertFunc){
			$('.tipsy').remove();
			Calendar.UI.loading(true);
			
			$.post(OC.filePath('calendar', 'ajax/event', 'move.php'), { id: event.id, dayDelta: dayDelta, minuteDelta: minuteDelta, allDay: allDay?1:0, lastmodified: event.lastmodified},
			function(data) {
				Calendar.UI.loading(false);
				$('.tipsy').remove();
				if (data.status == 'success'){
					event.lastmodified = data.lastmodified;
					//alert(data.message);
					console.log("Event moved successfully");
				}else{
					revertFunc();
					//$('#fullcalendar').fullCalendar('refetchEvents');
					 $('#fullcalendar').fullCalendar('updateEvent', event);
				}
			});
		},
		resizeEvent:function(event, dayDelta, minuteDelta, revertFunc){
			$('.tipsy').remove();
			Calendar.UI.loading(true);
			$.post(OC.filePath('calendar', 'ajax/event', 'resize.php'), { id: event.id, dayDelta: dayDelta, minuteDelta: minuteDelta, lastmodified: event.lastmodified},
			function(data) {
				Calendar.UI.loading(false);
				$('.tipsy').remove();
				if (data.status == 'success'){
					event.lastmodified = data.lastmodified;
					console.log("Event resized successfully");
				}else{
					revertFunc();
					//$('#fullcalendar').fullCalendar('refetchEvents');
					$('#fullcalendar').fullCalendar('updateEvent', event);
				}
			});
		},
		showadvancedoptions:function(){
			$("#advanced_options").slideDown('slow');
			$("#advanced_options_button").css("display", "none");
		},
		showadvancedoptionsforrepeating:function(){
			if($("#advanced_options_repeating").is(":hidden")){
				//$('#advanced_options_repeating').slideDown('slow');
			}else{
				//$('#advanced_options_repeating').slideUp('slow');
			}
		},
		getEventPopupText:function(event){
			if (event.allDay){
				var timespan = $.fullCalendar.formatDates(event.start, event.end, 'ddd d MMMM[ yyyy]{ - [ddd d] MMMM yyyy}', {monthNamesShort: monthNamesShort, monthNames: monthNames, dayNames: dayNames, dayNamesShort: dayNamesShort}); //t('calendar', "ddd d MMMM[ yyyy]{ - [ddd d] MMMM yyyy}")
			}else{
				var timespan = $.fullCalendar.formatDates(event.start, event.end, 'ddd d MMMM[ yyyy] ' + defaulttime + '{ - [ ddd d MMMM yyyy]' + defaulttime + '}', {monthNamesShort: monthNamesShort, monthNames: monthNames, dayNames: dayNames, dayNamesShort: dayNamesShort}); //t('calendar', "ddd d MMMM[ yyyy] HH:mm{ - [ ddd d MMMM yyyy] HH:mm}")
				// Tue 18 October 2011 08:00 - 16:00
			}
			
			var html =
				'<div class="summary">' + escapeHTML(event.title)+'</div>' +
				'<div class="timespan">' + timespan + '</div>';
			if (event.rightsoutput!=false){
				html += '<div class="rightsreader">' + escapeHTML(event.rightsoutput) +'</div>';
				
			}	
			if (event.description){
				html += '<div class="description">' + escapeHTML(event.description) +'</div>';
				
			}
			if (event.categories.length>0){
				
				html += '<div class="categories">';
				$(event.categories).each(function(i, category){
					 html +='<a class="tag">'+category+'</a>';
				});
				html += '</div>';
			}
			
			
			return html;
		},
		lockTime:function(){
			if($('#allday_checkbox').is(':checked')) {
				$("#fromtime").attr('disabled', true)
					.addClass('disabled');
				$("#totime").attr('disabled', true)
					.addClass('disabled');
		} else {
				$("#fromtime").attr('disabled', false)
					.removeClass('disabled');
				$("#totime").attr('disabled', false)
					.removeClass('disabled');
					//$('#from').datetimepicker('option', 'showTimepicker',true);
					//$('#to').datetimepicker('option','showTimepicker',true);
					
			}
		},
		showCalDAVUrl:function(username, calname){
			$('#caldav_url').val(totalurl + '/' + username + '/' + calname);
			$('#caldav_url').show();
			$("#caldav_url_close").show();
		},
		reminder:function(task){
			if(task=='init'){
				$('#reminderemailinputTable').css('display', 'none');
				 $('#reminderdateTable').css('display', 'none');
				  $('#reminderTable').css('display', 'none');
				  
				Calendar.UI.reminder('reminder');
				Calendar.UI.reminder('remindertime');
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
		
		repeat:function(task){
			if(task=='init'){
				
				var advanced_byweeknoTitle=$('#byweekno').attr('title');
				
				$('#byweekno').multiselect({
					header: false,
					noneSelectedText: advanced_byweeknoTitle,
					selectedList: 2,
					minWidth:200
				});
				var weeklyoptionsTitle=$('#weeklyoptions').attr('title');
				$('#weeklyoptions').multiselect({
					header: false,
					noneSelectedText: weeklyoptionsTitle,
					selectedList: 2,
					minWidth:200
				});
				
				$('input[name="bydate"]').datepicker({
					dateFormat : 'dd-mm-yy'
				});
				
				var byyeardayTitle=$('#byyearday').attr('title');
				$('#byyearday').multiselect({
					header: false,
					noneSelectedText: byyeardayTitle,
					selectedList: 2,
					minWidth:200
				});
				
				var bymonthTitle=$('#bymonth').attr('title');
				$('#bymonth').multiselect({
					header: false,
					noneSelectedText: bymonthTitle,
					selectedList: 2,
					minWidth:200
				});
				var bymonthdayTitle=$('#bymonthday').attr('title');
				$('#bymonthday').multiselect({
					header: false,
					noneSelectedText: bymonthdayTitle,
					selectedList: 2,
					minWidth:200
				});
				Calendar.UI.repeat('end');
				Calendar.UI.repeat('month');
				Calendar.UI.repeat('year');
				Calendar.UI.repeat('repeat');
			}
			if(task == 'end'){
				$('#byoccurrences').css('display', 'none');
				$('#bydate').css('display', 'none');
				if($('#end option:selected').val() == 'count'){
					$('#byoccurrences').css('display', 'block');
				}
				if($('#end option:selected').val() == 'date'){
					$('#bydate').css('display', 'block');
				}
			}
			if(task == 'repeat'){
				$('#advanced_month').css('display', 'none');
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_weekofmonth').css('display', 'none');
				$('#advanced_byyearday').css('display', 'none');
				$('#advanced_bymonth').css('display', 'none');
				$('#advanced_byweekno').css('display', 'none');
				$('#advanced_year').css('display', 'none');
				$('#advanced_bymonthday').css('display', 'none');
				if($('#repeat option:selected').val() == 'monthly'){
					$('#advanced_month').css('display', 'block');
					Calendar.UI.repeat('month');
				}
				if($('#repeat option:selected').val() == 'weekly'){
					$('#advanced_weekday').css('display', 'block');
					
				}
				if($('#repeat option:selected').val() == 'yearly'){
					$('#advanced_year').css('display', 'block');
					Calendar.UI.repeat('year');
				}
				if($('#repeat option:selected').val() == 'doesnotrepeat'){
					//$('#advanced_options_repeating').slideUp('slow');
				}
			}
			if(task == 'month'){
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_weekofmonth').css('display', 'none');
				$('#advanced_bymonthday').css('display', 'none');
				if($('#advanced_month_select option:selected').val() == 'weekday'){
					$('#advanced_weekday').css('display', 'block');
					$('#advanced_weekofmonth').css('display', 'block');
				}
				if($('#advanced_month_select option:selected').val() == 'monthday'){
				
					$('#advanced_bymonthday').css('display', 'block');
					$('#bymonthday').multiselect({multiple:true,selectedList: 2});
					$("#bymonthday").multiselect('refresh');
				}
			}
			if(task == 'year'){
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_byyearday').css('display', 'none');
				$('#advanced_bymonth').css('display', 'none');
				$('#advanced_byweekno').css('display', 'none');
				$('#advanced_bymonthday').css('display', 'none');
				
				if($('#advanced_year_select option:selected').val() == 'byyearday'){
					$('#advanced_byyearday').css('display', 'block');
				}
				/*
				if($('#advanced_year_select option:selected').val() == 'byweekno'){
					$('#advanced_byweekno').css('display', 'block');
					$('#advanced_weekday').css('display', 'block');
				}*/
				if($('#advanced_year_select option:selected').val() == 'bydaymonth'){
					$('#advanced_bymonth').css('display', 'block');
					$('#advanced_bymonthday').css('display', 'block');
					
				}
			}

		},
		setViewActive: function(view){
			$('#view button').removeClass('active');
			var id;
			switch (view) {
				case 'agendaDay':
					id = 'onedayview_radio';
					break;
				case 'agendaWeek':
					id = 'oneweekview_radio';
					break;
				case 'agendaWorkWeek':
					id = 'oneweekworkview_radio';
					break;	
				case 'month':
					id = 'onemonthview_radio';
					break;
				case 'agendaThreeDays':
					id = 'threedayview_radio';
					break;		
				case 'list':
					id = 'listview_radio';
					break;
			}
			$('#'+id).addClass('active');
		},
		categoriesChanged:function(newcategories){
			categories = $.map(newcategories, function(v) {return v;});
			console.log('Calendar categories changed to: ' + categories);
			$('#category').multiple_autocomplete('option', 'source', categories);
		},
		Calendar:{
			overview:function(){
				if($('#choosecalendar_dialog').dialog('isOpen') == true){
					$('#choosecalendar_dialog').dialog('moveToTop');
				}else{
					Calendar.UI.loading(true);
					$('#dialog_holder').load(OC.filePath('calendar', 'ajax/calendar', 'overview.php'), function(){
						$('#choosecalendar_dialog').dialog({
							width : 600,
							height: 400,
							close : function(event, ui) {
								$(this).dialog('destroy').remove();
							}
						});
						Calendar.UI.loading(false);
					});
				}
			},
			activation:function(checkbox, calendarid)
			{
				Calendar.UI.loading(true);
				$.post(OC.filePath('calendar', 'ajax/calendar', 'activation.php'), { calendarid: calendarid, active: checkbox.checked?1:0 },
				  function(data) {
					Calendar.UI.loading(false);
					if (data.status == 'success'){
						
						checkbox.checked = data.active == 1;
						if (data.active == 1){
							$('#fullcalendar').fullCalendar('addEventSource', data.eventSource);
						}else{
							$('#fullcalendar').fullCalendar('removeEventSource', data.eventSource.url);
						}
						Calendar.Util.rebuildTaskView();
						Calendar.Util.rebuildCalView();
					}
					
				  });
			},
			newCalendar:function(object){
				var tr = $(document.createElement('tr'))
					.load(OC.filePath('calendar', 'ajax/calendar', 'new.form.php'),
						function(){
							//Calendar.UI.Calendar.colorPicker(this)
							$('input.minicolor').miniColors({
											letterCase: 'uppercase',
								});
							
							});
					//$('#newCalendar').hide();		
				$(object).closest('tr').after(tr).hide();
			},
			edit:function(object, calendarid){
				var tr = $(document.createElement('tr'))
					.load(OC.filePath('calendar', 'ajax/calendar', 'edit.form.php'), {calendarid: calendarid},
						function(){
							//Calendar.UI.Calendar.colorPicker(this)
							$('input.minicolor').miniColors({
											letterCase: 'uppercase',
								});
							});
				$(object).closest('tr').after(tr).hide();
			},
			deleteCalendar:function(calid){
				var check = confirm("Do you really want to delete this calendar?");
				
				if(check == false){
					return false;
				}else{
					$.post(OC.filePath('calendar', 'ajax/calendar', 'delete.php'), { calendarid: calid},
					  function(data) {
						if (data.status == 'success'){
							var url = 'ajax/events.php?calendar_id='+calid;
							$('#fullcalendar').fullCalendar('removeEventSource', url);
							$('#choosecalendar_dialog').dialog('destroy').remove();
							Calendar.UI.Calendar.overview();
							$('#calendar tr[data-id="'+calid+'"]').fadeOut(400,function(){
								$('#calendar tr[data-id="'+calid+'"]').remove();
							});
							$('#fullcalendar').fullCalendar('refetchEvents');
							Calendar.Util.rebuildCalView();
						}
					  });
				}
			},
			submit:function(button, calendarid){
				var displayname = $.trim($("#displayname_"+calendarid).val());
				var active =0;
				if( $("#edit_active_"+calendarid).is(':checked')){
					 active =1;
				}
				var description = $("#description_"+calendarid).val();
				var calendarcolor = $("#calendarcolor_"+calendarid).val();
				if(displayname == ''){
					$("#displayname_"+calendarid).css('background-color', '#FF2626');
					$("#displayname_"+calendarid).focus(function(){
						$("#displayname_"+calendarid).css('background-color', '#F8F8F8');
					});
				}

				var url;
				if (calendarid == 'new'){
					url = OC.filePath('calendar', 'ajax/calendar', 'new.php');
				}else{
					url = OC.filePath('calendar', 'ajax/calendar', 'update.php');
				}
				
				$.post(url, { id: calendarid, name: displayname, active: active, description: description, color: calendarcolor },
					function(data){
						if(data.status == 'success'){
							
							var prevElem=$(button).closest('tr').prev();
							
							prevElem.html(data.page).show().next().remove();
							
							$('#fullcalendar').fullCalendar('removeEventSource', data.eventSource.url);
							$('#fullcalendar').fullCalendar('addEventSource', data.eventSource);
							if (calendarid == 'new'){
								$(prevElem).attr('data-id',data.calid);
								$('#calendar > table:first').append('<tr><td colspan="6"><a href="#" id="chooseCalendar"><input type="button" value="' + newcalendar + '"></a></td></tr>');
							   	
							}
							Calendar.Util.rebuildCalView();
						}else{
							$("#displayname_"+calendarid).css('background-color', '#FF2626');
							$("#displayname_"+calendarid).focus(function(){
								$("#displayname_"+calendarid).css('background-color', '#F8F8F8');
							});
						}
					}, 'json');
			},
			cancel:function(button, calendarid){
				$(button).closest('tr').prev().show().next().remove();
			},
			colorPicker:function(container){
				// based on jquery-colorpicker at jquery.webspirited.com
				var obj = $('.colorpicker', container);
				var picker = $('<div class="calendar-colorpicker"></div>');
				//build an array of colors
				var colors = {};
				$(obj).children('option').each(function(i, elm) {
					colors[i] = {};
					colors[i].color = $(elm).val();
					colors[i].label = $(elm).text();
				});
				for (var i in colors) {
					picker.append('<span class="calendar-colorpicker-color ' + (colors[i].color == $(obj).children(":selected").val() ? ' active' : '') + '" rel="' + colors[i].label + '" style="background-color: ' + colors[i].color + ';"></span>');
				}
				picker.delegate(".calendar-colorpicker-color", "click", function() {
					$(obj).val($(this).attr('rel'));
					$(obj).change();
					picker.children('.calendar-colorpicker-color.active').removeClass('active');
					$(this).addClass('active');
				});
				$(obj).after(picker);
				$(obj).css({
					position: 'absolute',
					left: -10000
				});
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
						var itemType = 'event';
						var itemSource = $('#sharewith').data('item-source');
						var shareType = selected.item.value.shareType;
						var shareWith = selected.item.value.shareWith;
						$(this).val(shareWith);
						// Default permissions are Read and Share
						var permissions = OC.PERMISSION_READ | OC.PERMISSION_SHARE;
						$('#haveshareaction').val('1');
						OC.Share.share(itemType, itemSource, shareType, shareWith, permissions, function(data) {
							var newitem = '<li data-item-type="event"'
								+ 'data-share-with="'+shareWith+'" '
								+ 'data-item="'+itemSource+'" '
								+ 'data-permissions="'+permissions+'" '
								+ 'data-share-type="'+shareType+'">'+shareWith+' ('+(shareType == OC.Share.SHARE_TYPE_USER ? t('core', 'user') : t('core', 'group'))+')'
								+ '<span class="shareactions">'
								+ t('core', 'can edit')+' <input class="update" type="checkbox" > '
								+ t('core', 'share')+' <input class="share" type="checkbox"  checked="checked"> '
								+ t('core', 'delete')+' <input class="delete" type="checkbox" > '
								+ '<img style="cursor: pointer;" class="svg action unshare" title="'+ t('core', 'Unshare')+'" src="'+ OC.imagePath('core', 'actions/delete.svg') +'"></span></li>';
							$('.sharedby.eventlist').append(newitem);
							$('#sharedWithNobody').remove();
							$('#sharewith').val('');
							
							Calendar.UI.Share.buildShareEvents(shareWith);
							
						});
						
						return false;
					}
				});
	              Calendar.UI.Share.buildShareEvents('');
	              
				}	
			},
			
			buildShareEvents:function(shareWithSingle){
				
				
				var selector='.shareactions > input:checkbox';
				if(shareWithSingle!=''){
					var selector='li[data-share-with='+shareWithSingle+'] .shareactions > input:checkbox';
				}
				$(selector).on('change',function() {
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
	
					$('.shareactions > .unshare').click(function() {
						var container = $(this).parents('li').first();
						var itemType = container.data('item-type');
						var shareType = container.data('share-type');
						var itemSource = container.data('item');
						var shareWith = container.data('share-with');
						$('#haveshareaction').val('1');
						OC.Share.unshare(itemType, itemSource, shareType, shareWith, function() {
							container.remove();
						});
					});
				
			},
		},
		Drop:{
			init:function(){
				if (typeof window.FileReader === 'undefined') {
					console.log('The drop-import feature is not supported in your browser :(');
					return false;
				}
				droparea = document.getElementById('fullcalendar');
				droparea.ondrop = function(e){
					e.preventDefault();
					Calendar.UI.Drop.drop(e);
				}
				console.log('Drop initialized successfully');
			},
			drop:function(e){
				var files = e.dataTransfer.files;
				for(var i = 0;i < files.length;i++){
					var file = files[i];
					var reader = new FileReader();
					reader.onload = function(event){
						Calendar.UI.Drop.doImport(event.target.result);
						$('#fullcalendar').fullCalendar('refetchEvents');
					}
					reader.readAsDataURL(file);
				}
			},
			doImport:function(data){
				$.post(OC.filePath('calendar', 'ajax/import', 'dropimport.php'), {'data':data},function(result) {
					if(result.status == 'success'){
						$('#fullcalendar').fullCalendar('addEventSource', result.eventSource);
						$('#notification').html(result.message);
						$('#notification').slideDown();
						window.setTimeout(function(){$('#notification').slideUp();}, 5000);
						return true;
					}else{
						$('#notification').html(result.message);
						$('#notification').slideDown();
						window.setTimeout(function(){$('#notification').slideUp();}, 5000);
					}
				});
			}
		}
	},
	Settings:{
		//
	},

}

$.fullCalendar.views.list = ListView;
function ListView(element, calendar) {
	var t = this;

	// imports
	jQuery.fullCalendar.views.month.call(t, element, calendar);
	//jQuery.fullCalendar.BasicView.call(t, element, calendar, 'month');
	var opt = t.opt;
	var trigger = t.trigger;
	var eventElementHandlers = t.eventElementHandlers;
	var reportEventElement = t.reportEventElement;
	var formatDate = calendar.formatDate;
	var formatDates = calendar.formatDates;
	var addDays = $.fullCalendar.addDays;
	var cloneDate = $.fullCalendar.cloneDate;
	//var clearTime =  $.fullCalendar.clearTime;
	var skipHiddenDays = t.skipHiddenDays;
	
	function clearTime(d) {
	d.setHours(0);
	d.setMinutes(0);
	d.setSeconds(0); 
	d.setMilliseconds(0);
	return d;
}
	
	function skipWeekend(date, inc, excl) {
		inc = inc || 1;
		while (!date.getDay() || (excl && date.getDay()==1 || !excl && date.getDay()==6)) {
			addDays(date, inc);
		}
		return date;
	}
     
	// overrides
	t.name='list';
	t.render=render;
	t.renderEvents=renderEvents;
	t.setHeight=setHeight;
	t.setWidth=setWidth;
	t.clearEvents=clearEvents;
   
	function setHeight(height, dateChanged) {
	}

	function setWidth(width) {
	}
     
	function clearEvents() {
		//this.reportEventClear();
	}

	// main
	function sortEvent(a, b) {
		return a.start - b.start;
	}

	function render(date, delta) {
		var viewDays=14;
		if (delta) {
			addDays(date, delta * viewDays);
		}

		var start = addDays(cloneDate(date), -((date.getDay() - opt('firstDay') + viewDays) % viewDays));
		var end = addDays(cloneDate(start), viewDays);

		var visStart = cloneDate(start);
		skipHiddenDays(visStart);

		var visEnd = cloneDate(end);
		skipHiddenDays(visEnd, -1, true);
		
		t.title = formatDates(
			visStart,
			addDays(cloneDate(visEnd), -1),
			opt('titleFormat', 'week')
		);
		t.start = start;
		t.end = end;
		t.visStart = visStart;
		t.visEnd = visEnd;

	}

	function eventsOfThisDay(events, theDate) {
		var start = cloneDate(theDate, true);
		var end = addDays(cloneDate(start), 1);
		var retArr = new Array();
		
		$.each(events, function( i, value ) {
			var event_end = t.eventEnd(events[i]);
			if (events[i].start < end && event_end >= start) {
				retArr.push(events[i]);
			}
		});
		return retArr;
	}

	function renderEvent(event) {
		if (event.allDay) { //all day event
			var time = opt('allDayText');
		}
		else {
			var time = formatDates(event.start, event.end, opt('timeFormat', 'agenda'));
		}
		var classes = ['fc-event', 'fc-list-event'];
		classes = classes.concat(event.className);
		
		if (event.source) {
			classes = classes.concat(event.source.className || []);
		}
		
		var bgColor='#D4D5AA';
		var color='#000000';
		if(typeof calendarcolors[event.calendarid]!='undefined'){
			bgColor=calendarcolors[event.calendarid]['bgcolor'];
			color=calendarcolors[event.calendarid]['color'];
		}
		
		var imgReminder='';
			if(event.isalarm){
				imgReminder=' <img style="margin-top:2px;margin-bottom:-2px;" title="reminder" src="'+ OC.imagePath('core', 'actions/clock.svg') +'" width="14">';
			}
		
		var imgShare='';
			if(event.shared){
				imgShare=' <img style="margin-top:2px;margin-bottom:-2px;" title="shared" src="'+ OC.imagePath('core', 'actions/shared.svg') +'" width="14">';
			
			}
			
			var imgPrivate='';
			
			if(event.privat=='private'){
				imgPrivate=' <img style="margin-top:2px;margin-bottom:-2px;" title="privat" src="'+ OC.imagePath('core', 'actions/lock.svg') +'" width="12">';
			}
			if(event.privat=='confidential'){
				imgPrivate=' <img  title="confidential" src="'+ OC.imagePath('core', 'actions/toggle.svg') +'" width="12">';
			}
			eventLocation='';
			if(event.location!='' && event.location!=null && typeof event.location!='undefined') {
				
				eventLocation='<span class="location">'+event.location+'</span>';
			}
			var imgRepeating='';
			if(event.isrepeating){
				imgRepeating=' <img style="margin-top:2px;margin-bottom:-2px;" title="repeating" src="'+ OC.imagePath('core', 'actions/history.svg') +'" width="14">';
			}
		var html = '<tr class="fc-list-row">' +
			'<td>&nbsp;</td>' +
			'<td class="fc-list-time ">' +
			time +
			'</td>' +
			'<td>&nbsp;</td>' +
			'<td class="fc-list-event">' +
			'<span id="list' + event.id + '"' +
			' class="' + classes.join(' ') + '"' +
			'>' +
			'<span class="colorCal" style="margin-top:6px;background-color:'+bgColor+';">' +
			'&nbsp;'+
			'</span>' +
			'<span style="float:left;display:block;width:38px;">' +
			 imgShare+ ' '+imgPrivate+' '+imgRepeating+' '+imgReminder+'&nbsp;'+
			'</span>' +
			'<span class="fc-event-title">'+
			escapeHTML(event.title) +
			'</span>' +
			'<span>' +
			eventLocation+
			'</span>' +
			'</span>' +
			'</td>' +
			'</tr>';
		return html;
	}

	function renderDay(date, events) {
		
		var today = clearTime(new Date());
		
		var addTodayClass='';
		if (+date == +today) {
			addTodayClass='fc-list-today';
			
		}
		
		var dayRows = $('<tr>' +
			'<td colspan="4" class="fc-list-date '+addTodayClass+'">' +
			'&nbsp;<span>' +
			formatDate(date, opt('titleFormat', 'day')) +
			'</span>' +
			'</td>' +
			'</tr>');
			
			$.each(events, function( i, value ) {
		
			var event = events[i];
			var eventElement = $(renderEvent(event));
			triggerRes = trigger('eventRender', event, event, eventElement);
			if (triggerRes === false) {
				eventElement.remove();
			}else{
				if (triggerRes && triggerRes !== true) {
					eventElement.remove();
					eventElement = $(triggerRes);
				}
				$.merge(dayRows, eventElement);
				eventElementHandlers(event, eventElement);
				reportEventElement(event, eventElement);
			}
		});
		return dayRows;
	}

	function renderEvents(events, modifiedEventId) {
		events = events.sort(sortEvent);

		var table = $('<table class="fc-list-table" align="center"></table>');
		var total = events.length;
		if (total > 0) {
			var date = cloneDate(t.visStart);
			while (date <= t.visEnd) {
				var dayEvents = eventsOfThisDay(events, date);
				if (dayEvents.length > 0) {
					table.append(renderDay(date, dayEvents));
				}
				date=addDays(date, 1);
			}
		}else{
			table=$('<div>').text('No Events');
			
		}

		this.element.html(table);
	}
}

function formatDatePretty(date,formatOpt){
	if(typeof date=='number'){
		date=new Date(date);
	}
	return $.datepicker.formatDate(formatOpt, date);
}

/*
var openEvent = function(id) {
	if(typeof Calendar !== 'undefined') {
		Calendar.openEvent(id);
	} else {
		window.location.href = OC.linkTo('calendar', 'index.php') + '#' + id;
	}
};
*/


$(document).ready(function(){
	//Calendar.UI.initScroll();
	var bWeekends=true;
	if(defaultView=='agendaWorkWeek'){
		bWeekends=false;
	}
	$('#fullcalendar').fullCalendar({
		header: {center:'title',left:'',right:''},
		firstDay: firstDay,
		editable: true,
		defaultView: defaultView,
		aspectRatio: 1.35,
		weekNumberTitle: 'KW',
		weekNumbers:true,
		weekMode:'variable',
		firstHour:8,
		lazyFetching: false,
		weekends:bWeekends,
		timeFormat: {
			agenda: agendatime,
			'': defaulttime
			},
		columnFormat: {
			month: t('calendar', 'ddd'),    // Mon
			week: t('calendar', 'ddd M/d'), // Mon 9/7
			agendaThreeDays: t('calendar', 'dddd M/d'), // Mon 9/7
			day: t('calendar', 'dddd M/d')  // Monday 9/7
			},
		titleFormat: {
			month: t('calendar', 'MMMM yyyy'),
					// September 2009
			week: t('calendar', "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}"),
					// Sep 7 - 13 2009
			day: t('calendar', 'dddd, MMM d, yyyy'),
					// Tuesday, Sep 8, 2009
			},
		axisFormat: defaulttime,
		monthNames: monthNames,
		monthNamesShort: monthNamesShort,
		dayNames: dayNames,
		dayNamesShort: dayNamesShort,
		allDayText: allDayText,
		viewRender: function(view,element) {
			//$('#datecontrol_date').text($('<p>').html(view.title).text());
			
			if (view.name != defaultView) {
				$.post(OC.filePath('calendar', 'ajax', 'changeview.php'), {v:view.name},function(data){
					//$('#fullcalendar').fullCalendar('option', 'aspectRatio', 3);
				});
				defaultView = view.name;
			}
		
			Calendar.UI.setViewActive(view.name);
		    Calendar.Util.rebuildWidthCalendar();
		   
		  
		   
		  // if(typeof(timelineInterval) != "undefined") window.clearInterval(timelineInterval);
		  //	timelineInterval = window.setInterval(Calendar.Util.setTimeline, 10000);
			
			try {
				Calendar.Util.setTimeline();
			} catch(err) { }
			/*
			if (view.name == 'agendaWeek' || view.name == 'agendaWorkWeek') {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 0.1);
				
			}
			else {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 1.35);
				
				
			}*/
		},
		
		selectable: true,
		selectHelper: true,
		select: Calendar.UI.newEvent,
		eventClick: Calendar.UI.showEvent,
		eventDrop: Calendar.UI.moveEvent,
		eventResize: Calendar.UI.resizeEvent,
		
		eventRender: function(event, element) {
			
			var imgReminder='';
			if(event.orgevent){
				//alert(event.orgcal['calendarcolor']);
				//element.find('.fc-event-inner').prepend('<div style="margin-top:2px;margin-bottom:-2px;width:14px;float:left;height:14px;border:1px solid #000; background-color:'+event.orgevent['calendarcolor']+'">&nbsp;</div>');
			  element.css('border','2px dotted #000000');
			}
			if(event.isalarm){
				imgReminder=' <img style="margin-top:2px;margin-bottom:-2px;" title="reminder" src="'+ OC.imagePath('core', 'actions/clock.svg') +'" width="14">';
				element.find('.fc-event-inner').prepend('<div style="width:14px;float:left;height:14px;">'+imgReminder+'</div>');
			}
			
			var imgRepeating='';
			if(event.isrepeating){
				imgRepeating=' <img style="margin-top:2px;margin-bottom:-2px;" title="repeating" src="'+ OC.imagePath('core', 'actions/history.svg') +'" width="14">';
				element.find('.fc-event-inner').prepend('<div style="width:14px;float:left;height:14px;">'+imgRepeating+'</div>');
			}
			var imgShare='';
			if(event.shared){
				imgShare=' <img style="margin-top:2px;margin-bottom:-2px;" title="'+ t('core', 'Shared')+'" src="'+ OC.imagePath('core', 'actions/shared.svg') +'" width="14">';
				element.find('.fc-event-inner').prepend('<div style="width:14px;float:left;height:14px;">'+imgShare+'</div>');
			}
			
			var imgPrivate='';
			
			if(event.privat=='private'){
				imgPrivate=' <img style="margin-top:2px;margin-bottom:-2px;" title="privat" src="'+ OC.imagePath('core', 'actions/lock.svg') +'" width="12">';
				element.find('.fc-event-inner').prepend('<div style="width:14px;height:14px;float:left;">'+imgPrivate+'</div>');
			}
			if(event.privat=='confidential'){
				imgPrivate=' <img title="confidential" src="'+ OC.imagePath('core', 'actions/toggle.svg') +'" width="12">';
				element.find('.fc-event-inner').prepend('<div style="width:14px;height:14px;float:left;">'+imgPrivate+'</div>');
			}
			
			//element.find('.fc-event-title').text($("<div/>").html(escapeHTML(event.title)).text());
			
			
			if (event.categories.length > 0){
				 var $categories = $('<div>').addClass('categories').appendTo(element.find('.fc-event-title'));
			  	    $(event.categories).each(function(i, category){
						$categories.append($('<a>').addClass('tag').text(category));
					});
			}
			/*
			element.tipsy({
				className: 'tipsy-event',
				opacity: 0.9,
				gravity:$.fn.tipsy.autoBounds(150, 's'),
				fade:true,
				delayIn: 400,
				html:true,
				title:function() {
					return Calendar.UI.getEventPopupText(event);
				}
			});*/
		},
		loading: Calendar.UI.loading,
		eventSources: eventSources
	});
	
	
	/***NEW ***/
	
	
	$('.inputTasksRow').each(function(i,el){
		  $(el).click(Calendar.Util.completedTaskHandler);
	});
	
	
	
	/**END**/
	/*
	$('#datecontrol_date').datepicker({
		changeMonth: true,
		changeYear: true,
		showButtonPanel: true,
		beforeShow: function(input, inst) {
			var calendar_holder = $('#fullcalendar');
			var date = calendar_holder.fullCalendar('getDate');
			//inst.input.datepicker('setDate', date);
			alert(calendar_holder.fullCalendar('getView').title);
			inst.input.text(calendar_holder.fullCalendar('getView').title);
			//$('#datecontrol_date').text(calendar_holder.fullCalendar('getView').title);
			return inst;
		},
		onSelect: function(value, inst) {
			var date = inst.input.datepicker('getDate');
			$('#fullcalendar').fullCalendar('gotoDate', date);
		}
	});
	*/
	//fillWindow($('#content'));
	OCCategories.changed = Calendar.UI.categoriesChanged;
	OCCategories.app = 'calendar';
	OCCategories.type = 'event';
	
	 Calendar.Util.calViewEventHandler();
	 
	$('#onedayview_radio').click(function(){
		$('#fullcalendar').fullCalendar('option', 'weekends', true);
		$('#fullcalendar').fullCalendar('changeView', 'agendaDay');
		$
	});
	
	$('#oneweekview_radio').click(function(){
	$('#fullcalendar').fullCalendar('option', 'weekends', true);
		$('#fullcalendar').fullCalendar('changeView', 'agendaWeek');
	});
	
	
	$('#oneweekworkview_radio').click(function(){
		$('#fullcalendar').fullCalendar('option', 'weekends', false);
		$('#fullcalendar').fullCalendar('changeView', 'agendaWorkWeek');
	});
	
	$('#onemonthview_radio').click(function(){
		$('#fullcalendar').fullCalendar('option', 'weekends', true);
		$('#fullcalendar').fullCalendar('changeView', 'month');
		
	});
	

	$('#threedayview_radio').click(function(){
		$('#fullcalendar').fullCalendar('option', 'weekends', true);
		$('#fullcalendar').fullCalendar('changeView', 'agendaThreeDays');
		
	});
	
	$('#listview_radio').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'list');
	});
	$('#today_input').click(function(){
		$('#fullcalendar').fullCalendar('today');
	});
	$('#datecontrol_left').click(function(){
		$('#fullcalendar').fullCalendar('prev');
	});
	$('#datecontrol_today').click(function(){
		$('#fullcalendar').fullCalendar('today');
	});
	$('#datecontrol_right').click(function(){
		$('#fullcalendar').fullCalendar('next');
	});
	Calendar.UI.Share.init();
	Calendar.UI.Drop.init();
	$('#choosecalendarGeneralsettings').on('click keydown', function(event) {
		event.preventDefault();
		OC.appSettings({appid:'calendar', loadJS:true, cache:false, scriptName:'settingswrapper.php'});
	});
	
	
	Calendar.Util.rebuildWidthCalendar();
	
	$('#tasknavActive').on('click', function(event){
			
			event.stopPropagation();
			var checkedTask='false';
			if($(this).hasClass('button-info')){
				$(this).removeClass('button-info');
				$('#rightCalendarNav').addClass('isHiddenTask');
	            $('#rightCalendarNav').html('');
	             Calendar.Util.rebuildWidthCalendar();
	             checkedTask='false';
			}else{
				$(this).addClass('button-info');
			    $('#rightCalendarNav').removeClass('isHiddenTask');
	            Calendar.Util.rebuildTaskView();
	             checkedTask='true';
			}
			$.post(OC.filePath('calendar', 'ajax/settings', 'settasknav.php'),{checked:checkedTask});
			
		});
	
	$('#calendarnavActive').on('click', function(event){
			
			event.stopPropagation();
			var checkedCal='false';
			if($(this).hasClass('button-info')){
				$(this).removeClass('button-info');
				$('#leftcontent').addClass('isHiddenCal');
	            $('#leftcontent').html('');
	             Calendar.Util.rebuildWidthCalendar();
	             checkedCal='false';
			}else{
				$(this).addClass('button-info');
			    $('#leftcontent').removeClass('isHiddenCal');
	            Calendar.Util.rebuildCalView();
	             checkedCal='true';
			}
			$.post(OC.filePath('calendar', 'ajax/settings', 'setcalendarnav.php'),{checked:checkedCal});
			
		});
	
	
	
	
	$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#controls').height() - $('#header').height() - 15);

 // liveReminderCheck();

});

	$(window).bind('hashchange', function() {
		    Calendar.Util.checkShowEventHash();
	});
   




