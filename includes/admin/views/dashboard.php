<?php
/**
 * Dashboard Page HTML
 *
 * @since    TBD
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add all the meta boxes for the dashboard.
 */
add_meta_box(
	'llms_dashboard_addons',
	__( 'Most Popular Add-ons, Courses, and Resources', 'lifterlms' ),
	'llms_dashboard_addons_callback',
	'toplevel_page_llms-dashboard',
	'normal'
);
add_meta_box(
	'llms_dashboard_quick_links',
	__( 'Quick Links', 'lifterlms' ),
	'llms_dashboard_quick_links_callback',
	'toplevel_page_llms-dashboard',
	'normal'
);
add_meta_box(
	'llms_dashboard_blog',
	__( 'From the Blog', 'lifterlms' ),
	'llms_dashboard_blog_callback',
	'toplevel_page_llms-dashboard',
	'side'
);
add_meta_box(
	'llms_dashboard_podcast_updates',
	__( 'From the Podcast', 'lifterlms' ),
	'llms_dashboard_podcast_callback',
	'toplevel_page_llms-dashboard',
	'side'
);

?>
<div class="wrap lifterlms lifterlms-settings llms-dashboard">

	<div class="llms-subheader">

		<h1><?php esc_html_e( 'LifterLMS Dashboard', 'lifterlms' ); ?></h1>

	</div>

	<div class="llms-inside-wrap">

		<hr class="wp-header-end">

		<div class="llms-dashboard-activity">
			<h2><?php printf( __( 'Recent Activity: %1s to %2s', 'lifterlms' ), date( get_option( 'date_format' ), current_time( 'timestamp' ) - WEEK_IN_SECONDS ), date( get_option( 'date_format' ), current_time( 'timestamp' ) ) ); ?></h2>
			<?php echo '<style type="text/css">#llms-charts-wrapper{display:none;}</style>'; ?>
			<?php
				echo llms_get_template(
					'admin/reporting/tabs/widgets.php',
					array(
						'json'        => json_encode(
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
									'link'	  => admin_url( 'admin.php?page=llms-reporting&tab=enrollments' ),
								),
								'registrations'     => array(
									'title'   => __( 'Registrations', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Number of total user registrations during the selected period', 'lifterlms' ),
									'link'	  => admin_url( 'admin.php?page=llms-reporting&tab=students' ),
								),
								'sold'              => array(
									'title'   => __( 'Net Sales', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Total of all successful transactions during this period', 'lifterlms' ),
									'link'	  => admin_url( 'admin.php?page=llms-reporting&tab=sales' ),
								),
								'lessoncompletions' => array(
									'title'   => __( 'Lessons Completed', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Number of total lessons completed during the selected period', 'lifterlms' ),
									'link'	  => admin_url( 'admin.php?page=llms-reporting&tab=courses' ),
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

					<?php //do_meta_boxes( 'toplevel_page_llms-dashboard', 'normal', '' ); ?>

					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( 'toplevel_page_llms-dashboard', 'side', '' ); ?>
					</div>

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( 'toplevel_page_llms-dashboard', 'normal', '' ); ?>
					</div>

			        <br class="clear">

		    	</div> <!-- end dashboard-widgets -->

				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>

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

<?php
/**
 * Callback function for llms_dashboard_welcome meta box.
 */
function llms_dashboard_addons_callback() {
	/**
	 * Get advert banner HTML.
	 */
	$view = new LLMS_Admin_AddOns();
	$url  = esc_url( admin_url( 'admin.php?page=llms-add-ons' ) );

	echo $view->output_for_settings();
	echo '<p style="text-align:center;"><a class="llms-button-primary" href="' . $url . '">' . __( 'View Add-ons & more', 'lifterlms' ) . '</a></p><br />'; 
}

/**
 * Callback function for llms_dashboard_quick_links meta box.
 */
function llms_dashboard_quick_links_callback() { ?>
	<div class="llms-list">
		<ul>
			<li><p><?php echo sprintf( __( 'Version: %s', 'lifterlms' ), llms()->version ); ?></p></li>
			<li><p><?php echo sprintf( __( 'Need help? Get support on the %1$sforums%2$s', 'lifterlms' ), '<a href="https://wordpress.org/support/plugin/lifterlms" target="_blank">', '</a>' ); ?></p></li>
			<li><p><?php echo sprintf( __( 'Looking for a quickstart guide, shortcodes, or developer documentation? Get started at %s', 'lifterlms' ), '<a href="https://lifterlms.com/docs" target="_blank">https://lifterlms.com/docs</a>' ); ?></p></li>
			<li><p><?php echo sprintf( __( 'Get LifterLMS news, updates, and more on our %1$sblog%2$s', 'lifterlms' ), '<a href="http://blog.lifterlms.com/" target="_blank">', '</a>' ); ?></p></li>
		</ul>
	</div>
	<?php
}

/**
 * Callback function for llms_dashboard_blog meta box.
 */
function llms_dashboard_blog_callback() { ?>
	...
	<?php
}

/**
 * Callback function for llms_dashboard_podcast meta box.
 */
function llms_dashboard_podcast_callback() { ?>
	...
	<?php
}
