<?php
/**
 * Dashboard Page HTML.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 7.1.0
 * @since 7.3.0 Leverage new `LLMS_Admin_Dashboard_Widget::get_dashboard_widget_data()` method.
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wrap lifterlms lifterlms-settings llms-dashboard">

	<div class="llms-subheader">

		<h1><?php esc_html_e( 'LifterLMS Dashboard', 'lifterlms' ); ?></h1>

	</div>

	<div class="llms-inside-wrap">

		<hr class="wp-header-end">

		<div class="llms-dashboard-activity">
			<h2><?php printf( esc_html__( 'Recent Activity: %1$1s to %2$2s', 'lifterlms' ), esc_html( date( get_option( 'date_format' ), current_time( 'timestamp' ) - WEEK_IN_SECONDS ) ), esc_html( date( get_option( 'date_format' ), current_time( 'timestamp' ) ) ) ); ?></h2>
			<?php echo '<style type="text/css">#llms-charts-wrapper{display:none;}</style>'; ?>
			<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in template.
				echo llms_get_template(
					'admin/reporting/tabs/widgets.php',
					array(
						'json'        => wp_json_encode(
							array(
								'current_tab'         => 'settings',
								'current_range'       => 'last-7-days',
								'current_students'    => array(),
								'current_courses'     => array(),
								'current_memberships' => array(),
								'dates'               => array(
									'start' => date( 'Y-m-d', current_time( 'timestamp' ) - WEEK_IN_SECONDS ),
									'end'   => current_time( 'Y-m-d' ),
								),
							)
						),
						'widget_data' => array( LLMS_Admin_Dashboard_Widget::get_dashboard_widget_data() ),
					)
				);
				?>
		</div> <!-- end llms-dashboard-activity -->

		<form id="llms-dashboard-form" method="post" action="admin-post.php">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">

					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( 'toplevel_page_llms-dashboard', 'side', '' ); ?>
					</div>

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( 'toplevel_page_llms-dashboard', 'normal', '' ); ?>
					</div>

					<br class="clear">

				</div> <!-- end dashboard-widgets -->

				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

			</div> <!-- end dashboard-widgets-wrap -->
		</form>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('toplevel_page_llms-dashboard');
			});
			//]]>
		</script>

	</div>

</div> <!-- end .wrap.llms-dashboard -->
