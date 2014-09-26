<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) : the_post();

	llms_get_template_part( 'content', 'single-course' );

endwhile;
?>

<?php

get_sidebar();
get_footer();
?>