<?php
/**
 * Template: Single Certificate
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	llms_get_template_part( 'content', 'certificate' );

endwhile;

get_footer();
