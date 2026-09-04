/**
 * Lesson Schemas
 *
 * @since    3.17.0
 * @version  [version]
 */
define( [], function() {

	/**
	 * Whether the Advanced Videos promo fields should render.
	 *
	 * @since [version]
	 *
	 * @return {boolean}
	 */
	function is_av_promo_visible() {
		return ! window.llms_builder.av && !! this.get( 'video_embed' );
	}

	/**
	 * Dummy cascading options matching Advanced Videos selects.
	 *
	 * @since [version]
	 *
	 * @return {Array}
	 */
	function get_av_promo_options() {
		var disabled = LLMS.l10n.translate( 'Disabled' );
		return [
			{
				key: 'global',
				val: LLMS.l10n.replace( 'Global setting (%s)', {
					'%s': disabled,
				} ),
			},
			{
				key: 'course',
				val: LLMS.l10n.replace( 'Course setting (%s)', {
					'%s': disabled,
				} ),
			},
			{
				key: 'yes',
				val: LLMS.l10n.translate( 'Enabled' ),
			},
			{
				key: 'no',
				val: LLMS.l10n.translate( 'Disabled' ),
			},
		];
	}

	var schema = {

		default: {
			title: LLMS.l10n.translate( 'General Settings' ),
			toggleable: true,
			fields: [
			[
				{
					attribute: 'permalink',
					id: 'permalink',
					type: 'permalink',
		},
			], [
				{
					attribute: 'content',
					id: 'content',
					label: LLMS.l10n.translate( 'Content' ),
					type: 'editor',
					condition: function() {
						return '' === this.get( 'content' ) || 'yes' === this.get( 'content_added_in_builder' );
					},
		},
			], [
				{
					id: 'content-page-builder-notice',
					label: LLMS.l10n.translate( 'Content' ),
					type: 'page_builder_notice',
					condition: function() {
						return '' !== this.get( 'content' ) && 'yes' !== this.get( 'content_added_in_builder' );
					},
		},
			], [
				{
					attribute: 'video_embed',
						id: 'video-embed',
						label: LLMS.l10n.translate( 'Video Embed URL' ),
						type: 'video_embed',
			},
					{
						attribute: 'audio_embed',
						id: 'audio-embed',
						label: LLMS.l10n.translate( 'Audio Embed URL' ),
						type: 'audio_embed',
			},
			], [
					{
						attribute: 'free_lesson',
						id: 'free-lesson',
						label: LLMS.l10n.translate( 'Free Lesson' ),
						tip: LLMS.l10n.translate( 'Free lessons can be accessed without enrollment.' ),
						type: 'switch',
			},
					{
						attribute: 'require_passing_grade',
						id: 'require-passing-grade',
						label: LLMS.l10n.translate( 'Require Passing Grade on Quiz' ),
						tip: LLMS.l10n.translate( 'When enabled, students must pass this quiz before the lesson can be completed.' ),
						type: 'switch',
						condition: function() {
							return ( 'yes' === this.get( 'quiz_enabled' ) );
						},
			},
					{
						attribute: 'require_assignment_passing_grade',
						id: 'require-assignment-passing-grade',
						label: LLMS.l10n.translate( 'Require Passing Grade on Assignment' ),
						tip: LLMS.l10n.translate( 'When enabled, students must pass this assignment before the lesson can be completed.' ),
						type: 'switch',
						condition: function() {
							return ( 'undefined' !== window.llms_builder.assignments && 'yes' === this.get( 'assignment_enabled' ) );
						},
			},
					{
						attribute: 'points',
						id: 'points',
						label: LLMS.l10n.translate( 'Lesson Weight' ),
						label_after: LLMS.l10n.translate( 'POINTS' ),
						min: 0,
						max: 99,
						tip: LLMS.l10n.translate( 'Determines the weight of the lesson when calculating the overall grade of the course.' ),
						tip_position: 'top-left',
						type: 'number',
						condition: function() {
							return ( ( 'yes' === this.get( 'quiz_enabled' ) ) || ( 'undefined' !== window.llms_builder.assignments && 'yes' === this.get( 'assignment_enabled' ) ) );
						},
			},
			], [
				{
					attribute: 'has_minimum_time',
					id: 'has-minimum-time',
					label: LLMS.l10n.translate( 'Minimum Time on Lesson' ),
					tip: LLMS.l10n.translate( 'Require students to spend a minimum amount of time on this lesson before they can mark it complete' ),
					type: 'switch',
					condition: function() {
						return 'yes' !== this.get( 'free_lesson' );
					},
			},
			], [
				{
					attribute: 'minimum_time_hours',
					id: 'minimum-time-hours',
					label: LLMS.l10n.translate( 'Hours' ),
					min: 0,
					max: 999,
					type: 'number',
					condition: function() {
						return 'yes' === this.get( 'has_minimum_time' ) && 'yes' !== this.get( 'free_lesson' );
					},
			},
				{
					attribute: 'minimum_time_minutes',
					id: 'minimum-time-minutes',
					label: LLMS.l10n.translate( 'Minutes' ),
					min: 0,
					max: 59,
					type: 'number',
					condition: function() {
						return 'yes' === this.get( 'has_minimum_time' ) && 'yes' !== this.get( 'free_lesson' );
					},
			},
				{
					attribute: 'minimum_time_seconds',
					id: 'minimum-time-seconds',
					label: LLMS.l10n.translate( 'Seconds' ),
					min: 0,
					max: 59,
					type: 'number',
					condition: function() {
						return 'yes' === this.get( 'has_minimum_time' ) && 'yes' !== this.get( 'free_lesson' );
					},
			},
			], [
					{
						attribute: 'prerequisite',
						condition: function() {
							return ( false === this.is_first_in_course() );
						},
						id: 'prerequisite',
						label: LLMS.l10n.translate( 'Prerequisite' ),
						switch_attribute: 'has_prerequisite',
						type: 'switch-select',
						options: function() {
							return this.get_available_prereq_options();
						},
			},
				], [
					{
						label: LLMS.l10n.translate( 'Course Drip Method' ),
						id: 'course-drip',
						type: 'heading',
						condition: function() {
							return ( this.get_course() && 'yes' === this.get_course().get( 'lesson_drip' ) && this.get_course().get( 'drip_method' ) );
						},
						detail: LLMS.l10n.translate( 'Drip settings are currently set at the course level, under the Restrictions settings tab. Disable to allow lesson level drip settings.' ) + ' <a href=\"javascript:document.getElementById(\'llms-exit-button\').click()\">' + LLMS.l10n.translate( 'Edit Course' ) + '</a>',
					},
				], [
					{
						label: LLMS.l10n.translate( 'Course Drip Method' ),
						id: 'course-drip',
						type: 'heading',
						condition: function() {
							return ( ! this.get_course() || 'yes' !== this.get_course().get( 'lesson_drip' ) || ! this.get_course().get( 'drip_method' ) );
						},
						detail: LLMS.l10n.translate( 'Drip settings can be set at the course level to release course content at a specified interval, in the Restrictions settings tab.' ) + ' <a href=\"javascript:document.getElementById(\'llms-exit-button\').click()\">' + LLMS.l10n.translate( 'Edit Course' ) + '</a>',
					},
				], [
					{
						attribute: 'drip_method',
						id: 'drip-method',
						label: LLMS.l10n.translate( 'Drip Method' ),
						switch_attribute: 'drip_method',
						type: 'select',
						condition: function() {
							return ( ! this.get_course() || 'yes' !== this.get_course().get( 'lesson_drip' ) || ! this.get_course().get( 'drip_method' ) );
						},
						options: function() {

							var options = [
								{
									key: '',
									val: LLMS.l10n.translate( 'None' ),
							},
								{
									key: 'date',
									val: LLMS.l10n.translate( 'On a specific date' ),
							},
								{
									key: 'enrollment',
									val: LLMS.l10n.translate( '# of days after course enrollment' ),
							},
							];

							if ( this.get_course() && this.get_course().get( 'start_date' ) ) {
								options.push( {
									key: 'start',
									val: LLMS.l10n.translate( '# of days after course start date' ),
								} );
							}

							if ( 'yes' === this.get( 'has_prerequisite' ) ) {
								options.push( {
									key: 'prerequisite',
									val: LLMS.l10n.translate( '# of days after prerequisite lesson completion' ),
								} );
							}

							return options;

						},
			},
					{
						attribute: 'days_before_available',
						condition: function() {
							if ( this.get_course() && 'yes' === this.get_course().get( 'lesson_drip' ) && this.get_course().get( 'drip_method' ) ) {
								return false;
							}

							return ( -1 !== [ 'enrollment', 'start', 'prerequisite' ].indexOf( this.get( 'drip_method' ) ) );
						},
						id: 'days-before-available',
						label: LLMS.l10n.translate( '# of days' ),
						min: 0,
						type: 'number',
			},
					{
						attribute: 'date_available',
						date_format: 'Y-m-d',
						condition: function() {
							if ( this.get_course() && 'yes' === this.get_course().get( 'lesson_drip' ) && this.get_course().get( 'drip_method' ) ) {
								return false;
							}

							return ( 'date' === this.get( 'drip_method' ) );
						},
						id: 'date-available',
						label: LLMS.l10n.translate( 'Date' ),
						timepicker: 'false',
						type: 'datepicker',
			},
					{
						attribute: 'time_available',
						condition: function() {
							if ( this.get_course() && 'yes' === this.get_course().get( 'lesson_drip' ) && this.get_course().get( 'drip_method' ) ) {
								return false;
							}

							return ( 'date' === this.get( 'drip_method' ) );
						},
						datepicker: 'false',
						date_format: 'h:i A',
						id: 'time-available',
						label: LLMS.l10n.translate( 'Time' ),
			type: 'datepicker',
		},
			], [
				{
					label: LLMS.l10n.translate( 'Associated Event(s)' ),
					id: 'llms-events-promo',
					type: 'heading',
					detail: LLMS.l10n.translate( 'Schedule events for your lessons with the LifterLMS Events add-on.' ) + ' <a href="https://lifterlms.com/product/lifterlms-events/?utm_source=LifterLMS%20Plugin&utm_medium=Lesson%20Builder&utm_campaign=Events%20Addon%20Upsell" target="_blank">' + LLMS.l10n.translate( 'Learn More' ) + '</a>',
					condition: function() {
						return ! window.llms_builder.events;
					},
				},
			],
		],
	},

	streams: {
		title: LLMS.l10n.translate( 'Streams' ),
		toggleable: true,
		fields: [
			[
				{
					attribute: 'streams',
					id: 'streams',
					label: LLMS.l10n.translate( 'Streams' ),
					tip: LLMS.l10n.translate( 'Leave empty to include this lesson in every stream.' ),
					type: 'select',
					multiple: true,
					condition: function() {
						var course = this.get_course();
						return course && 'yes' === course.get( 'streams_enabled' ) && ( course.get( 'streams' ) || [] ).length;
					},
					options: function() {
						var course = this.get_course();
						if ( ! course ) {
							return [];
						}
						return _.map( course.get( 'streams' ) || [], function( stream ) {
							return {
								key: stream.id,
								val: stream.name,
							};
						} );
					},
				},
			],
		],
	},

	};

	schema.default.fields.splice(
		4,
		0,
			[
				{
					attribute: 'llms_av_promo_require',
					id: 'llms-av-promo-require',
					label: LLMS.l10n.translate( 'Require Video Completion' ),
					tip: LLMS.l10n.translate( 'When enabled, students must watch the entire video before they can progress to the next lesson or attempt a quiz associated with the lesson.' ),
					type: 'select',
					disabled: true,
					options: get_av_promo_options,
					condition: is_av_promo_visible,
				},
				{
					attribute: 'llms_av_promo_advance',
					id: 'llms-av-promo-advance',
					label: LLMS.l10n.translate( 'Auto-Advance Videos' ),
					tip: LLMS.l10n.translate( 'After a student completes the lesson video, a countdown timer is displayed and when the timer expires, the student is automatically redirected to the next lesson, quiz, or assignment.' ),
					type: 'select',
					disabled: true,
					options: get_av_promo_options,
					condition: is_av_promo_visible,
				},
			],
			[
				{
					id: 'llms-av-promo',
					type: 'heading',
					detail: LLMS.l10n.translate( 'Require lesson video completion, auto-advance lessons on video completion, customize video player controls, and more with the LifterLMS Advanced Videos add-on.' ) + ' <a href="https://lifterlms.com/product/advanced-videos/?utm_source=LifterLMS%20Plugin&utm_medium=Course%20Builder%20Upsell&utm_campaign=Plugin%20to%20Sale" target="_blank">' + LLMS.l10n.translate( 'Learn More' ) + '</a>',
					condition: is_av_promo_visible,
				},
			]
		);

	return window.llms.hooks.applyFilters( 'llms_define_lesson_schema', schema );

} );
