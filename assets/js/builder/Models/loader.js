/**
 * Load all models
 * @return   obj
 * @since    [version]
 * @version  [version]
 */
define( [
		'Models/Course',
		'Models/Image',
		'Models/Lesson',
		'Models/Question',
		'Models/QuestionChoice',
		'Models/QuestionType',
		'Models/Quiz',
		'Models/Section'
	],
	function(
		Course,
		Image,
		Lesson,
		Question,
		QuestionChoice,
		QuestionType,
		Quiz,
		Section
	) {

	return {
		Course: Course,
		Image: Image,
		Lesson: Lesson,
		Question: Question,
		QuestionChoice: QuestionChoice,
		QuestionType: QuestionType,
		Quiz: Quiz,
		Section: Section,
	};

} );
