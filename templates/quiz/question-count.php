<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $question;
$quiz = LLMS()->session->get( 'llms_quiz' );

$question_count = count( $quiz->questions );

foreach ( $quiz->questions as $key => $value ) {
	if ( $value['id'] == $question->id ) {
		$current_question = ( $key + 1 );
	}
}

printf( __( 'Question %d of %d', 'lifterlms' ), $current_question, $question_count );
?>





