<?php
/**
 * The Template for displaying the quiz.
 * @since  1.0.0
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices();
$loop = new WP_Query( array(
	'post_type' => 'llms_question',
	'p' => $args['question_id'],
	'posts_per_page' => 1,
) );

if ( ! $loop->have_posts() ) {

	_e( 'No question found.', 'lifterlms' );

} else {

	while ( $loop->have_posts() ) { $loop->the_post();

		do_action( 'lifterlms_single_question_before_summary', $args );

		the_content();

		do_action( 'lifterlms_single_question_after_summary', $args );

	}
}

wp_reset_postdata();
