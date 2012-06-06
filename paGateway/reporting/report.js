/*
$(document).ready(function(){
	
	$('#date-from').datepicker();
	$('#date-to').datepicker();
	
	$('.top').addClass('hidden');
	$.waypoints.settings.scrollThrottle = 30;
	$('#wrapper').waypoint(function(event, direction) {
		$('.top').toggleClass('hidden', direction === "up");
		window.console && console.log("wrapper", this);
	}, {
		offset: '-100%'
	}).find('#main-nav-holder').waypoint(function(event, direction) {
		$(this).parent().toggleClass('sticky', direction === "down");
		window.console && console.log("event direction", this);
		event.stopPropagation();
	});
	
	alert("foo");
	
});
*/


$(document).ready(function() {
	$('.top').addClass('hidden');
	$.waypoints.settings.scrollThrottle = 30;
	$('#wrapper').waypoint(function(event, direction) {
		$('.top').toggleClass('hidden', direction === "up");
	}, {
		offset: '-100%'
	}).find('#main-nav-holder').waypoint(function(event, direction) {
		$(this).parent().toggleClass('sticky', direction === "down");
		event.stopPropagation();
	});
	
	alert("foo");
});