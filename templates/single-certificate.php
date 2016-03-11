<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	LifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

while ( have_posts() ) : the_post();

	llms_get_template_part( 'content', 'certificate' );

endwhile;

get_footer();
