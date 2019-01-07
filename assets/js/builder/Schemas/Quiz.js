/**
 * Quiz Schema
 * @since    3.17.6
 * @version  3.24.0
 */
define( [], function() {

	return window.llms.hooks.applyFilters( 'llms_define_quiz_schema', {

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
						id: 'description',
						label: LLMS.l10n.translate( 'Description' ),
						type: 'editor',
					},
				], [
					{
						attribute: 'passing_percent',
						id: 'passing-percent',
						label: LLMS.l10n.translate( 'Passing Percentage' ),
						min: 0,
						max: 100,
						tip: LLMS.l10n.translate( 'Minimum percentage of total points required to pass the quiz' ),
						type: 'number',
					},
					{
						attribute: 'allowed_attempts',
						id: 'allowed-attempts',
						label: LLMS.l10n.translate( 'Limit Attempts' ),
						switch_attribute: 'limit_attempts',
						tip: LLMS.l10n.translate( 'Limit the maximum number of times a student can take this quiz' ),
						type: 'switch-number',
					},
					{
						attribute: 'time_limit',
						id: 'time-limit',
						label: LLMS.l10n.translate( 'Time Limit' ),
						min: 1,
						max: 360,
						switch_attribute: 'limit_time',
						tip: LLMS.l10n.translate( 'Enforce a maximum number of minutes a student can spend on each attempt' ),
						type: 'switch-number',
					},
				], [
					{
						attribute: 'show_correct_answer',
						id: 'show-correct-answer',
						label: LLMS.l10n.translate( 'Show Correct Answers' ),
						tip: LLMS.l10n.translate( 'When enabled, students will be shown the correct answer to any question they answered incorrectly.' ),
						type: 'switch',
					},
					{
						attribute: 'random_questions',
						id: 'random-questions',
						label: LLMS.l10n.translate( 'Randomize Question Order' ),
						tip: LLMS.l10n.translate( 'Display questions in a random order for each attempt. Content questions are locked into their defined positions.' ),
						type: 'switch',
					},
				],

			],
		},

	} );

} );
