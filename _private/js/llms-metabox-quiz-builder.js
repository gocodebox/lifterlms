( function( $ ) {

	window.llms = window.llms || {};

	/**
	 * Retrieve the Question ID from any element inside a tr.llms-question element
	 *
	 * @example      var id = $( '.element' ).getQuestionId();
	 *
	 * @return int
	 *
	 * @since  2.4.0
	 */
	$.fn.getQuestionId = function() {

		var $q = this.closest( 'tr.llms-question' );

		if ( ! $q.length ) {
			return 0;
		}

		return $q.attr( 'data-question-id' );

	};


	/**
	 * Initialize select2 on select elements
	 *
	 * @return obj
	 *
	 * @since  2.4.0
	 */
	$.fn.select2ify = function() {

		this.select2({
			allowClear: false,
			ajax: {
				dataType: 'JSON',
				delay: 250,
				method: 'POST',
				url: window.ajaxurl,
				data: function( params ) {
					return {
						term: params.term,
						page: params.page,
						action: 'query_quiz_questions',
						post_id: self.quiz_id,
					};
				},
				processResults: function( data, params ) {
					return {
						results: $.map( data.items, function( item ) {

							return {
								text: item.name,
								id: item.id,
							};

						} ),
						pagination: {
							more: data.more
						}
					};

				},
			},
			cache: true,
			placeholder: 'Select a Question',
			multiple: false,
			width: '100%',
		});

		return this;

	};

	/**
	 * UI for managing the quiz settings
	 *
	 * @since  2.4.0
	 */
	window.llms.metabox_quiz_settings = function() {

		if ( $( '#_llms_show_results').attr('checked') ) {
			$( '#_llms_show_correct_answer' ).parent().parent().show();
			$( '#_llms_show_options_description_right_answer ').parent().parent().show();
			$( '#_llms_show_options_description_wrong_answer').parent().parent().show();
		}

		$( '#_llms_show_results').on('change', function() {
			if( $( '#_llms_show_results' ).attr( 'checked' ) ) {
				$( '#_llms_show_correct_answer' ).parent().parent().fadeIn(300);
				$( '#_llms_show_options_description_right_answer ').parent().parent().fadeIn(300);
				$( '#_llms_show_options_description_wrong_answer').parent().parent().fadeIn(300);
			} else {
				$( '#_llms_show_correct_answer' ).parent().parent().fadeOut(300);
				$( '#_llms_show_options_description_right_answer ').parent().parent().fadeOut(300);
				$( '#_llms_show_options_description_wrong_answer').parent().parent().fadeOut(300);
			}
		});

	};




	/**
	 * UI for adding and removing questions from the quiz
	 *
	 * @since  2.4.0
	 */
	window.llms.metabox_quiz_builder = function() {

		/**
		 * WordPress Post ID of the Quiz
		 * @type int
		 *
		 * @since  2.4.0
		 */
		this.quiz_id = null;

		/**
		 * Initialize
		 * @return void
		 *
		 * @since  2.4.0
		 */
		this.init = function() {

			// bind dom events
			this.bind();
			this.bind_sortable();

			// set the total points on load
			this.set_total_points();

			this.quiz_id = $( '#post_ID' ).val();

		};


		/**
		 * Bind DOM Events
		 * @return void
		 *
		 * @since  2.4.0
		 */
		this.bind = function() {

			var self = this,
				$delegate = $( '#llms-single-options' );

			// setup all existing (php loaded) questions as select2 elements
			$delegate.find( '.llms-question select' ).select2ify();

			// update points whenever points change
			$( '.llms-points' ).blur( function ( e ) {

				e.preventDefault();
				self.set_total_points();

			} );

			// add a new question on question click
			$( '#add_new_question' ).on( 'click', function( e ) {

				e.preventDefault();
				self.add_new_question( $( this ) );

			} );

			/**
			 * When a select item changes update an HTML data-attr with the ID of the question
			 * Allows easy access to the question id from any element inside a question tr
			 */
			$delegate.on( 'change', '.llms-question-select', function( ) {

				var $el = $( this );
				$el.closest( 'tr.llms-question' ).attr( 'data-question-id', $el.val() );

			} );

			// handle click event for the edit icon
			$delegate.on( 'click', '.llms-fa-edit', function( e ) {

				e.preventDefault();
				window.open( self.get_question_edit_link( $( this ).getQuestionId() ) );

			} );

			$delegate.on( 'click', '.llms-remove-question', function( e ) {

				$( this ).closest( 'tr.llms-question' ).remove();
				self.set_total_points();

			} );

			// when the # of points changes, update total points
			$delegate.on( 'keyup', '.llms-points', function( ) {

				self.set_total_points();

			} );


			// prevent selecting the same question multiple times on one quiz
			$delegate.on( 'select2:selecting', 'select', function( e ) {

				var this_id = e.params.args.data.id,
					selected_ids = self.get_selected_question_ids();

				if ( selected_ids.indexOf( this_id ) !== -1 ) {

					e.preventDefault();
					alert( 'You cannot select the same question more than once per quiz.' );

				}

			} );

		};

		/**
		 * Bind the UI Sortable event
		 * @return void
		 *
		 * @since  2.4.0
		 */
		this.bind_sortable = function() {

			$( '.question-list' ).sortable( {

				axis: 'y',
				cursor: 'move',
				forcePlaceholderSize: true,
				placeholder: 'placeholder',
				items: '.list_item',

				helper: function( e, tr ) {

					var $originals = tr.children(),
						$helper = tr.clone();

					$helper.children().each( function( i ) {

						$( this ).width( $originals.eq( i ).width() );

					} );

					return $helper;

				},

				start: function( e, ui ) {

					ui.item.data( 'start_pos', ui.item.index() );

				}

			} );

		};



		/**
		 * Handle the click even for adding a new question
		 * @param obj   $btn   jQuery selector of the clicked button
		 *
		 * @since  2.4.0
		 */
		this.add_new_question = function( $btn ) {

			var self = this,
				$html = self.get_question_html();

			$html.find( 'select' ).select2ify( );

			$html.appendTo( '#llms-single-options .question-list tbody' ).hide().fadeIn( 300 );

			this.set_total_points();

		};


		/**
		 * Retrieve the URL to edit a question post type
		 * @param  int    question_id   WP Post ID of the question
		 * @return string
		 *
		 * @since  2.4.0
		 */
		this.get_question_edit_link = function( question_id ) {

			var link = window.llms.admin_url + 'post.php?action=edit&post=' + question_id;

			return link;

		};


		/**
		 * Retrieve the total points of all questions currently in a quiz
		 * @return int       total points
		 *
		 * @since  2.4.0
		 */
		this.get_total_points = function() {

			var sum = 0;
			$( '#llms-single-options .llms-points' ).each( function() {
				sum += Number( $( this ).val() );
			} );

			return sum;

		};


		/**
		 * Retrieve the number of questions currently in the quiz
		 * @return int
		 *
		 * @since  2.4.0
		 */
		this.get_total_questions = function() {

			return $( '#llms-single-options tbody tr' ).length;

		};


		// move this into PHP so it can be translated and be more dry with the actual template
		this.get_question_html = function() {

			return $( $( '#llms-single-question-template' ).html() ).find( 'tr' );

		}


		/**
		 * Retrieve an array of currently selected question ids
		 * @return array
		 *
		 * @since  2.4.0
		 */
		this.get_selected_question_ids = function() {

			var r = [];

			$( '#llms-single-options .llms-question' ).each( function() {

				var id = $( this ).attr( 'data-question-id' );

				if ( id && '0' !== id ) {

					r.push( id );

				}

			} );

			return r;

		};

		/**
		 * Update the total points element total
		 *
		 * @since  2.4.0
		 */
		this.set_total_points = function() {

			$( '#llms_points_total' ).text( this.get_total_points() );

		};

		// go
		this.init();

	};

	var a = new window.llms.metabox_quiz_builder(),
		b = new window.llms.metabox_quiz_settings();

} )( jQuery );
