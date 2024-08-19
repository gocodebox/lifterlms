<?php
/**
 * Single Question Template
 *
 * @since 1.0.0
 * @since 3.16.0 Unknown.
 * @since [version] Pass the `$attempt` object when retrieving the question content via `$question->get_question();`
 * @version [version]
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 * @arg  $question (obj)  LLMS_Question instance
 */

defined( 'ABSPATH' ) || exit;

/**
 * lifterlms_single_question_before_summary
 *
 * @hooked lifterlms_template_question_wrapper_start - 10
 */
do_action( 'lifterlms_single_question_before_summary', $args ); ?>

	<h3 class="llms-question-text">
	<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the question templates.
			echo $question->get_question( 'html', $attempt );
	?>
	</h3>

	<?php
		/**
		 * lifterlms_single_question_content
		 *
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
 *
 * @hooked lifterlms_template_question_wrapper_end - 10
 */
do_action( 'lifterlms_single_question_after_summary', $args );
