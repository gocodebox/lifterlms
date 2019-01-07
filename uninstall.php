<?php
/**
 * LifterLMS Uninstall
 * @since    1.0.0
 * @version  3.14.9
 */

// If uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

wp_clear_scheduled_hook( 'llms_check_for_expired_memberships' );
wp_clear_scheduled_hook( 'lifterlms_cleanup_sessions' );
wp_clear_scheduled_hook( 'llms_send_tracking_data' );
wp_clear_scheduled_hook( 'lifterlms_engagement_award_achievement' );
wp_clear_scheduled_hook( 'lifterlms_engagement_award_certificate' );
wp_clear_scheduled_hook( 'lifterlms_engagement_send_email' );

/**
 * Only actually delete LifterLMS and Related Data when constant is defined
 * This will prevent data loss when a plugin is deactivated
 */
if ( defined( 'LLMS_REMOVE_ALL_DATA' ) && true === LLMS_REMOVE_ALL_DATA ) {

	include_once( dirname( __FILE__ ) . '/includes/class.llms.roles.php' );
	include_once( dirname( __FILE__ ) . '/includes/class.llms.post-types.php' );

	global $wpdb, $wp_version;

	// delete posts
	wp_trash_post( get_option( 'lifterlms_shop_page_id' ) );
	wp_trash_post( get_option( 'lifterlms_memberships_page_id' ) );
	wp_trash_post( get_option( 'lifterlms_checkout_page_id' ) );
	wp_trash_post( get_option( 'lifterlms_myaccount_page_id' ) );

	// remove roles
	LLMS_Roles::remove_roles();

	// delete options
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'lifterlms\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'llms\_%';" );

	// delete custom usermeta
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'llms\_%';" );

	// drop tables
 	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lifterlms_user_postmeta" );
 	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lifterlms_product_to_voucher" );
 	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lifterlms_voucher_code_redemptions" );
 	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lifterlms_vouchers_codes" );
 	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lifterlms_notifications" );

 	// delete all post types & related meta data
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'course', 'section', 'lesson', 'llms_quiz', 'llms_question', 'llms_membership', 'llms_engagement', 'llms_order', 'llms_transaction', 'llms_achievement', 'llms_certificate', 'llms_my_certificate', 'llms_email', 'llms_coupon', 'llms_voucher', 'llms_review', 'llms_access_plan' );" );
	$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

	// Delete terms if > WP 4.2 (term splitting was added in 4.2)
	if ( version_compare( $wp_version, '4.2', '>=' ) ) {

		// Delete term taxonomies
		foreach ( array( 'course_cat', 'course_difficulty', 'course_tag', 'course_track', 'membership_cat', 'membership_tag', 'llms_product_visibility', 'llms_access_plan_visibility' ) as $taxonomy ) {
			$wpdb->delete(
				$wpdb->term_taxonomy,
				array(
					'taxonomy' => $taxonomy,
				)
			);
		}

		// Delete orphan relationships
		$wpdb->query( "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} posts ON posts.ID = tr.object_id WHERE posts.ID IS NULL;" );

		// Delete orphan terms
		$wpdb->query( "DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.term_id IS NULL;" );

		// Delete orphan term meta
		if ( ! empty( $wpdb->termmeta ) ) {
			$wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id WHERE tt.term_id IS NULL;" );
		}

	}

}
