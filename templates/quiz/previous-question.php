<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $post;

$question = new LLMS_Question( $args['question_id'] );

if ( ! $question ) {

	$question = new LLMS_Question( $post->ID );

}

$quiz = LLMS()->session->get( 'llms_quiz' );

foreach ( $quiz->questions as $key => $value ) :
	if ( $value['id'] == $question->id ) :
		$previous_question_key = ( $key - 1 );
		if ( $previous_question_key >= 0 ) :
		?>
		<input id="llms_prev_question" type="submit" class="button" name="llms_prev_question" value="<?php _e( 'Previous Question', 'lifterlms' ); ?>" />
		<input type="hidden" name="action" value="llms_prev_question" />
		<?php wp_nonce_field( 'llms_prev_question' ); ?>
		<?php
		endif;
	endif;
endforeach;
?>
