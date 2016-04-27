/* global LLMS, Ajax, $ */
/* jshint strict: false */

/**
 * Front End Quiz Class
 * Applies only to post type quiz
 * @type {Object}
 */
LLMS.Quiz = {

	/**
	 * init
	 * loads class methods
	 */
	init: function() {

		var $R = LLMS.Rest,
			post_type = ['llms_quiz'];

		if ( $R.is_path( post_type ) || $( 'body' ).hasClass( 'single-llms_quiz' ) ) {
			this.bind();
		}

	},

	/**
	 * Bind Method
	 * Handles dom binding on load
	 * @return {[type]} [description]
	 */
	bind: function() {
		var that = this;

		//hides the quiz timer when page loads
		$('#llms-quiz-timer').hide();

		// calls start quiz on "Start Quiz" button click
		$('#llms_start_quiz').click(function() {
			that.start_quiz();
			return false;
		});

		$('.view-summary').click(function() {
			var accordion = $('.accordion');
			if(accordion.hasClass('hidden')) {
				accordion.fadeIn(300);
				accordion.removeClass('hidden');
				$(this).text('Hide Summary');
			} else{
				accordion.fadeOut(300);
				accordion.addClass('hidden');
				$(this).text('View Summary');
			}
		});

		//draw quiz grade circular chart
		this.chart_quiz_grade();

	},

	/**
	 * Draws quiz grade circular charts
	 * @return {[void]}
	 */
	chart_quiz_grade: function() {

		/**
		 * Used for populating the quiz grade svg graph
		 * @type {[type]}
		 */
		var $llms_circ = $('.llms-animated-circle'),
			$llms_prog_count = $('.llms-progress-circle-count'),
			llms_grade_perc = $('#llms-grade-value').val(),
			llms_circ_offset = 430 * llms_grade_perc / 100;

		$llms_circ.css({
			'stroke-dashoffset' : 430 - llms_circ_offset
		});

		$llms_prog_count.html(Math.round(llms_grade_perc) + '%');

	},

	/**
	 * Start Quiz
	 * Finds values of quiz-id and user-id
	 * Calls ajax.start_quiz
	 * @return {[void]}
	 */
	start_quiz: function () {

		var post_id = $('#llms-quiz').val(),
			user_id = $('#llms-user').val(),
			ajax = new Ajax( 'post', {
				action : 'start_quiz',
				quiz_id : post_id,
				user_id : user_id
			}, true);

		ajax.start_quiz( post_id, user_id );
	},

	/**
	 * Answer Question
	 * Finds values of quiz-id, question-type, question-id and answer
	 * Calls ajax.answer_question
	 *
	 * @return {[void]}
	 */
	answer_question: function() {

		if ( $( 'input[name=llms_option_selected]:checked' ).length <= 0 ){

			$('#llms-quiz-question-wrapper .llms-error').remove();
			$('#llms-quiz-question-wrapper')
				.prepend( '<div class="llms-error">You must enter an answer to continue.</div>' );

		} else {

			var quiz_id = $('#llms-quiz').val(),
				question_type = $('#question-type').val(),
				question_id = $('#question-id').val(),
				answer = $('input[name=llms_option_selected]:checked').val(),

				ajax = new Ajax('post', {
					action : 'answer_question',
					quiz_id : quiz_id,
					question_type : question_type,
					question_id : question_id,
					answer : answer
				},true );

			ajax.answer_question(
				question_type,
				question_id,
				answer
			);

		}

	},

	/**
	 * Previous Question
	 * Finds quiz-id and question-id
	 * Calls ajax.previous_question to find the previous question
	 * @return {[void]}
	 */
	previous_question: function() {

		var quiz_id = $('#llms-quiz').val(),
			question_id = $('#question-id').val(),
			ajax = new Ajax('post', {
				action :'previous_question',
				quiz_id : quiz_id,
				question_id : question_id
			}, true);

		ajax.previous_question( quiz_id, question_id );
	},

	/**
	 * Start Quiz Timer
	 * Gets minutes from hidden field
	 * Not used as actual quiz timer. Quiz is timed on the server from the quiz class
	 * Calculates minutes to milliseconds and then converts to hours / minutes
	 *
	 * When time limit reaches 0 calls complete_quiz() to complete quiz.
	 *
	 * @return Calls get_count_down at a set interval of 1 second
	 */
	start_quiz_timer: function() {

		var total_minutes = $('#set-time').val();

		if ( total_minutes ) {

			var target_date = new Date().getTime() + ((total_minutes * 60 ) * 1000), // set the countdown date
				time_limit = ((total_minutes * 60 ) * 1000),
				days, hours, minutes, seconds, // variables for time units
				countdown = document.getElementById('tiles'), // get tag element
				that = this;

			//set actual timer
			setTimeout(
				function() {
					that.complete_quiz();
				}, time_limit );

			this.getCountdown(
				total_minutes,
				target_date,
				time_limit,
				days,
				hours,
				minutes,
				seconds,
				countdown
			);

			//call get_count_down every 1 second
			setInterval(function () {
				that.getCountdown(
					total_minutes,
					target_date,
					time_limit,
					days,
					hours,
					minutes,
					seconds,
					countdown
				);
			}, 1000 );
		}
	},

	/**
	 * Get Count Down
	 * Called every second to update the on screen countdown timer
	 * Changes color to yellow at 1/2 of total time
	 * Changes color to red at 1/4 of total time
	 * @param  {[int]} minutes     [description]
	 * @param  {[date]} target_date [description]
	 * @param  {[int]} time_limit  [description]
	 * @param  {[int]} days        [description]
	 * @param  {[int]} hours       [description]
	 * @param  {[int]} minutes     [description]
	 * @param  {[int]} seconds     [description]
	 * @param  {[int]} countdown   [description]
	 * @return Displays updates hours, minutes on quiz timer
	 */
	getCountdown: function(total_minutes, target_date, time_limit, days, hours, minutes, seconds, countdown){

		// find the amount of "seconds" between now and target
		var current_date = new Date().getTime(),
			seconds_left = (target_date - current_date) / 1000;
		if ( seconds_left >= 0 ) {

			if ( (seconds_left * 1000 ) < ( time_limit / 2 ) )  {

				$( '#tiles' ).removeClass('color-full');
				$( '#tiles' ).addClass('color-half');

			}

			if ( (seconds_left * 1000 ) < ( time_limit / 4 ) )  {

			$( '#tiles' ).removeClass('color-half');
			$( '#tiles' ).addClass('color-empty');

			}

			days = this.pad( parseInt(seconds_left / 86400) );
			seconds_left = seconds_left % 86400;
			hours = this.pad( parseInt(seconds_left / 3600) );
			seconds_left = seconds_left % 3600;
			minutes = this.pad( parseInt( seconds_left / 60 ) );
			seconds = this.pad( parseInt( seconds_left % 60 ) );
			// format countdown string + set tag value
			countdown.innerHTML = '<span>' + hours + ':</span><span>' + minutes + ':</span><span>' + seconds + '</span>';
		}
	},

	/**
	 * Pad Number
	 * pads number with 0 if single digit.
	 * @param  {[int]} n [number]
	 * @return {[string]} [padded number]
	 */
	pad: function(n) {
		return (n < 10 ? '0' : '') + n;
	},

	/**
	 * Complete Quiz
	 * Called by start_quiz_timer when countdown reaches 0
	 * @return Calls ajax.complete_quiz to end quiz
	 */
	complete_quiz: function() {

		var quiz_id = $('#llms-quiz').val(),
			question_type = $('#question-type').val(),
			question_id = $('#question-id').val(),
			answer = $('input[name=llms_option_selected]:checked').val(),
			ajax = new Ajax( 'post', {
				action : 'complete_quiz',
				quiz_id : quiz_id,
				question_id : question_id,
				question_type : question_type,
				answer : answer
			}, true);
		ajax.complete_quiz( quiz_id, question_id, question_type, answer );
	}
};
