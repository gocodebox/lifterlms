<?php
/**
 * Template: Single post no access
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

global $post;
get_header();

while ( have_posts() ) :

	the_post();

	llms_get_template_part( 'content', 'no-access' );

endwhile;

get_footer();
