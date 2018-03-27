/**
 * Model relationships mixin
 * @since    [version]
 * @version  [version]
 */
define( [], function() {

	return {

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
						type: 'switch',
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
						attribute: 'drip_method',
						id: 'drip-method',
						label: LLMS.l10n.translate( 'Drip Method' ),
						switch_attribute: 'drip_method',
						type: 'select',
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

							if ( this.get_course().get( 'start_date' ) ) {
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
							return ( 'date' === this.get( 'drip_method' ) );
						},
						datepicker: 'false',
						date_format: 'h:i A',
						id: 'time-available',
						label: LLMS.l10n.translate( 'Time' ),
						type: 'datepicker',
					},
				],
			],
		},

	};

} );
