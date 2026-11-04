/**
 * Due Date Schema fields.
 *
 * Shared settings rows for gradable content (quizzes in core, assignments in the add-on).
 * Exposed as `window.llms_builder.due_date_fields()` so add-ons can append them to their own schemas.
 *
 * @since [version]
 * @version [version]
 */
define( [], function() {

	/**
	 * Retrieve the course of the model (quiz or assignment) via its parent lesson.
	 *
	 * @since [version]
	 *
	 * @param {Object} model Backbone model.
	 * @return {Object|false}
	 */
	function get_course( model ) {
		var lesson = model.get_parent ? model.get_parent() : false;
		return lesson && lesson.get_course ? lesson.get_course() : false;
	}

	/**
	 * Retrieve the due date settings rows.
	 *
	 * @since [version]
	 *
	 * @return {Array[]}
	 */
	function fields() {

		return [
			[
				{
					id: 'due-date-heading',
					label: LLMS.l10n.translate( 'Due Date' ),
					type: 'heading',
					detail: LLMS.l10n.translate( 'Students can still submit after the due date; late submissions are flagged in reporting. Per-student due dates can be set from the student\'s course reporting screen.' ),
				},
			], [
				{
					attribute: 'due_method',
					id: 'due-method',
					label: LLMS.l10n.translate( 'Due' ),
					type: 'select',
					options: function() {

						var options = [
							{ key: '', val: LLMS.l10n.translate( 'No due date' ) },
							{ key: 'date', val: LLMS.l10n.translate( 'On a specific date' ) },
							{ key: 'enrollment', val: LLMS.l10n.translate( '# of days after course enrollment' ) },
						],
						course = get_course( this );

						if ( 'start' === this.get( 'due_method' ) || ( course && course.get( 'start_date' ) ) ) {
							options.push( { key: 'start', val: LLMS.l10n.translate( '# of days after course start date' ) } );
						}

						return options;

					},
				},
				{
					attribute: 'due_days',
					condition: function() {
						return ( -1 !== [ 'enrollment', 'start' ].indexOf( this.get( 'due_method' ) ) );
					},
					id: 'due-days',
					label: LLMS.l10n.translate( '# of days' ),
					min: 0,
					type: 'number',
				},
				{
					attribute: 'due_date',
					condition: function() {
						return ( 'date' === this.get( 'due_method' ) );
					},
					date_format: 'Y-m-d',
					id: 'due-date',
					label: LLMS.l10n.translate( 'Date' ),
					timepicker: 'false',
					type: 'datepicker',
				},
				{
					attribute: 'due_time',
					condition: function() {
						return !! this.get( 'due_method' );
					},
					datepicker: 'false',
					date_format: 'h:i A',
					id: 'due-time',
					label: LLMS.l10n.translate( 'Time (optional)' ),
					tip: LLMS.l10n.translate( 'Time of day in the site\'s timezone. Leave empty for the end of the day (11:59 PM).' ),
					type: 'datepicker',
				},
			],
		];

	}

	window.llms_builder.due_date_fields = fields;

	return fields;

} );
