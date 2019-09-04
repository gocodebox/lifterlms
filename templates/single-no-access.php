<?php
/**
 * Template: Single post no access
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

global $post;
get_header();

while ( have_posts() ) :

	the_post();

	llms_get_template_part( 'content', 'no-access' );

endwhile;

get_footer();
