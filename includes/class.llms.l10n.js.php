<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Localize JS strings
 * This file should not be edited directly
 * It is compiled automatically via the gulp task `js:pot`
 * See the lifterlms-lib-tasks package for more information
 * @since    3.17.8
 * @version  [version]
 */
class LLMS_L10n_JS {

	/**
	 * Constructor
	 * @since    3.17.8
	 * @version  3.17.8
	 */
	public function __construct() {
		add_filter( 'lifterlms_js_l10n', array( $this, 'get_strings' ) );
	}

	/**
	 * Get strings to be passed to LifterLMS l10n class
	 * @param    array  $strings  existing strings from core / 3rd parties
	 * @return   array
	 * @since    3.17.8
	 * @version  [version]
	 */
	public function get_strings( $strings ) {

		return array_merge( $strings, array(

			/**
			 * file: assets/js/app/llms-l10n.js
			 * @since    2.7.3
			 * @version  3.16.0
			 */
			'This is a %2$s %1$s String' => esc_html__( 'This is a %2$s %1$s String', 'lifterlms' ),

			/**
			 * file: assets/js/app/llms-lesson-preview.js
			 * @since    3.0.0
			 * @version  3.16.12
			 */
			'You do not have permission to access this content' => esc_html__( 'You do not have permission to access this content', 'lifterlms' ),

			/**
			 * file: assets/js/app/llms-password-strength.js
			 * @since    3.0.0
			 * @version  3.7.0
			 */
			'There is an issue with your chosen password.' => esc_html__( 'There is an issue with your chosen password.', 'lifterlms' ),
			'Too Short' => esc_html__( 'Too Short', 'lifterlms' ),
			'Very Weak' => esc_html__( 'Very Weak', 'lifterlms' ),
			'Weak' => esc_html__( 'Weak', 'lifterlms' ),
			'Medium' => esc_html__( 'Medium', 'lifterlms' ),
			'Strong' => esc_html__( 'Strong', 'lifterlms' ),
			'Mismatch' => esc_html__( 'Mismatch', 'lifterlms' ),

			/**
			 * file: assets/js/app/llms-pricing-tables.js
			 * @since    3.0.0
			 * @version  3.9.1
			 */
			'Members Only Pricing' => esc_html__( 'Members Only Pricing', 'lifterlms' ),

			/**
			 * file: assets/js/app/llms-student-dashboard.js
			 * @since    3.7.0
			 * @version  3.10.0
			 */
			'Are you sure you want to cancel your subscription?' => esc_html__( 'Are you sure you want to cancel your subscription?', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Models/Lesson.js
			 * @since    3.13.0
			 * @version  3.17.1
			 */
			'New Lesson' => esc_html__( 'New Lesson', 'lifterlms' ),
			'lessons' => esc_html__( 'lessons', 'lifterlms' ),
			'lesson' => esc_html__( 'lesson', 'lifterlms' ),
			'Section %1$d: %2$s' => esc_html__( 'Section %1$d: %2$s', 'lifterlms' ),
			'Lesson %1$d: %2$s' => esc_html__( 'Lesson %1$d: %2$s', 'lifterlms' ),
			'%1$s Quiz' => esc_html__( '%1$s Quiz', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Models/Quiz.js
			 * @since    3.16.0
			 * @version  3.17.6
			 */
			'New Quiz' => esc_html__( 'New Quiz', 'lifterlms' ),
			'quizzes' => esc_html__( 'quizzes', 'lifterlms' ),
			'quiz' => esc_html__( 'quiz', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Models/Section.js
			 * @since    3.16.0
			 * @version  3.16.12
			 */
			'New Section' => esc_html__( 'New Section', 'lifterlms' ),
			'sections' => esc_html__( 'sections', 'lifterlms' ),
			'section' => esc_html__( 'section', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Schemas/Lesson.js
			 * @since    3.17.0
			 * @version  3.17.1
			 */
			'General Settings' => esc_html__( 'General Settings', 'lifterlms' ),
			'Video Embed URL' => esc_html__( 'Video Embed URL', 'lifterlms' ),
			'Audio Embed URL' => esc_html__( 'Audio Embed URL', 'lifterlms' ),
			'Free Lesson' => esc_html__( 'Free Lesson', 'lifterlms' ),
			'Require Passing Grade on Quiz' => esc_html__( 'Require Passing Grade on Quiz', 'lifterlms' ),
			'Require Passing Grade on Assignment' => esc_html__( 'Require Passing Grade on Assignment', 'lifterlms' ),
			'Prerequisite' => esc_html__( 'Prerequisite', 'lifterlms' ),
			'Drip Method' => esc_html__( 'Drip Method', 'lifterlms' ),
			'None' => esc_html__( 'None', 'lifterlms' ),
			'On a specific date' => esc_html__( 'On a specific date', 'lifterlms' ),
			'# of days after course enrollment' => esc_html__( '# of days after course enrollment', 'lifterlms' ),
			'# of days after course start date' => esc_html__( '# of days after course start date', 'lifterlms' ),
			'# of days after prerequisite lesson completion' => esc_html__( '# of days after prerequisite lesson completion', 'lifterlms' ),
			'# of days' => esc_html__( '# of days', 'lifterlms' ),
			'Date' => esc_html__( 'Date', 'lifterlms' ),
			'Time' => esc_html__( 'Time', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Schemas/Quiz.js
			 * @since    3.17.6
			 * @version  3.17.6
			 */
			'General Settings' => esc_html__( 'General Settings', 'lifterlms' ),
			'Description' => esc_html__( 'Description', 'lifterlms' ),
			'Passing Percentage' => esc_html__( 'Passing Percentage', 'lifterlms' ),
			'Minimum percentage of total points required to pass the quiz' => esc_html__( 'Minimum percentage of total points required to pass the quiz', 'lifterlms' ),
			'Limit Attempts' => esc_html__( 'Limit Attempts', 'lifterlms' ),
			'Limit the maximum number of times a student can take this quiz' => esc_html__( 'Limit the maximum number of times a student can take this quiz', 'lifterlms' ),
			'Time Limit' => esc_html__( 'Time Limit', 'lifterlms' ),
			'Enforce a maximum number of minutes a student can spend on each attempt' => esc_html__( 'Enforce a maximum number of minutes a student can spend on each attempt', 'lifterlms' ),
			'Show Correct Answers' => esc_html__( 'Show Correct Answers', 'lifterlms' ),
			'When enabled, students will be shown the correct answer to any question they answered incorrectly.' => esc_html__( 'When enabled, students will be shown the correct answer to any question they answered incorrectly.', 'lifterlms' ),
			'Randomize Question Order' => esc_html__( 'Randomize Question Order', 'lifterlms' ),
			'Display questions in a random order for each attempt. Content questions are locked into their defined positions.' => esc_html__( 'Display questions in a random order for each attempt. Content questions are locked into their defined positions.', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/_Detachable.js
			 * @since    3.16.12
			 * @version  3.16.12
			 */
			'Are you sure you want to detach this %s?' => esc_html__( 'Are you sure you want to detach this %s?', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/_Editable.js
			 * @since    3.16.0
			 * @version  [version]
			 */
			'Select an image' => esc_html__( 'Select an image', 'lifterlms' ),
			'Use this image' => esc_html__( 'Use this image', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/_Trashable.js
			 * @since    3.16.12
			 * @version  3.16.12
			 */
			'Are you sure you want to move this %s to the trash?' => esc_html__( 'Are you sure you want to move this %s to the trash?', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/Assignment.js
			 * @since    3.17.0
			 * @version  3.17.7
			 */
			'%1$s Assignment' => esc_html__( '%1$s Assignment', 'lifterlms' ),
			'Add Existing Assignment' => esc_html__( 'Add Existing Assignment', 'lifterlms' ),
			'Search for existing assignments...' => esc_html__( 'Search for existing assignments...', 'lifterlms' ),
			'Get Your Students Taking Action' => esc_html__( 'Get Your Students Taking Action', 'lifterlms' ),
			'Get Assignments Now!' => esc_html__( 'Get Assignments Now!', 'lifterlms' ),
			'Unlock LifterLMS Assignments' => esc_html__( 'Unlock LifterLMS Assignments', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/Elements.js
			 * @since    3.16.0
			 * @version  3.16.12
			 */
			'Add Existing Lesson' => esc_html__( 'Add Existing Lesson', 'lifterlms' ),
			'Search for existing lessons...' => esc_html__( 'Search for existing lessons...', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/PostSearch.js
			 * @since    3.16.0
			 * @version  3.17.0
			 */
			'Searching...' => esc_html__( 'Searching...', 'lifterlms' ),
			'Attach' => esc_html__( 'Attach', 'lifterlms' ),
			'Clone' => esc_html__( 'Clone', 'lifterlms' ),
			'ID' => esc_html__( 'ID', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/Question.js
			 * @since    3.16.0
			 * @version  3.16.0
			 */
			'Are you sure you want to delete this question?' => esc_html__( 'Are you sure you want to delete this question?', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/Quiz.js
			 * @since    3.16.0
			 * @version  3.17.7
			 */
			'Add Existing Quiz' => esc_html__( 'Add Existing Quiz', 'lifterlms' ),
			'Search for existing quizzes...' => esc_html__( 'Search for existing quizzes...', 'lifterlms' ),
			'Add a Question' => esc_html__( 'Add a Question', 'lifterlms' ),

			/**
			 * file: assets/js/builder/Views/SettingsFields.js
			 * @since    3.17.0
			 * @version  3.17.7
			 */
			'Use SoundCloud or Spotify audio URLS.' => esc_html__( 'Use SoundCloud or Spotify audio URLS.', 'lifterlms' ),
			'Permalink' => esc_html__( 'Permalink', 'lifterlms' ),
			'Use YouTube, Vimeo, or Wistia video URLS.' => esc_html__( 'Use YouTube, Vimeo, or Wistia video URLS.', 'lifterlms' ),

			/**
			 * file: assets/js/llms-admin-settings.js
			 * @since    3.7.3
			 * @version  3.17.5
			 */
			'Select an Image' => esc_html__( 'Select an Image', 'lifterlms' ),
			'Select Image' => esc_html__( 'Select Image', 'lifterlms' ),

			/**
			 * file: assets/js/llms-admin.js
			 * @since    ??
			 * @version  3.17.5
			 */
			'Select a student' => esc_html__( 'Select a student', 'lifterlms' ),

			/**
			 * file: assets/js/llms-analytics.js
			 * @since    3.0.0
			 * @version  3.17.6
			 */
			'Filter by Student(s)' => esc_html__( 'Filter by Student(s)', 'lifterlms' ),
			'Error' => esc_html__( 'Error', 'lifterlms' ),
			'Request timed out' => esc_html__( 'Request timed out', 'lifterlms' ),
			'Retry' => esc_html__( 'Retry', 'lifterlms' ),
			'Date' => esc_html__( 'Date', 'lifterlms' ),

			/**
			 * file: assets/js/llms-builder.js
			 * @since    3.16.0
			 * @version  3.16.0
			 */
			'General Settings' => esc_html__( 'General Settings', 'lifterlms' ),
			'Description' => esc_html__( 'Description', 'lifterlms' ),
			'Passing Percentage' => esc_html__( 'Passing Percentage', 'lifterlms' ),
			'Minimum percentage of total points required to pass the quiz' => esc_html__( 'Minimum percentage of total points required to pass the quiz', 'lifterlms' ),
			'Limit Attempts' => esc_html__( 'Limit Attempts', 'lifterlms' ),
			'Limit the maximum number of times a student can take this quiz' => esc_html__( 'Limit the maximum number of times a student can take this quiz', 'lifterlms' ),
			'Time Limit' => esc_html__( 'Time Limit', 'lifterlms' ),
			'Enforce a maximum number of minutes a student can spend on each attempt' => esc_html__( 'Enforce a maximum number of minutes a student can spend on each attempt', 'lifterlms' ),
			'Show Correct Answers' => esc_html__( 'Show Correct Answers', 'lifterlms' ),
			'When enabled, students will be shown the correct answer to any question they answered incorrectly.' => esc_html__( 'When enabled, students will be shown the correct answer to any question they answered incorrectly.', 'lifterlms' ),
			'Randomize Question Order' => esc_html__( 'Randomize Question Order', 'lifterlms' ),
			'Display questions in a random order for each attempt. Content questions are locked into their defined positions.' => esc_html__( 'Display questions in a random order for each attempt. Content questions are locked into their defined positions.', 'lifterlms' ),
			'New Quiz' => esc_html__( 'New Quiz', 'lifterlms' ),
			'quizzes' => esc_html__( 'quizzes', 'lifterlms' ),
			'quiz' => esc_html__( 'quiz', 'lifterlms' ),
			'Video Embed URL' => esc_html__( 'Video Embed URL', 'lifterlms' ),
			'Audio Embed URL' => esc_html__( 'Audio Embed URL', 'lifterlms' ),
			'Free Lesson' => esc_html__( 'Free Lesson', 'lifterlms' ),
			'Require Passing Grade on Quiz' => esc_html__( 'Require Passing Grade on Quiz', 'lifterlms' ),
			'Require Passing Grade on Assignment' => esc_html__( 'Require Passing Grade on Assignment', 'lifterlms' ),
			'Prerequisite' => esc_html__( 'Prerequisite', 'lifterlms' ),
			'Drip Method' => esc_html__( 'Drip Method', 'lifterlms' ),
			'None' => esc_html__( 'None', 'lifterlms' ),
			'On a specific date' => esc_html__( 'On a specific date', 'lifterlms' ),
			'# of days after course enrollment' => esc_html__( '# of days after course enrollment', 'lifterlms' ),
			'# of days after course start date' => esc_html__( '# of days after course start date', 'lifterlms' ),
			'# of days after prerequisite lesson completion' => esc_html__( '# of days after prerequisite lesson completion', 'lifterlms' ),
			'# of days' => esc_html__( '# of days', 'lifterlms' ),
			'Date' => esc_html__( 'Date', 'lifterlms' ),
			'Time' => esc_html__( 'Time', 'lifterlms' ),
			'New Lesson' => esc_html__( 'New Lesson', 'lifterlms' ),
			'lessons' => esc_html__( 'lessons', 'lifterlms' ),
			'lesson' => esc_html__( 'lesson', 'lifterlms' ),
			'Section %1$d: %2$s' => esc_html__( 'Section %1$d: %2$s', 'lifterlms' ),
			'Lesson %1$d: %2$s' => esc_html__( 'Lesson %1$d: %2$s', 'lifterlms' ),
			'%1$s Quiz' => esc_html__( '%1$s Quiz', 'lifterlms' ),
			'New Section' => esc_html__( 'New Section', 'lifterlms' ),
			'sections' => esc_html__( 'sections', 'lifterlms' ),
			'section' => esc_html__( 'section', 'lifterlms' ),
			'Are you sure you want to detach this %s?' => esc_html__( 'Are you sure you want to detach this %s?', 'lifterlms' ),
			'Select an image' => esc_html__( 'Select an image', 'lifterlms' ),
			'Use this image' => esc_html__( 'Use this image', 'lifterlms' ),
			'Are you sure you want to move this %s to the trash?' => esc_html__( 'Are you sure you want to move this %s to the trash?', 'lifterlms' ),
			'Use SoundCloud or Spotify audio URLS.' => esc_html__( 'Use SoundCloud or Spotify audio URLS.', 'lifterlms' ),
			'Permalink' => esc_html__( 'Permalink', 'lifterlms' ),
			'Use YouTube, Vimeo, or Wistia video URLS.' => esc_html__( 'Use YouTube, Vimeo, or Wistia video URLS.', 'lifterlms' ),
			'Searching...' => esc_html__( 'Searching...', 'lifterlms' ),
			'Attach' => esc_html__( 'Attach', 'lifterlms' ),
			'Clone' => esc_html__( 'Clone', 'lifterlms' ),
			'ID' => esc_html__( 'ID', 'lifterlms' ),
			'Are you sure you want to delete this question?' => esc_html__( 'Are you sure you want to delete this question?', 'lifterlms' ),
			'Add Existing Quiz' => esc_html__( 'Add Existing Quiz', 'lifterlms' ),
			'Search for existing quizzes...' => esc_html__( 'Search for existing quizzes...', 'lifterlms' ),
			'Add a Question' => esc_html__( 'Add a Question', 'lifterlms' ),
			'%1$s Assignment' => esc_html__( '%1$s Assignment', 'lifterlms' ),
			'Add Existing Assignment' => esc_html__( 'Add Existing Assignment', 'lifterlms' ),
			'Search for existing assignments...' => esc_html__( 'Search for existing assignments...', 'lifterlms' ),
			'Get Your Students Taking Action' => esc_html__( 'Get Your Students Taking Action', 'lifterlms' ),
			'Get Assignments Now!' => esc_html__( 'Get Assignments Now!', 'lifterlms' ),
			'Unlock LifterLMS Assignments' => esc_html__( 'Unlock LifterLMS Assignments', 'lifterlms' ),
			'Add Existing Lesson' => esc_html__( 'Add Existing Lesson', 'lifterlms' ),
			'Search for existing lessons...' => esc_html__( 'Search for existing lessons...', 'lifterlms' ),

			/**
			 * file: assets/js/llms-metabox-product.js
			 * @since    3.0.0
			 * @version  3.6.0
			 */
			'There was an error loading the necessary resources. Please try again.' => esc_html__( 'There was an error loading the necessary resources. Please try again.', 'lifterlms' ),

			/**
			 * file: assets/js/llms-metabox-students.js
			 * @since    3.0.0
			 * @version  3.0.0
			 */
			'Please select a student to enroll' => esc_html__( 'Please select a student to enroll', 'lifterlms' ),

			/**
			 * file: assets/js/llms-metaboxes.js
			 * @since    3.0.0
			 * @version  3.17.5
			 */
			'Are you sure you want to delete this row? This cannot be undone.' => esc_html__( 'Are you sure you want to delete this row? This cannot be undone.', 'lifterlms' ),
			'membership_bulk_enrollment_warning' => esc_html__( 'membership_bulk_enrollment_warning', 'lifterlms' ),
			'Cancel' => esc_html__( 'Cancel', 'lifterlms' ),
			'Refund' => esc_html__( 'Refund', 'lifterlms' ),
			'Record a Manual Payment' => esc_html__( 'Record a Manual Payment', 'lifterlms' ),
			'Copy this code and paste it into the desired area' => esc_html__( 'Copy this code and paste it into the desired area', 'lifterlms' ),
			'View' => esc_html__( 'View', 'lifterlms' ),

			/**
			 * file: assets/js/llms-quiz-attempt-review.js
			 * @since    3.16.0
			 * @version  3.16.9
			 */
			'Remarks to Student' => esc_html__( 'Remarks to Student', 'lifterlms' ),
			'points' => esc_html__( 'points', 'lifterlms' ),

			/**
			 * file: assets/js/llms-quiz.js
			 * @since    1.0.0
			 * @version  3.16.9
			 */
			'Are you sure you wish to quit this quiz attempt?' => esc_html__( 'Are you sure you wish to quit this quiz attempt?', 'lifterlms' ),
			'Grading Quiz...' => esc_html__( 'Grading Quiz...', 'lifterlms' ),
			'Loading Question...' => esc_html__( 'Loading Question...', 'lifterlms' ),
			'An unknown error occurred. Please try again.' => esc_html__( 'An unknown error occurred. Please try again.', 'lifterlms' ),
			'Loading Quiz...' => esc_html__( 'Loading Quiz...', 'lifterlms' ),
			'Time Remaining' => esc_html__( 'Time Remaining', 'lifterlms' ),
			'Next Question' => esc_html__( 'Next Question', 'lifterlms' ),
			'Complete Quiz' => esc_html__( 'Complete Quiz', 'lifterlms' ),
			'Previous Question' => esc_html__( 'Previous Question', 'lifterlms' ),
			'Loading...' => esc_html__( 'Loading...', 'lifterlms' ),
			'You must select an answer to continue.' => esc_html__( 'You must select an answer to continue.', 'lifterlms' ),

			/**
			 * file: assets/js/llms.js
			 * @since    3.14.0
			 * @version  3.14.0
			 */
			'This is a %2$s %1$s String' => esc_html__( 'This is a %2$s %1$s String', 'lifterlms' ),
			'You do not have permission to access this content' => esc_html__( 'You do not have permission to access this content', 'lifterlms' ),
			'There is an issue with your chosen password.' => esc_html__( 'There is an issue with your chosen password.', 'lifterlms' ),
			'Too Short' => esc_html__( 'Too Short', 'lifterlms' ),
			'Very Weak' => esc_html__( 'Very Weak', 'lifterlms' ),
			'Weak' => esc_html__( 'Weak', 'lifterlms' ),
			'Medium' => esc_html__( 'Medium', 'lifterlms' ),
			'Strong' => esc_html__( 'Strong', 'lifterlms' ),
			'Mismatch' => esc_html__( 'Mismatch', 'lifterlms' ),
			'Members Only Pricing' => esc_html__( 'Members Only Pricing', 'lifterlms' ),
			'Are you sure you want to cancel your subscription?' => esc_html__( 'Are you sure you want to cancel your subscription?', 'lifterlms' ),

			/**
			 * file: assets/js/partials/_metabox-field-repeater.js
			 * @since    3.11.0
			 * @version  3.17.3
			 */
			'Are you sure you want to delete this row? This cannot be undone.' => esc_html__( 'Are you sure you want to delete this row? This cannot be undone.', 'lifterlms' ),

			/**
			 * file: assets/js/private/llms-metaboxes.js
			 * @since    3.0.0
			 * @version  3.17.5
			 */
			'membership_bulk_enrollment_warning' => esc_html__( 'membership_bulk_enrollment_warning', 'lifterlms' ),
			'Cancel' => esc_html__( 'Cancel', 'lifterlms' ),
			'Refund' => esc_html__( 'Refund', 'lifterlms' ),
			'Record a Manual Payment' => esc_html__( 'Record a Manual Payment', 'lifterlms' ),
			'Copy this code and paste it into the desired area' => esc_html__( 'Copy this code and paste it into the desired area', 'lifterlms' ),
			'View' => esc_html__( 'View', 'lifterlms' ),

		) );

	}

}

return new LLMS_L10n_JS();
