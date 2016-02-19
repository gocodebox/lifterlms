<?php
/**
 * The Template for displaying the quiz.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
llms_print_notices();

$html = '';
$query_args = array(
	'post_type' => array( 'llms_question' ),
	'orderby' => 'ASC',
	'post__in' => array( $args['question_id'] ),
);

$loop = new WP_Query( $query_args );

if ( ! $loop->have_posts() ) {
		get_template_part( 'content', 'none' );
} else {
	while ($loop->have_posts()) : $loop->the_post();
		ob_start();
			do_action( 'lifterlms_single_question_before_summary', $args );
			$html .= ob_get_contents();
		ob_clean();

		ob_start();
			the_content();
			$html .= ob_get_contents();
		ob_clean();

		ob_start();
			do_action( 'lifterlms_single_question_after_summary', $args );
			$html .= ob_get_contents();
		ob_clean();

		endwhile;
}
	echo $html;
	wp_reset_postdata();
