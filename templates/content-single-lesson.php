<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>


	<div id="lesson-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php do_action( 'lifterlms_before_single_lesson_header' ); ?>
		<header class="llms-entry-header entry-header">
		<?php do_action( 'lifterlms_single_lesson_header' ); ?>
		</header>

		<?php llms_print_notices(); ?>

		<div class="llms-summary entry-content">

			<?php

				do_action( 'lifterlms_single_lesson_summary' );

			?>

		</div>


	</div>

