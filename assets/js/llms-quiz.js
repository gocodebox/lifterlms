/* global LLMS, $ */
/* jshint strict: true */

/**
 * Front End Quiz Class
 * @type     {Object}
 * @since    1.0.0
 * @version  3.24.3
 */
;( function( $ ) {

	var quiz = {

		/**
		 * Selector of all the available button elemens
		 * @type  obj
		 */
		$buttons: null,

		/**
		 * Main Question Container Element
		 * @type  obj
		 */
		$container: null,

		/**
		 * Main Quiz container UI element
		 * @type  obj
		 */
		$ui: null,

		/**
		 * Attempt key for the current quiz
		 * @type  {[type]}
		 */
		attempt_key: null,

		/**
		 * Question ID of the current question
		 * @type  {Number}
		 */
		current_question: 0,

		/**
		 * Total number of questions in the current quiz
		 * @type  {Number}
		 */
		total_questions: 0,

		/**
		 * Object of quiz question HTML
		 * @type  {Object}
		 */
		questions: {},

		/**
		 * Validator functions for question types
		 * Third party custom question types can register validators for use when answering questiosn
		 * @type  {Object}
		 */
		validators: {},

		/**
		 * Records current status of a quiz session
		 * If a user attempts to navigate away from a quiz
		 * while taking the quiz they'll be warned that their progress
		 * will not be saved if this status is not null
		 * @type  boolean
		 */
		status: null,

		/**
		 * Bind DOM events
		 * @return void
		 * @since    1.0.0
		 * @version  3.16.6
		 */
		bind: function() {

			var self = this;

			// start quiz
			$( '#llms_start_quiz' ).on( 'click', function( e ) {
				e.preventDefault();
				self.start_quiz();
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
					return LLMS.l10n.translate( 'Are you sure you wish to quit this quiz attempt?' );
				}
			} );

			// complete the quiz attempt when user leaves if the quiz is running
			$( window ).on( 'unload', function() {
				if ( self.status ) {
					self.complete_quiz();
				}
			} );

			$( document ).on( 'llms-post-append-question', self.post_append_question );

			// register validators
			this.register_validator( 'content', this.validate );
			this.register_validator( 'choice', this.validate_choice );
			this.register_validator( 'picture_choice', this.validate_choice );
			this.register_validator( 'true_false', this.validate_choice );

		},

		/**
		 * Add an error message to the UI
		 * @param    string   msg  error message string
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		add_error: function( msg ) {

			var self = this;

			self.$container.find( '.llms-error' ).remove();
			var $err = $( '<p class="llms-error">' + msg + '<a href="#"><i class="fa fa-times-circle" aria-hidden="true"></i></a></p>' );
			$err.on( 'click', 'a', function( e ) {
				e.preventDefault();
				$err.fadeOut( '200' );
				setTimeout( function() {
					$err.remove();
				}, 210 );
			} );
			self.$container.append( $err );

		},

		/**
		 * Answer a Question
		 * @param    obj   $btn   jQuery object for the "Next Lesson" button
		 * @return   void
		 * @since    1.0.0
		 * @version  3.16.6
		 */
		answer_question: function( $btn ) {

			var self = this,
				$question = this.$container.find( '.llms-question-wrapper' ),
				type = $question.attr( 'data-type' ),
				valid;

			if ( ! this.validators[ type ] ) {

				console.log( 'No validator registered for question type ' + type );
				return;

			}

			valid = this.validators[ type ]( $question );
			if ( ! valid || true !== valid.valid || !valid.answer ) {
				return self.add_error( valid.valid );
			}

			LLMS.Ajax.call( {
				data: {
					action: 'quiz_answer_question',
					answer: valid.answer,
					attempt_key: self.attempt_key,
					question_id: $question.attr( 'data-id' ),
					question_type: $question.attr( 'data-type' ),
				},
				beforeSend: function() {

					var msg = $btn.hasClass( 'llms-button-quiz-complete' ) ? LLMS.l10n.translate( 'Grading Quiz...' ) : LLMS.l10n.translate( 'Loading Question...' );
					self.toggle_loader( 'show', msg );

					self.update_progress_bar( 'increment' );

				},
				success: function( r ) {

					self.toggle_loader( 'hide' );

					if ( r.data && r.data.html ) {

						// load html from the cached questions if it exists already
						if ( r.data.question_id && self.questions[ 'q-' + r.data.question_id ] ) {

							self.load_question( self.questions[ 'q-' + r.data.question_id ] );

						// load html from server if the question's never been seen before
						} else {
							self.load_question( r.data.html );
						}

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
		 * @version  3.9.0
		 */
		complete_quiz: function() {

			var self = this;

			LLMS.Ajax.call( {
				data: {
					action: 'quiz_end',
					attempt_key: self.attempt_key,
				},
				beforeSend: function() {

					self.toggle_loader( 'show', 'Grading Quiz...' );

				},
				success: function( r ) {

					self.toggle_loader( 'hide' );

					if ( r.data && r.data.redirect ) {

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
		 * Retrieve the index of a question by question id
		 * @param    int   qid  WP Post ID of the question
		 * @return   int
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		get_question_index: function( qid ) {

			return Object.keys( this.questions ).indexOf( 'q-' + qid );

		},

		/**
		 * Redirect on quiz comlpetion / timeout
		 * @param    string   url  redirect url
		 * @return   void
		 * @since    3.9.0
		 * @version  3.16.0
		 */
		redirect: function( url ) {

			this.toggle_loader( 'show', 'Grading Quiz...' );
			this.status = null;
			window.location.href = url;

		},

		/**
		 * Return to the previous question
		 * @return   void
		 * @since    1.0.0
	 	 * @version  3.16.6
		 */
		previous_question: function() {

			var self = this;

			self.toggle_loader( 'show', LLMS.l10n.translate( 'Loading Question...' ) );
			self.update_progress_bar( 'decrement' );

			var ids = Object.keys( self.questions ),
				curr = ids.indexOf( 'q-' + self.current_question ),
				prev_id = ids[0];

			if ( curr >= 1 ) {
				prev_id = ids[ curr - 1 ];
			}

			setTimeout( function() {
				self.toggle_loader( 'hide' );
				self.load_question( self.questions[ prev_id ] );
			}, 100 );

		},

		/**
		 * Register question type validator functions
		 * @param    string     type  question type id
		 * @param    function   func  callback function to validate the question with
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		register_validator: function( type, func ) {

			this.validators[ type ] = func;

		},

		/**
		 * Start a Quiz via AJAX call
		 * @return   void
		 * @since    1.0.0
		 * @version  3.24.3
		 */
		start_quiz: function () {

			var self = this;

			this.load_ui_elements();
			this.$ui = $( '#llms-quiz-ui' );
			this.$buttons = $( '#llms-quiz-nav button' );
			this.$container = $( '#llms-quiz-question-wrapper' );

			// bind sumbission event for answering questions
			$( '#llms-next-question, #llms-complete-quiz' ).on( 'click', function( e ) {
				e.preventDefault();
				self.answer_question( $( this ) );
			} );

			// bind submission event for navigating backwards
			$( '#llms-prev-question' ).on( 'click', function( e ) {
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
					self.toggle_loader( 'show', LLMS.l10n.translate( 'Loading Quiz...' ) );

				},
				error: function( r, s, t ) {
					console.log( r, s, t );
				},
				success: function( r ) {

					self.toggle_loader( 'hide' );

					if ( r.data && r.data.html ) {

						// start the quiz timer when a time limit is set
						if ( r.data.time_limit ) {
							self.start_quiz_timer( r.data.time_limit );
						}


						self.attempt_key = r.data.attempt_key;
						self.total_questions = r.data.total;

						self.load_question( r.data.html );

					} else if ( r.message ) {

						self.$container.append( '<p>' + r.message + '</p>' );

					} else {

						var msg = LLMS.l10n.translate( 'An unknown error occurred. Please try again.' );
						self.$container.append( '<p>' + msg + '</p>' );

					}

				}

			} );

			/**
			 * Use JS mouse events instead of CSS :hover because iOS is really smart
			 * @see: https://css-tricks.com/annoying-mobile-double-tap-link-issue/
			 */
			if ( ! LLMS.is_touch_device() ) {

				this.$ui.on( 'mouseenter', 'li.llms-choice label', function() {
					$( this ).addClass( 'hovered' );
				} );
				this.$ui.on( 'mouseleave', 'li.llms-choice label', function() {
					$( this ).removeClass( 'hovered' );
				} );

			}

		},

		/**
		 * Start Quiz Timer
		 * Gets minutes from hidden field
		 * Not used as actual quiz timer. Quiz is timed on the server from the quiz class
		 * Calculates minutes to milliseconds and then converts to hours / minutes
		 * When time limit reaches 0 calls complete_quiz() to complete quiz.
		 * @return Calls get_count_down at a set interval of 1 second
		 * @since    1.0.0
		 * @version  3.16.0
		 */
		start_quiz_timer: function( total_minutes ) {

			// create and append the UI for the countdown clock
			var $el = $( '<div class="llms-quiz-timer" id="llms-quiz-timer" />' ),
				msg = LLMS.l10n.translate( 'Time Remaining' );

			$el.append( '<i class="fa fa-clock-o" aria-hidden="true"></i><span class="screen-reader-text">' + msg + '</span>' );
			$el.append( '<div id="llms-tiles" class="llms-tiles"></div>' );

			$( '#llms-quiz-header' ).append( $el );

			// start the timer
			var self = this,
				target_date = new Date().getTime() + ( ( total_minutes * 60 ) * 1000 ), // set the countdown date
				time_limit = ( ( total_minutes * 60 ) * 1000 ),
				countdown = document.getElementById('llms-tiles'), // get tag element
				days, hours, minutes, seconds; // variables for time units

			//set actual timer
			setTimeout( function() {
				self.complete_quiz();
			}, time_limit + 1000 );

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

			// call get_count_down every 1 second
			setInterval( function () {
				self.getCountdown(
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
		},

		/**
		 * Trigger events
		 * @param    string   event  event to trigger
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		trigger: function( event ) {

			var self = this;

			// trigger question submission for the current question
			if ( 'answer_question' === event ) {

				if ( this.get_question_index( self.current_question ) === self.total_questions ) {

					$( '#llms-complete-quiz' ).trigger( 'click' );

				} else {

					$( '#llms-next-question' ).trigger( 'click' );

				}

			}

		},

		/**
		 * Load the HTML of a question into the DOM and the question cache
		 * @param    string   html  string of html
		 * @return   void
		 * @since    3.9.0
		 * @version  3.16.6
		 */
		load_question: function( html ) {

			var $html = $( html ),
				qid = $html.attr( 'data-id' );

			// cache the question HTML for faster rewinds
			if ( !this.questions[ 'q-' + qid ] ) {
				this.questions[ 'q-' + qid ] = $html;
			}

			this.update_progress( qid );

			this.current_question = qid;

			$( document ).trigger( 'llms-pre-append-question', $html );

			this.$container.append( $html );

			$( document ).trigger( 'llms-post-append-question', $html );

		},

		/**
		 * Constructs the quiz UI & adds the elements into the DOM
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.9
		 */
		load_ui_elements: function() {

			var $html = $( '<div class="llms-quiz-ui" id="llms-quiz-ui" />' ),
				$header = $( '<header class="llms-quiz-header" id="llms-quiz-header" />')
				$footer = $( '<footer class="llms-quiz-nav" id="llms-quiz-nav" />' );

			$footer.append( '<button class="button large llms-button-action" id="llms-next-question" name="llms_next_question" type="submit">' + LLMS.l10n.translate( 'Next Question' ) + '</button>' );
			$footer.append( '<button class="button large llms-button-action llms-button-quiz-complete" id="llms-complete-quiz" name="llms_complete_quiz" type="submit" style="display:none;">' + LLMS.l10n.translate( 'Complete Quiz' ) + '</button>' );
			$footer.append( '<button class="button llms-button-secondary" id="llms-prev-question" name="llms_prev_question" type="submit" style="display:none;">' + LLMS.l10n.translate( 'Previous Question' ) + '</button>' );

			$header.append( '<div class="llms-progress"><div class="progress-bar-complete"></div></div>' );
			$footer.append( '<div class="llms-quiz-counter" id="llms-quiz-counter"><span class="llms-current"></span><span class="llms-sep">/</span><span class="llms-total"></span></div>')

			$html.append( $header )
				 .append( '<div class="llms-quiz-question-wrapper" id="llms-quiz-question-wrapper" />' )
				 .append( $footer );

			$( '#llms-quiz-wrapper' ).after( $html );

		},

		/**
		 * Perform actions on question HTML after it's been appended to the DOM
		 * @param    obj      event  js event object
		 * @param    obj      html   js HTML object
		 * @return   void
		 * @since    3.16.6
		 * @version  3.16.6
		 */
		post_append_question: function( event, html ) {

			var $html = $( html );

			if ( $html.find( 'audio' ).length ) {
				wp.mediaelement.initialize();
			}

		},

		/**
		 * Show or hide the "loading" spinnr with an option message
		 * @param    string   display  show|hide
		 * @param    string   msg      text to display when showing
		 * @return   void
		 * @since    3.9.0
		 * @version  3.16.6
		 */
		toggle_loader: function( display, msg ) {

			if ( 'show' === display ) {

				msg = msg || LLMS.l10n.translate( 'Loading...' );

				this.$buttons.attr( 'disabled', 'disabled' );

				this.$container.empty();
				LLMS.Spinner.start( this.$container );
				this.$container.append( '<div class="llms-quiz-loading">' + LLMS.l10n.translate( msg ) + '</div>' );

			} else {

				LLMS.Spinner.stop( this.$container );
				this.$buttons.removeAttr( 'disabled' );
				this.$container.find( '.llms-quiz-loading' ).remove();

			}

		},

		/**
		 * Update the progress bar and toggle button avalability based on question the question being shown
		 * @param    {[type]}   qid  [description]
		 * @return   {[type]}
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_progress: function( qid ) {

			var index = this.get_question_index( qid ),
				progress;

			if ( -1 === index ) {
				return;
			}

			index++;

			$( '#llms-quiz-counter .llms-current' ).text( index );
			if ( index === 1 ) {
				$( '#llms-quiz-counter .llms-total' ).text( this.total_questions );
				$( '#llms-quiz-counter' ).show();
			}

			// handle prev question
			if ( index >= 2 ) {
				$( '#llms-prev-question' ).show();
			} else {
				$( '#llms-prev-question' ).hide();
			}

			if ( index === this.total_questions ) {
				$( '#llms-next-question' ).hide();
				$( '#llms-complete-quiz' ).show();
			} else {
				$( '#llms-next-question' ).show();
				$( '#llms-complete-quiz' ).hide();
			}

		},

		/**
		 * Increase progress bar ui elment
		 * @param    string   dir  update direction [increment|decrement]
		 * @return   void
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		update_progress_bar: function( dir ) {

			var index = this.get_question_index( this.current_question );
			if ( 'increment' === dir ) {
				index++;
			} else {
				index--;
			}

			progress = ( index / this.total_questions ) * 100;
			this.$ui.find( '.progress-bar-complete' ).css( 'width', progress + '%' );

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
		getCountdown: function( total_minutes, target_date, time_limit, days, hours, minutes, seconds, countdown ){

			// find the amount of "seconds" between now and target
			var current_date = new Date().getTime(),
				seconds_left = ( target_date - current_date ) / 1000;

			if ( seconds_left >= 0 ) {

				if ( ( seconds_left * 1000 ) < ( time_limit / 2 ) )  {

					$( '#llms-quiz-timer' ).addClass( 'color-half' );

				}

				if ( ( seconds_left * 1000 ) < ( time_limit / 4 ) )  {

					$( '#llms-quiz-timer' ).removeClass( 'color-half' );
					$( '#llms-quiz-timer' ).addClass( 'color-empty' );

				}

				days = this.pad( parseInt(seconds_left / 86400) );
				seconds_left = seconds_left % 86400;
				hours = this.pad( parseInt(seconds_left / 3600) );
				seconds_left = seconds_left % 3600;
				minutes = this.pad( parseInt( seconds_left / 60 ) );
				seconds = this.pad( parseInt( seconds_left % 60 ) );

				// format countdown string + set tag value
				countdown.innerHTML = '<span class="hours">' + hours + '</span>:<span class="minutes">' + minutes + '</span>:<span class="seconds">' + seconds + '</span>';
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

		/**
		 * Basic validation method which performs no validation and returns a valiation object
		 * in the format required by the application
		 * @param    obj   $question  jQuery selector of the question
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		validate: function( $question ) {
			return {
				answer: [],
				valid: true,
			};
		},

		/**
		 * Validates a choice question to ensure there's at least one checked input
		 * @param    obj   $question  jQuery selector of the question
		 * @return   obj
		 * @since    3.16.0
		 * @version  3.16.0
		 */
		validate_choice: function( $question ) {

			var ret = window.llms.quizzes.validate( $question ),
				checked = $question.find( 'input:checked' );

			if ( !checked.length ) {
				ret.valid = LLMS.l10n.translate( 'You must select an answer to continue.' );
			} else {
				checked.each( function() {
					ret.answer.push( $( this ).val() );
				} );
			}

			return ret;

		},

	};

	quiz.bind();

	window.llms = window.llms || {};
	window.llms.quizzes = quiz;

} )( jQuery );
