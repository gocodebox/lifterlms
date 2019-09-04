/**
 * Lessons Collection
 *
 * @since    3.13.0
 * @version  3.16.0
 */
define( [
		'Collections/Lessons',
		'Collections/QuestionChoices',
		'Collections/Questions',
		'Collections/QuestionTypes',
		'Collections/Sections'
	], function(
		Lessons,
		QuestionChoices,
		Questions,
		QuestionTypes,
		Sections
	) {

		return {
			Lessons: Lessons,
			QuestionChoices: QuestionChoices,
			Questions: Questions,
			QuestionTypes: QuestionTypes,
			Sections: Sections,
		};

} );
