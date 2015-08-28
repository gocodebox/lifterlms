jQuery(document).ready(function($) {

	$('#associated_course').chosen();
	$('#associated_section').chosen();
	$('#trigger-select').chosen();
	$('.question-select').chosen();
	$('.llms-meta-select').chosen();

	//needs to be put into the infusionsoft plugin
	$('#lifterlms_gateway_is_accepted_cards').chosen({width: '350px'});
	
});


jQuery('.metabox_submit').click(function(e) {
    e.preventDefault();
    jQuery('#publish').click();
});
	
/**
 * Returns array of all lessons
 */
get_all_lessons = function() {
	//var ajax = new Ajax('post', {'action':'get_all_posts', 'post_type' : 'lesson'}, true);
	//ajax.get_all_posts( post_type );
	var ajax = new Ajax('post', {'action':'getLessons'}, true);
	ajax.getLessons();
}

get_all_sections = function() {
	//var ajax = new Ajax('post', {'action':'get_all_posts', 'post_type' : 'section'}, true);
	//ajax.get_all_posts( post_type );
	var ajax = new Ajax('post', {'action':'getSections'}, true);
	ajax.getSections();
}

get_all_courses = function() {
	var ajax = new Ajax('post', {'action':'get_courses'}, true);
	ajax.get_all_posts();
}

get_all_course_tracks = function() {
	var ajax = new Ajax('post', {'action':'get_course_tracks'}, true);
	ajax.get_course_tracks();
}

get_all_emails = function() {
	var ajax = new Ajax('post', {'action':'get_emails'}, true);
	ajax.get_all_engagements();
}

get_all_achievements = function() {
	var ajax = new Ajax('post', {'action':'get_achievements'}, true);
	ajax.get_all_engagements();
}

get_all_certificates = function() {
	var ajax = new Ajax('post', {'action':'get_certificates'}, true);
	ajax.get_all_engagements();
}
