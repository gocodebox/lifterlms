<?php
/**
 * Single Question Template
 * @since    1.0.0
 * @version 3.16.0
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 * @arg  $question (obj)  LLMS_Question instance
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * lifterlms_single_question_before_summary
 * @hooked lifterlms_template_question_wrapper_start - 10
 */
do_action( 'lifterlms_single_question_before_summary', $args ); ?>

	<h3 class="llms-question-text"><?php echo $question->get_question(); ?></h3>

	<?php
		/**
		 * lifterlms_single_question_content
		 * @hooked lifterlms_template_question_description - 10
		 * @hooked lifterlms_template_question_image - 20
		 * @hooked lifterlms_template_question_video - 30
		 * @hooked lifterlms_template_question_content - 40
		 */
		do_action( 'lifterlms_single_question_content', $args );
	?>

<?php
/**
 * lifterlms_single_question_after_summary
 * @hooked lifterlms_template_question_wrapper_end - 10
 */
do_action( 'lifterlms_single_question_after_summary', $args );
