<?php
/**
 * Update LifterLMS Database to 3.0.0
 *
 * @author   LifterLMS
 * @category Admin
 * @package  LifterLMS/Admin/Updates
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;

$r = 'success';

/**
 * Create Access Plans for courses
 */
// $courses = new WP_Query( array(
// 	'post_type' => 'course',
// 	''
// ) );







return $r;



















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
 * Update postmeta keys for billing frequency for lifterlms orders
 * change "billing_freq" to "billing_frequency"
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

/**
 * Update postmeta keys for payment gateway for lifterlms orders
 * change "payment_method" to be "payment_gateway"
 */
$payment_gateway = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = '_llms_payment_gateway'
 	 WHERE p.post_type = 'order' AND m.meta_key = '_llms_payment_method';"
);

if ( false === $payment_gateway ) {
	return false;
}

/**
 * Rename all "order" post types to "llms_order" to prevent any future compatibility issues
 */
$update_order_post_type_name = $wpdb->query(
	"UPDATE {$wpdb->posts}
	 SET post_type = 'llms_order'
	 WHERE post_type = 'order';"
);
if ( false === $update_order_post_type_name ) {
	return false;
}

/**
 * Update "first_payment" meta keys to be "first_payment_total" for consistency
 */
$first_payment = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = '_llms_first_payment_total'
 	 WHERE p.post_type = 'llms_order' AND m.meta_key = '_llms_first_payment';"
);

if ( false === $first_payment ) {
	return false;
}

/**
 * Update paypal recurring profile ids to the generic "subscription_id"
 */
$paypal_sub_id = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = '_llms_subscription_id'
 	 WHERE p.post_type = 'llms_order' AND m.meta_key = '_llms_order_paypal_profile_id';"
);

if ( false === $paypal_sub_id ) {
	return false;
}



return $r;
