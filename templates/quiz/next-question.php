<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $question;

$options = $question->get_options();
$quiz = LLMS()->session->get( 'llms_quiz' );

foreach ( $quiz->questions as $key => $value ) :
	if ( $value['id'] == $question->id ) :
		$next_question_key = ( $key + 1 );
		if ( $next_question_key > ( count($quiz->questions) - 1 ) ) :
			$btn_text = 'Complete Quiz';
		else:
			$btn_text = 'Next Question';
		endif;
	endif;
endforeach;
?>

		<input id="llms_answer_question" type="submit" class="button" name="llms_answer_question" value="<?php printf( __( '%s', 'lifterlms' ), $btn_text ); ?>" />
		<input type="hidden" name="action" value="llms_answer_question" />
		<?php wp_nonce_field( 'llms_answer_question' ); ?>
		



