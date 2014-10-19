/**
 * Returns array of all lessons
 */
get_all_lessons = function() {
	var ajax = new Ajax('post', {'action':'get_lessons'}, true);
	ajax.get_all_posts();
}

get_all_sections = function() {
	var ajax = new Ajax('post', {'action':'get_sections'}, true);
	ajax.get_all_posts();
}

get_all_courses = function() {
	var ajax = new Ajax('post', {'action':'get_courses'}, true);
	ajax.get_all_posts();
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
