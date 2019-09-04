/**
 * Load all models
 *
 * @return   obj
 * @since    3.16.0
 * @version  3.17.0
 */
define( [
		'Models/Abstract',
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
		Abstract,
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
			Abstract: Abstract,
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
