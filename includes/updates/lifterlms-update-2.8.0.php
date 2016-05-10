<?php
/**
 * Update LifterLMS Database to 2.8.0
 *
 * @author   LifterLMS
 * @category Admin
 * @package  LifterLMS/Admin/Updates
 * @version  2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;

$r = 'success';

/**
 * Delete legacy options related to LifterLMS updating
 * prior to 2.0 release. this is long overdue
 */
delete_option( 'lifterlms_is_activated' );
delete_option( 'lifterlms_update_key' );
delete_option( 'lifterlms_authkey' );
delete_option( 'lifterlms_activation_key' );

/**
 * Update postmeta data for LifterLMS Orders
 * Normalize all metadata associated with an order to have "_llms_" as a prefix
 * before this update, some keys have "_llms_" while others have "_llms_order"
 */
$order_meta_keys = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = REPLACE( m.meta_key, '_llms_order_', '_llms_' )
 	 WHERE p.post_type = 'order' AND m.meta_key LIKE '_llms_order_%';"
);

if ( false === $order_meta_keys ) {
	return false;
}

/**
 * Update postmeta keys for billing frequency
 * change "billing_freq" to "billing_frequency"
 * @var [type]
 */
$normalize_billing_frequency = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = '_llms_billing_frequency'
 	 WHERE p.post_type = 'order' AND m.meta_key = '_llms_billing_freq';"
);

if ( false === $normalize_billing_frequency ) {
	return false;
}

return $r;
