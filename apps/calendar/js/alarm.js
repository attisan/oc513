
(function($){

  $.extend({
    playSound: function(){
      return $("<audio autoplay='autoplay'><source src='"+arguments[0]+".mp3' /><source src='"+arguments[0]+".ogg' /></audio>").appendTo('#reminderBox');
    }
  });

})(jQuery);

$(document).ready(function(){
	
	$('<div id="reminderBox" style="width:0;height:0;top:0;left:0;display:none;">').appendTo($('#body-user'));
	liveReminderCheck();
	
});



/**
 * Calls the server periodically every 1 min to check live calendar events
 * 
 */
function liveReminderCheck(){
	OC.Router.registerLoadedCallback(function(){
		var url = OC.Router.generate('liveReminderCheck');
		
		setInterval(function(){
		// alert('done');
			
			$.post(url,function(jasondata){
				if(jasondata.status == 'success'){
					openReminderDialog(jasondata.data);
				}
				//Calendar.Util.setTimeline();
			});
		}, 60000);
	});
}

var openReminderDialog=function(data){
			//var output='<audio autoplay="autoplay"><source src="'+OC.filePath('calendar','audio', 'ring.ogg')+'"></source><source src="'+OC.filePath('calendar','audio','ring.mp3')+'"></source></audio>';
			
			 $.playSound(oc_webroot+'/apps/calendar/audio/ring');
			 var output='';
			 $.each(data, function(i, elem) {
				  output+='<b>'+elem.startdate+'</b><br />';
				 output+='<a href="'+elem.link+'">'+elem.summary+'</a><br />';
				
				});
			$( "#reminderBox" ).html(output);	
			
			$( "#reminderBox" ).dialog({
			resizable: false,
			title : t('calendar', 'Reminder Alert'),
			width:350,
			height:200,
			modal: true,
			buttons: 
			[  { text:t('calendar', 'Ready'), click: function() {
			    	$( "#reminderBox" ).html('');	
			    	$( this ).dialog( "close" );
			    }
			    } 
			],
	
		});
  	 
		return false;

			
		}