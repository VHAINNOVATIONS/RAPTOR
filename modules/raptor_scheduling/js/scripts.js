/*
 * RAPTOR 2014
 * Copyright SAN Business Consultants for VA
 */

/**
 * Call this to place text into the textbox
 */
function setScheduleTextboxByName(sName, sValue)
{
    var oTB=document.getElementsByName(sName);
    oTB[0].value = sValue;
}

(function($) {
	$(document).bind('click', function (event) {
		if ($(event.target).is('#raptor-schedule-time')) {
			var now = new Date();
			$('[name=event_starttime_tx]').val(now.getHours() + ':' + (now.getMinutes() > 9 ? now.getMinutes() : '0' + now.getMinutes()));
		}
	})
})(jQuery);