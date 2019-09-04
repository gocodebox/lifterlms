<?php
/**
 * Template: Single Certificate
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	llms_get_template_part( 'content', 'certificate' );

endwhile;

get_footer();
