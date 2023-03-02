<?php
/**
 * Dashboard Page HTML.
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 7.1.0
 * @version 7.1.0
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
			<h2><?php printf( __( 'Recent Activity: %1$1s to %2$2s', 'lifterlms' ), date( get_option( 'date_format' ), current_time( 'timestamp' ) - WEEK_IN_SECONDS ), date( get_option( 'date_format' ), current_time( 'timestamp' ) ) ); ?></h2>
			<?php echo '<style type="text/css">#llms-charts-wrapper{display:none;}</style>'; ?>
			<?php
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
						'widget_data' => array(
							array(
								'enrollments'       => array(
									'title'   => __( 'Enrollments', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Number of total enrollments during the selected period', 'lifterlms' ),
									'link'    => admin_url( 'admin.php?page=llms-reporting&tab=enrollments' ),
								),
								'registrations'     => array(
									'title'   => __( 'Registrations', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Number of total user registrations during the selected period', 'lifterlms' ),
									'link'    => admin_url( 'admin.php?page=llms-reporting&tab=students' ),
								),
								'sold'              => array(
									'title'   => __( 'Net Sales', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Total of all successful transactions during this period', 'lifterlms' ),
									'link'    => admin_url( 'admin.php?page=llms-reporting&tab=sales' ),
								),
								'lessoncompletions' => array(
									'title'   => __( 'Lessons Completed', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Number of total lessons completed during the selected period', 'lifterlms' ),
									'link'    => admin_url( 'admin.php?page=llms-reporting&tab=courses' ),
								),
							),
						),
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
