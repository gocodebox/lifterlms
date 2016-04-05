function Ajax (type, data, cache) {

	this.type = type;
	this.data = data;
	this.cache = cache;
	this.dataType = 'json';
	this.url = window.ajaxurl || window.llms.ajaxurl;

}

Ajax.prototype.get_sections = function () {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { section_template(response); }
	});
};

Ajax.prototype.get_lesson = function (lesson_id, row_id, type) {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { add_edit_link(response, lesson_id, row_id, type); },
	});
};

Ajax.prototype.get_lessons = function (section_id, section_position) {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { lesson_template(response, section_id, section_position); },
	});
};

Ajax.prototype.update_syllabus = function () {
	jQuery.ajax({
       // type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
        success 	: function(response) { console.log(JSON.stringify(response, null, 4)); },
        error 		: function(errorThrown){ console.log(errorThrown); },
	});
};

Ajax.prototype.get_all_posts = function () {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { return_data(response); },
	});
};

Ajax.prototype.get_all_engagements = function () {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { return_engagement_data(response); },
	});
};

Ajax.prototype.get_associated_lessons = function (section_id, section_position) {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { add_associated_lessons(response, section_id, section_position); },
	});
};

Ajax.prototype.get_question = function (question_id, row_id) {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { add_edit_link(response, question_id, row_id); },
	});
};

Ajax.prototype.start_quiz = function (quiz_id, user_id) {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		beforeSend: function() {

			jQuery( '#llms_start_quiz' ).hide();
			jQuery( '.llms-next-lesson' ).hide();
			jQuery('html, body').stop().animate({scrollTop: 0}, 500);
			jQuery('#llms-quiz-wrapper').empty();

			jQuery('#llms-quiz-question-wrapper').append( '<div id="loader">Loading Question...</div>' );

		},
		success: function( html ) {

			//start the quiz timer
			LLMS.Quiz.start_quiz_timer();

			//show the quiz timer
			jQuery('#llms-quiz-timer').show();

			//remove the loading message
			jQuery('#llms-quiz-question-wrapper #loader').remove();

			//append the returned html
			jQuery('#llms-quiz-question-wrapper').append( html );

			jQuery('#llms_answer_question').click(function() {

				//call answer question
				LLMS.Quiz.answer_question();

				return false;
			});
		}
	});
};

Ajax.prototype.answer_question = function ( quiz_id, question_type, question_id, answer ) {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		beforeSend: function() {
			jQuery('#llms-quiz-question-wrapper').empty();
			jQuery('#llms-quiz-question-wrapper').append( '<div id="loader">Loading Next Question...</div>' );
		},
		success: function( response ) {

			if ( response.redirect ) {
				window.location.replace( response.redirect );

			} else if ( response.message) {
				window.location.replace( response.redirect );

			} else {

				jQuery('#llms-quiz-question-wrapper #loader').remove();

				jQuery('#llms-quiz-question-wrapper').append( response.html );

				jQuery('#llms_answer_question').click(function() {
					LLMS.Quiz.answer_question();
					return false;
				});

				jQuery('#llms_prev_question').click(function() {
					LLMS.Quiz.previous_question();
					return false;
				});

			}
		} //end success
	});
};

Ajax.prototype.previous_question = function (quiz_id, question_id) {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		beforeSend: function() {

			jQuery('#llms-quiz-question-wrapper').empty();
			jQuery('#llms-quiz-question-wrapper').append( '<div id="loader">Loading Question...</div>' );

		},
		success: function( html ) {

			jQuery('#llms-quiz-question-wrapper #loader').remove();

			jQuery('#llms-quiz-question-wrapper').append( html );

			jQuery('#llms_answer_question').click(function() {
				LLMS.Quiz.answer_question();
				return false;
			});

			jQuery('#llms_prev_question').click(function() {
				LLMS.Quiz.previous_question();
				return false;
			});
		}

	});
};

Ajax.prototype.complete_quiz = function ( quiz_id, question_id, question_type, answer ) {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		beforeSend: function() {

			jQuery('#llms-quiz-question-wrapper').empty();
			jQuery('#llms-quiz-question-wrapper').append( '<div id="loader">Loading Quiz Results...</div>' );

		},
		success: function( response ) {

			//redirect back to quiz page
			window.location.replace( response.redirect );

		} //end success
	});
};

Ajax.prototype.getLessons = function () {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { return_data(response); },
	});
};

Ajax.prototype.getSections = function () {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { return_data(response); },
	});
};

Ajax.prototype.get_course_tracks = function () {
	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
        cache		: this.cache,
        dataType	: this.dataType,
		success		: function(response) { return_data(response); },
	});
};


Ajax.prototype.check_voucher_duplicate = function () {

	jQuery.ajax({
		type 		: this.type,
		url			: this.url,
		data 		: this.data,
		cache		: this.cache,
		dataType	: this.dataType,
		success		: function(response) {
			llms_on_voucher_duplicate(response.duplicates);
		}
	});
};
