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


/**
 * Move coupon title (previously used for description) to the postmeta table in the new description field
 * Move old coupon code from meta table to the coupon post title *
 */
$coupon_title_metas = $wpdb->get_results(
	"SELECT * FROM {$wpdb->postmeta}
	 WHERE meta_key = '_llms_coupon_title';"
);

foreach( $coupon_title_metas as $obj ) {

	// update new description field with the title b/c the title previously acted as a description
	update_post_meta( $obj->post_id, '_llms_description', get_the_title( $obj->post_id ) );

	// update the post title to be the value of the old meta field
	wp_update_post( array(
		'ID' => $obj->post_id,
		'post_title' => $obj->meta_value,
	) );

	// clean up
	delete_post_meta( $obj->post_id, '_llms_coupon_title' );

}

return $r;
