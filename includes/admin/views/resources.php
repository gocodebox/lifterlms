<?php
/**
 * Resources Page HTML.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 7.4.1
 * @version 7.4.1
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wrap lifterlms lifterlms-settings llms-resources">

	<div class="llms-subheader">

		<h1><?php esc_html_e( 'LifterLMS Resources', 'lifterlms' ); ?></h1>

	</div>

	<div class="llms-inside-wrap">

		<hr class="wp-header-end">

		<form id="llms-resources-form" method="post" action="admin-post.php">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">

					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( 'toplevel_page_llms-resources', 'side', '' ); ?>
					</div>

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( 'toplevel_page_llms-resources', 'normal', '' ); ?>
					</div>

					<br class="clear">

				</div> <!-- end metabox-holder -->

				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

			</div> <!-- end poststuff -->
		</form>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('toplevel_page_llms-resources');
			});
			//]]>
		</script>

	</div>

</div> <!-- end .wrap.llms-resources -->
