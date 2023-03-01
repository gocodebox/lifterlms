<?php
/**
 * Dashboard Page HTML
 *
 * @package LifterLMS/Admin/Views
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add all the meta boxes for the dashboard.
 */
add_meta_box(
	'llms_dashboard_quick_links',
	__( 'Quick Links', 'lifterlms' ),
	'llms_dashboard_quick_links_callback',
	'toplevel_page_llms-dashboard',
	'normal'
);
add_meta_box(
	'llms_dashboard_addons',
	__( 'Most Popular Add-ons, Courses, and Resources', 'lifterlms' ),
	'llms_dashboard_addons_callback',
	'toplevel_page_llms-dashboard',
	'normal'
);
add_meta_box(
	'llms_dashboard_blog',
	__( 'LifterLMS Blog', 'lifterlms' ),
	'llms_dashboard_blog_callback',
	'toplevel_page_llms-dashboard',
	'side'
);
add_meta_box(
	'llms_dashboard_podcast',
	__( 'LifterLMS Podcast', 'lifterlms' ),
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

<?php
/**
 * Callback function for llms_dashboard_welcome meta box.
 *
 * @since [version]
 *
 * @return void
 */
function llms_dashboard_addons_callback() {
	/**
	 * Get advert banner HTML.
	 */
	$view = new LLMS_Admin_AddOns();

	echo $view->output_for_settings();
	echo '<p><a class="llms-button-primary" href="' . esc_url( admin_url( 'admin.php?page=llms-add-ons' ) ) . '">' . __( 'View Add-ons & more', 'lifterlms' ) . '</a></p><br />';
}

/**
 * Callback function for llms_dashboard_quick_links meta box.
 *
 * @since [version]
 *
 * @return void
 */
function llms_dashboard_quick_links_callback() {
	?>
	<div class="llms-quick-links">
		<div class="llms-list">
			<h3><?php esc_html_e( 'Build LMS Content', 'lifterlms' ); ?></h3>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=llms_membership' ) ); ?>"><?php esc_html_e( 'Add a New Membership', 'lifterlms' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=llms_engagement' ) ); ?>"><?php esc_html_e( 'Create an Engagement', 'lifterlms' ); ?></a></li>
			</ul>
			<a class="llms-button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=course' ) ); ?>"><i class="fa fa-graduation-cap" aria-hidden="true"></i>&nbsp;&nbsp;<?php esc_html_e( 'Create a New Course', 'lifterlms' ); ?></a>
		</div>
		<div class="llms-list">
			<h3><?php esc_html_e( 'Access Reports', 'lifterlms' ); ?></h3>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=llms_order' ) ); ?>"><?php esc_html_e( 'View Orders', 'lifterlms' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=students' ) ); ?>"><?php esc_html_e( 'View Students', 'lifterlms' ); ?></a></li>
			</ul>
			<a class="llms-button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=sales' ) ); ?>"><i class="fa fa-line-chart" aria-hidden="true"></i>&nbsp;&nbsp;<?php esc_html_e( 'View Sales Report', 'lifterlms' ); ?></a>
		</div>
		<div class="llms-list">
			<h3><?php esc_html_e( 'Your Launch Checklist', 'lifterlms' ); ?></h3>
			<?php
				// Count access plans across the whole LMS.
				$ap_check = false;
				$ap_query = new WP_Query(
					array(
						'post_type'              => 'llms_access_plan',
						'posts_per_page'         => 1,
						'post_status'            => 'any', // Retrieves any status except for 'inherit', 'trash' and 'auto-draft'.
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
					)
				);
				// If more than 1 access plan, they are "set up".
			if ( $ap_query->post_count >= 1 ) {
				$ap_check = true;
			}

				// Count enrollments across the whole LMS.
				global $wpdb;
				$enrollments_check = false;
				$enrollments       = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_key = '_status' AND meta_value = 'enrolled'" ); // no-cache ok.
				// If more than 10 enrollments, they are "set up".
			if ( $enrollments >= 10 ) {
				$enrollments_check = true;
			}
			?>
			<ul class="llms-checklist">
				<li>
					<?php if ( $ap_check ) { ?>
						<i class="fa fa-check"></i> <?php esc_html_e( 'Create Access Plan', 'lifterlms' ); ?>
					<?php } else { ?>
						<i class="fa fa-times"></i> <a href="https://lifterlms.com/docs/what-is-an-access-plan/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=Create%20Access%20Plan" target="_blank"><?php esc_html_e( 'Create Access Plan', 'lifterlms' ); ?></a>
					<?php } ?>
				</li>
				<li>
					<?php if ( $enrollments_check ) { ?>
						<i class="fa fa-check"></i> <?php esc_html_e( 'Get 10 Enrollments', 'lifterlms' ); ?>
					<?php } else { ?>
						<i class="fa fa-times"></i> <a href="https://academy.lifterlms.com/course/enroll/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=Get%2010%20Enrollments" target="_blank"><?php esc_html_e( 'Get 10 Enrollments', 'lifterlms' ); ?></a>
					<?php } ?>
				</li>
			</ul>
			<a class="llms-button-action" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons' ) ); ?>"><i class="fa fa-plug" aria-hidden="true"></i> <?php esc_html_e( 'Add Advanced Features', 'lifterlms' ); ?></a>
		</div>
	</div>
	<hr />
	<div class="llms-help-links">
		<div class="llms-list">
			<h3><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e( 'Sales', 'lifterlms' ); ?></h3>
			<ul>
				<li><a href="https://lifterlms.com/pricing/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Pricing" target="_blank"><?php esc_html_e( 'Pricing', 'lifterlms' ); ?></a></li>
				<li><a href="https://lifterlms.com/store/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Add-ons" target="_blank"><?php esc_html_e( 'Add-Ons', 'lifterlms' ); ?></a></li>
				<li><a href="https://lifterlms.com/presales-contact/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Presales%20Contact" target="_blank"><?php esc_html_e( 'Contact Sales', 'lifterlms' ); ?></a></li>
			</ul>
		</div>
		<div class="llms-list">
			<h3><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Support', 'lifterlms' ); ?></h3>
			<ul>
				<li><a href="https://lifterlms.com/docs/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Documentation" target="_blank"><?php esc_html_e( 'Documentation', 'lifterlms' ); ?></a></li>
				<li><a href="https://wordpress.org/support/plugin/lifterlms/" target="_blank"><?php esc_html_e( 'WordPress.org Support', 'lifterlms' ); ?></a></li>
				<li><a href="https://lifterlms.com/my-account/my-tickets/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Support" target="_blank"><?php esc_html_e( 'Premium Support', 'lifterlms' ); ?></a></li>
			</ul>
		</div>
		<div class="llms-list">
			<h3><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e( 'Learn', 'lifterlms' ); ?></h3>
			<ul>
				<li><a href="https://academy.lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Academy" target="_blank"><?php esc_html_e( 'Academy', 'lifterlms' ); ?></a></li>
				<li><a href="https://lifterlms.com/community-events/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Events" target="_blank"><?php esc_html_e( 'Events', 'lifterlms' ); ?></a></li>
				<li><a href="https://developer.lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Developers" target="_blank"><?php esc_html_e( 'Developers', 'lifterlms' ); ?></a></li>
			</ul>
		</div>
		<div class="llms-list">
			<h3><span class="dashicons dashicons-admin-site"></span> <?php esc_html_e( 'Content', 'lifterlms' ); ?></h3>
			<ul>
				<li><a href="https://lifterlms.com/blog/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Blog" target="_blank"><?php esc_html_e( 'Blog', 'lifterlms' ); ?></a></li>
				<li><a href="https://podcast.lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=Podcast" target="_blank"><?php esc_html_e( 'Podcast', 'lifterlms' ); ?></a></li>
				<li><a href="https://www.youtube.com/lifterlms" target="_blank"><?php esc_html_e( 'YouTube', 'lifterlms' ); ?></a></li>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * Callback function for llms_dashboard_blog meta box.
 *
 * @since [version]
 *
 * @return void
 */
function llms_dashboard_blog_callback() {

	// Get RSS Feed(s).
	include_once ABSPATH . WPINC . '/feed.php';

	// Get a SimplePie feed object from the specified feed source.
	$rss = fetch_feed( 'https://lifterlms.com/feed' );

	$maxitems = 0;

	if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly.

		// Figure out how many total items there are, but limit it to 3.
		$maxitems = $rss->get_item_quantity( 3 );

		// Build an array of all the items, starting with element 0 (first element).
		$rss_items = $rss->get_items( 0, $maxitems );

	endif;

	?>

	<ul>
		<?php if ( 0 === $maxitems ) : ?>
			<li><?php esc_html_e( 'No news found.', 'lifterlms' ); ?></li>
		<?php else : ?>
			<?php // Loop through each feed item and display each item as a hyperlink. ?>
			<?php foreach ( $rss_items as $item ) : ?>
				<li>
					<a href="<?php echo esc_url( $item->get_permalink() ); ?>"
						title="<?php printf( __( 'Posted %s', 'lifterlms' ), date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ) ); ?>">
						<?php echo esc_html( $item->get_title() ); ?>
					</a>
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ) ); ?>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
	<p><a class="llms-button-secondary small" href="https://lifterlms.com/blog/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Blog"><?php esc_html_e( 'View More', 'lifterlms' ); ?></a></p>
	<?php
}

/**
 * Callback function for llms_dashboard_podcast meta box.
 *
 * @since [version]
 *
 * @return void
 */
function llms_dashboard_podcast_callback() {

	// Get RSS Feed(s).
	include_once ABSPATH . WPINC . '/feed.php';

	// Get a SimplePie feed object from the specified feed source.
	$rss = fetch_feed( 'https://podcast.lifterlms.com/feed/' );

	$maxitems = 0;

	if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly.

		// Figure out how many total items there are, but limit it to 3.
		$maxitems = $rss->get_item_quantity( 3 );

		// Build an array of all the items, starting with element 0 (first element).
		$rss_items = $rss->get_items( 0, $maxitems );

	endif;

	?>

	<ul>
		<?php if ( 0 === $maxitems ) : ?>
			<li><?php esc_html_e( 'No news found.', 'lifterlms' ); ?></li>
		<?php else : ?>
			<?php // Loop through each feed item and display each item as a hyperlink. ?>
			<?php foreach ( $rss_items as $item ) : ?>
				<li>
					<a href="<?php echo esc_url( $item->get_permalink() ); ?>"
						title="<?php printf( __( 'Posted %s', 'lifterlms' ), date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ) ); ?>">
						<?php echo esc_html( $item->get_title() ); ?>
					</a>
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ) ); ?>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
	<p><a class="llms-button-secondary small" href="https://lifterlms.com/blog/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Podcast"><?php esc_html_e( 'View More', 'lifterlms' ); ?></a></p>
	<?php
}
