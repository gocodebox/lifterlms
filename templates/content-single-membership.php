<?php
/**
 * The Template for displaying all single membership.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

	<div id="membership-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php do_action( 'lifterlms_before_single_membership_header' ); ?>
		<header class="llms-entry-header entry-header">
		<?php do_action( 'lifterlms_single_membership_header' ); ?>
		</header>


		<div class="llms-summary entry-content">

			<?php

				do_action( 'lifterlms_single_membership_summary' );

			?>

		</div>

	</div>


