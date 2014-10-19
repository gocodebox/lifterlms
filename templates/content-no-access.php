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

<main class="content llms-content" role="main">
	<div id="course-<?php the_ID(); ?>" <?php post_class(); ?>>


		<div class="llms-summary">
		<?php llms_print_notices(); ?>
			
			<?php
				do_action('before_lifterlms_no_access_main_content');

				do_action('after_lifterlms_no_access_main_content');

				do_action( 'lifterlms_no_access_main_content' );
			?>

		</div>

	</div>
</main>

