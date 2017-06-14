<?php
/**
 * Single Quiz: Question Count
 * @since    1.0.0
 * @version  3.9.2
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! isset( $args['attempt'] ) || ! is_a( $args['attempt'], 'LLMS_Quiz_Attempt' ) ) {
	return;
}

$order = $args['attempt']->get_question_order( $args['question_id'] );
$total = $args['attempt']->get_count( 'questions' );
?>

<div class="llms-question-count">
	<p><?php printf( __( 'Question %1$d of %2$d', 'lifterlms' ), $order, $total ); ?></p>
</div>
