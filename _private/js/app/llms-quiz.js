/* global LLMS, $ */
/* jshint strict: false */

/**
 * Front End Quiz Class
 * @type     {Object}
 * @since    1.0.0
 * @version  [version]
 */
LLMS.Quiz = {

	/**
	 * Main Container Element
	 * @type  obj
	 */
	$container: null,

	current_question: 0,
	prev_question: 0,
	questions: {},

	/**
	 * Records current status of a quiz session
	 * If a user attempts to navigate away from a quiz
	 * while taking the quiz they'll be warned that their progress
	 * will not be saved if this status is not null
	 * @type  boolean
	 */
	status: null,

	/**
	 * init
	 * loads class methods
	 * @since    1.0.0
 	 * @version  [version]
	 */
	init: function() {

		var $R = LLMS.Rest,
			post_type = ['llms_quiz'];

		if ( $R.is_path( post_type ) || $( 'body' ).hasClass( 'single-llms_quiz' ) ) {
			this.bind();
		}

	},

	/**
	 * Bind DOM events
	 * @return void
	 * @since    1.0.0
	 * @version  [version]
	 */
	bind: function() {

		var self = this;

		this.$container = $( '#llms-quiz-question-wrapper' );

		// start quiz
		$( '#llms_start_quiz' ).on( 'click', function( e ) {
			e.preventDefault();
			self.start_quiz();
		} );

		$( '.view-summary' ).on( 'click', function( e ) {
			e.preventDefault();
			var accordion = $('.accordion');
			if ( accordion.hasClass('hidden' )) {
				accordion.fadeIn(300);
				accordion.removeClass('hidden');
				$( this ).text( LLMS.l10n.translate( 'Hide Summary' ) );
			} else {
				accordion.fadeOut(300);
				accordion.addClass('hidden');
				$( this ).text( LLMS.l10n.translate( 'View Summary' ) );
			}
		} );

		// draw quiz grade circular chart
		$( '.llms-donut' ).each( function() {
			LLMS.Donut( $( this ) );
		} );

		// redirect to attempt on attempt selection change
		$( '#llms-quiz-attempt-select' ).on( 'change', function() {
			var val = $( this ).val();
			if ( val ) {
				window.location.href = val;
			}
		} );

		// warn when quiz is running and user tries to leave the page
		$( window ).on( 'beforeunload', function() {
			if ( self.status ) {
				return 'Are you sure you wish to quit this quiz attempt?';
			}
		} );

		// complete the quiz attempt when user leaves if the quiz is running
		$( window ).on( 'unload', function() {
			if ( self.status ) {
				self.complete_quiz();
			}
		} );

	},

	/**
	 * Answer a Question
	 * @return   void
	 * @since    1.0.0
	 * @version  [version]
	 */
	answer_question: function() {

		var self = this;

		if ( !$( 'input[name="llms_option_selected"]:checked' ).length ) {

			var msg = LLMS.l10n.translate( 'You must enter an answer to continue.' );

			self.$container.find( '.llms-error' ).remove();
			self.$container.prepend( '<p class="llms-error">' + msg + '</p>' );
			return;

		}

		LLMS.Ajax.call( {
			data: {
				action: 'quiz_answer_question',
				answer: $( 'input[name=llms_option_selected]:checked' ).val(),
				question_id: $( '#question-id' ).val(),
				question_type: $( '#question-type' ).val(),
				quiz_id: $( '#quiz-id' ).val(),
			},
			beforeSend: function() {

				self.toggle_loader( 'show', 'Loading Question...' );

			},
			success: function( r ) {

				self.toggle_loader( 'hide' );

				if ( r.data && r.data.html ) {

					self.load_question( r.data.html );

				} else if ( r.data && r.data.redirect ) {

					self.redirect( r.data.redirect );

				} else if ( r.message ) {

					self.$container.append( '<p>' + r.message + '</p>' );

				} else {

					var msg = LLMS.l10n.translate( 'An unknown error occurred. Please try again.' );
					self.$container.append( '<p>' + msg + '</p>' );

				}

			}

		} );

	},

	/**
	 * Complete the quiz
	 * Called when timed quizzes reach time limit
	 * & during unload events to record the attempt as abandoned
	 * @return   void
	 * @since    1.0.0
	 * @version  [version]
	 */
	complete_quiz: function() {

		var self = this;

		LLMS.Ajax.call( {
			data: {
				action: 'quiz_end',
				quiz_id: $( '#quiz-id' ).val(),
			},
			beforeSend: function() {

				self.toggle_loader( 'show', 'Grading Quiz...' );

			},
			success: function( r ) {

				self.toggle_loader( 'hide' );

				if ( r.data && r.data.redirect ) {

					self.redirect( r.data.redirect );

				} else if ( r.message ) {

					this.$container.append( '<p>' + r.message + '</p>' );

				} else {

					var msg = LLMS.l10n.translate( 'An unknown error occurred. Please try again.' );
					this.$container.append( '<p>' + msg + '</p>' );

				}

			}

		} );

	},

	/**
	 * Redirect on quiz comlpetion / timeout
	 * @param    string   url  redirect url
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	redirect: function( url ) {

		$( '#llms-quiz-timer' ).hide();
		this.toggle_loader( 'show', 'Grading Quiz...' );
		this.status = null;
		window.location.href = url;

	},

	/**
	 * Return to the previous question
	 * @return   void
	 * @since    1.0.0
 	 * @version  [version]
	 */
	previous_question: function() {

		var self = this;

		self.toggle_loader( 'show', 'Loading Question...' );

		setTimeout( function() {
			self.toggle_loader( 'hide' );
			self.load_question( self.questions[ self.prev_question ] );
		}, 100 );

	},

	/**
	 * Start a Quiz via AJAX call
	 * @return   void
	 * @since    1.0.0
	 * @version  [version]
	 */
	start_quiz: function () {

		var self = this;

		// bind sumbission event for answering questions
		this.$container.on( 'click', '#llms_answer_question', function( e ) {
			e.preventDefault();
			self.answer_question();
		} );

		// bind submission event for navigating backwards
		this.$container.on( 'click', '#llms_prev_question', function( e ) {
			e.preventDefault();
			self.previous_question();
		} );

		LLMS.Ajax.call( {
			data: {
				action: 'quiz_start',
				attempt_key: $( '#llms-attempt-key' ).val(),
				lesson_id : $( '#llms-lesson-id' ).val(),
				quiz_id : $( '#llms-quiz-id' ).val(),
			},
			beforeSend: function() {

				self.status = true;
				$( '#llms-quiz-wrapper, #quiz-start-button' ).remove();
				$( 'html, body' ).stop().animate( {scrollTop: 0 }, 500 );
				self.toggle_loader( 'show', 'Loading Quiz...' );

			},
			success: function( r ) {

				self.toggle_loader( 'hide' );

				if ( r.data && r.data.html ) {

					// start the quiz timer
					self.start_quiz_timer();

					// show the quiz timer
					$( '#llms-quiz-timer' ).show();

					self.load_question( r.data.html );

				} else if ( r.message ) {

					this.$container.append( '<p>' + r.message + '</p>' );

				} else {

					var msg = LLMS.l10n.translate( 'An unknown error occurred. Please try again.' );
					this.$container.append( '<p>' + msg + '</p>' );

				}

			}

		} );

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
	 * Load the HTML of a question into the DOM and the question cache
	 * @param    string   html  string of html
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	load_question: function( html ) {

		var $html = $( html ),
			qid = $html.find( '#question-id' ).val();

		if ( !this.questions[ qid ] ) {
			this.questions[ qid ] = $html;
		}

		this.prev_question = this.current_question;
		this.current_question = qid;

		this.$container.append( $html );

	},

	/**
	 * Show or hide the "loading" spinnr with an option message
	 * @param    string   display  show|hide
	 * @param    string   msg      text to display when showing
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	toggle_loader: function( display, msg ) {

		if ( 'show' === display ) {

			msg = msg || 'Loading...';

			this.$container.empty();
			LLMS.Spinner.start( this.$container );
			this.$container
				.append( '<div class="llms-quiz-loading">' + LLMS.l10n.translate( msg ) + '</div>' );

		} else {

			LLMS.Spinner.stop( this.$container );
			this.$container.find( '.llms-quiz-loading' ).remove();

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
	 * @since    1.0.0
 	 * @version  1.0.0
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
	 * @since    1.0.0
 	 * @version  1.0.0
	 */
	pad: function(n) {
		return (n < 10 ? '0' : '') + n;
	},

};
