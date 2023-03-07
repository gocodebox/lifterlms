<?php
/**
 * Helper Page HTML.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 7.1.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wrap lifterlms lifterlms-settings llms-helper">

	<div class="llms-subheader">

		<h1><?php esc_html_e( 'LifterLMS Helper', 'lifterlms' ); ?></h1>

	</div>

	<div class="llms-inside-wrap">

		<hr class="wp-header-end">

		<form id="llms-helper-form" method="post" action="admin-post.php">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">

					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( 'toplevel_page_llms-helper', 'side', '' ); ?>
					</div>

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( 'toplevel_page_llms-helper', 'normal', '' ); ?>
					</div>

					<br class="clear">

				</div> <!-- end helper-widgets -->

				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

			</div> <!-- end helper-widgets-wrap -->
		</form>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('toplevel_page_llms-helper');
			});
			//]]>
		</script>

	</div>

</div> <!-- end .wrap.llms-helper -->
