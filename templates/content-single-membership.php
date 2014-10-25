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
<main class="content llms-content" role="main">
	<div id="membership-<?php the_ID(); ?>" <?php post_class(); ?>>


		<div class="llms-summary">

			<?php

				do_action( 'lifterlms_single_membership_summary' );

			?>

		</div>

	</div>
</main>

