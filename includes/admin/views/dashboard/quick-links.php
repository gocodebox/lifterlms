<?php
/**
 * Quick links meta box HTML.
 *
 * @package LifterLMS/Admin/Views/Dashboard
 *
 * @since 7.1.0
 * @since 7.3.0 Added `llms_dashboard_checklist` filter.
 * @version 7.3.0
 */

defined( 'ABSPATH' ) || exit;
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

		// Add checklist items to an array so we can filter.
		$checklist = array();
		if ( $ap_check ) {
			$checklist['access_plan'] = '<i class="fa fa-check"></i> ' . esc_html__( 'Create Access Plan', 'lifterlms' );
		} else {
			$checklist['access_plan'] = '<i class="fa fa-times"></i> <a href="https://lifterlms.com/docs/what-is-an-access-plan/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=Create%20Access%20Plan" target="_blank" rel="noopener">' . esc_html__( 'Create Access Plan', 'lifterlms' ) . '</a>';
		}
		if ( $enrollments_check ) {
			$checklist['enrollments'] = '<i class="fa fa-check"></i> ' . esc_html__( 'Get 10 Enrollments', 'lifterlms' );
		} else {
			$checklist['enrollments'] = '<i class="fa fa-times"></i> <a href="https://academy.lifterlms.com/course/enroll/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=Get%2010%20Enrollments" target="_blank" rel="noopener">' . esc_html__( 'Get 10 Enrollments', 'lifterlms' ) . '</a>';
		}

		/**
		 * Filters the dashboard quick links checklist.
		 *
		 * @since 7.3.0
		 *
		 * @param array $checklist Dashboard quick links checklist.
		 */
		$checklist = apply_filters( 'llms_dashboard_checklist', $checklist );
		?>
		<ul class="llms-checklist">
			<?php
			foreach ( $checklist as $item ) {
				echo '<li>' . wp_kses_post( $item ) . '</li>';
			}
			?>
		</ul>
		<a class="llms-button-action" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons' ) ); ?>"><i class="fa fa-plug" aria-hidden="true"></i> <?php esc_html_e( 'Add Advanced Features', 'lifterlms' ); ?></a>
	</div>
</div>
<hr />
<div class="llms-help-links">
	<div class="llms-list">
		<h3><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e( 'Sales', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://lifterlms.com/pricing/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Pricing" target="_blank" rel="noopener"><?php esc_html_e( 'Pricing', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/store/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Add-ons" target="_blank" rel="noopener"><?php esc_html_e( 'Add-Ons', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/presales-contact/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Presales%20Contact" target="_blank" rel="noopener"><?php esc_html_e( 'Contact Sales', 'lifterlms' ); ?></a></li>
		</ul>
	</div>
	<div class="llms-list">
		<h3><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Support', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://lifterlms.com/docs/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Documentation" target="_blank" rel="noopener"><?php esc_html_e( 'Documentation', 'lifterlms' ); ?></a></li>
			<li><a href="https://wordpress.org/support/plugin/lifterlms/" target="_blank" rel="noopener"><?php esc_html_e( 'WordPress.org Support', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/my-account/my-tickets/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Support" target="_blank" rel="noopener"><?php esc_html_e( 'Premium Support', 'lifterlms' ); ?></a></li>
		</ul>
	</div>
	<div class="llms-list">
		<h3><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e( 'Learn', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://academy.lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Academy" target="_blank" rel="noopener"><?php esc_html_e( 'Academy', 'lifterlms' ); ?></a></li>
			<li><a href="https://lifterlms.com/community-events/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Events" target="_blank" rel="noopener"><?php esc_html_e( 'Events', 'lifterlms' ); ?></a></li>
			<li><a href="https://developer.lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Developers" target="_blank" rel="noopener"><?php esc_html_e( 'Developers', 'lifterlms' ); ?></a></li>
		</ul>
	</div>
	<div class="llms-list">
		<h3><span class="dashicons dashicons-admin-site"></span> <?php esc_html_e( 'Content', 'lifterlms' ); ?></h3>
		<ul>
			<li><a href="https://lifterlms.com/blog/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=LifterLMS%20Blog" target="_blank" rel="noopener"><?php esc_html_e( 'Blog', 'lifterlms' ); ?></a></li>
			<li><a href="https://podcast.lifterlms.com/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Dashboard%20Screen&utm_content=Podcast" target="_blank" rel="noopener"><?php esc_html_e( 'Podcast', 'lifterlms' ); ?></a></li>
			<li><a href="https://www.youtube.com/lifterlms" target="_blank" rel="noopener"><?php esc_html_e( 'YouTube', 'lifterlms' ); ?></a></li>
		</ul>
	</div>
</div>
