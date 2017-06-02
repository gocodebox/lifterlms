<?php
/**
 * Single Quiz: Previous Question button
 * @since    1.0.0
 * @version  3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $post;

$question = new LLMS_Question( $args['question_id'] );

if ( ! $question ) {

	$question = new LLMS_Question( $post->ID );

}

$student = llms_get_student( null );
$attempt = $student->quizzes()->get_current_attempt( $args['quiz_id'] );
$questions = $attempt->get( 'questions' );

foreach ( $questions as $key => $value ) :
	if ( $value['id'] == $question->id ) :
		$previous_question_key = ( $key - 1 );
		if ( $previous_question_key >= 0 ) :
		?>
		<input id="llms_prev_question" type="submit" class="button llms-button-secondary" name="llms_prev_question" value="<?php _e( 'Previous Question', 'lifterlms' ); ?>" />
		<input type="hidden" name="action" value="llms_prev_question" />
		<?php wp_nonce_field( 'llms_prev_question' ); ?>
		<?php
		endif;
	endif;
endforeach;
