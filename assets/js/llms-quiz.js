jQuery(document).ready(function($) {

	$('#llms_start_quiz_depreciated').click(function() {
		get_quiz_questions();
		//alert('sript is loaded');
		return false;
	});
	
});

/**
 * Returns array of all questions
 */
get_quiz_questions = function() {
	var post_id = jQuery('#llms-quiz').val();
	var user_id = jQuery('#llms-user').val();
	console.log(post_id + ' ' + user_id);
	var ajax = new Ajax('post', { 'action':'get_quiz_questions', 'quiz_id' : post_id, 'user_id' : user_id }, true);
	ajax.get_quiz_questions(post_id, user_id);
}

get_quiz_full_page = function(questions, user_id) {
	console.log('get quiz full page called');
	console.log(questions);

}

jQuery(document).ready(function($) {
	var $llms_circ = $('.llms-animated-circle');
	var $llms_prog_count = $('.llms-progress-circle-count');
	var $llms_circ_val = $('.progress-range');

	var llms_grade_perc = $('#llms-grade-value').val();
	var llms_circ_offset = 430 * llms_grade_perc / 100;
	$llms_circ.css({
	"stroke-dashoffset" : 430 - llms_circ_offset
	})
	$llms_prog_count.html(Math.round(llms_grade_perc) + "%");
});
