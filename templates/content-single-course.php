<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

do_action( 'lifterlms_before_main_content' );
?>

	<div id="course-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php llms_print_notices(); ?>
		<div class="llms-summary entry-content">


			<?php

				do_action( 'lifterlms_single_course_summary' );

			?>

		</div>

	</div>
<?php do_action( 'lifterlms_after_main_content' ); ?>

