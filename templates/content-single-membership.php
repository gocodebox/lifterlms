<?php
/**
 * The Template for displaying all single membership.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;
do_action( 'lifterlms_before_main_content' );
?>

	<div id="membership-<?php the_ID(); ?>" <?php post_class(); ?>>


		<div class="llms-summary entry-content">

			<?php

				do_action( 'lifterlms_single_membership_summary' );

			?>

		</div>

	</div>
<?php do_action( 'lifterlms_after_main_content' ); ?>

