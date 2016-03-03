<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box Product info.
*
* Fields for managing the Product as a sellable product.
*/
class LLMS_Meta_Box_Product {

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {

		global $wpdb;
		do_action( 'lifterlms_before_save_product_meta_box', $post_id, $post );

		// Update post meta
		if ( isset( $_POST['_regular_price'] ) ) {

			/**
			 * @todo  deprecate '_price' b/c all this logic should be handled from the Product Class
			 */
			update_post_meta( $post_id, '_regular_price', ( $_POST['_regular_price'] === '' ) ? '' : llms_format_decimal( $_POST['_regular_price'] ) );
			update_post_meta( $post_id, '_price', ( $_POST['_regular_price'] === '' ) ? '' : llms_format_decimal( $_POST['_regular_price'] ) );

		}

		if ( isset( $_POST['_sale_price'] ) ) {

			update_post_meta( $post_id, '_sale_price', ( $_POST['_sale_price'] === '' ? '' : llms_format_decimal( $_POST['_sale_price'] ) ) );

		}

		//Update Sales Price Dates
		$date_from = isset( $_POST['_sale_price_dates_from'] ) && $_POST['_sale_price_dates_from'] ? LLMS_Date::db_date( $_POST['_sale_price_dates_from'] ) : '';
		$date_to = isset( $_POST['_sale_price_dates_to'] ) && $_POST['_sale_price_dates_to'] ? LLMS_Date::db_date( $_POST['_sale_price_dates_to'] ) : '';

		// Dates
		update_post_meta( $post_id, '_sale_price_dates_from', $date_from );
		update_post_meta( $post_id, '_sale_price_dates_to', $date_to );

		if ( $date_to && ! $date_from ) {
			update_post_meta( $post_id, '_sale_price_dates_from', LLMS_Date::db_date( strtotime( 'NOW', current_time( 'timestamp' ) ) ) );
			$date_from = LLMS_Date::db_date( strtotime( 'NOW', current_time( 'timestamp' ) ) );
		}

		// can't be on sale without a sale price
		if ( isset( $_POST['_on_sale'] ) && ! empty( $_POST['_sale_price'] ) ) {

			$on_sale = llms_clean( $_POST['_on_sale'] );
			update_post_meta( $post_id, '_on_sale', $on_sale );

		} else {

			update_post_meta( $post_id, '_on_sale', '' );

		}

		//Update Recurring Payments
		if ( isset( $_POST['_llms_recurring_enabled'] )
			&& ! empty( $_POST['_llms_subscription_price'] )
			&& isset( $_POST['_llms_billing_period'] )
			&& ! empty( $_POST['_llms_billing_freq'] )) {

			$recurring_enabled 			= llms_clean( $_POST['_llms_recurring_enabled'] );
			$subscription_price 		= llms_clean( $_POST['_llms_subscription_price'] );
			$subscription_first_payment = ( ! $_POST['_llms_subscription_first_payment'] == '' ? llms_clean( $_POST['_llms_subscription_first_payment'] ) : '0' );
			$billing_period 			= llms_clean( $_POST['_llms_billing_period'] );
			$billing_freq 				= llms_clean( $_POST['_llms_billing_freq'] );
			$billing_cycle				= llms_clean( $_POST['_llms_billing_cycle'] );

			update_post_meta( $post_id, '_llms_recurring_enabled', $recurring_enabled );
			update_post_meta( $post_id, '_llms_subscription_price', llms_format_decimal( $subscription_price ) );
			update_post_meta( $post_id, '_llms_subscription_first_payment', llms_format_decimal( $subscription_first_payment ) );
			update_post_meta( $post_id, '_llms_billing_period', $billing_period );
			update_post_meta( $post_id, '_llms_billing_freq', $billing_freq );
			update_post_meta( $post_id, '_llms_billing_cycle', $billing_cycle );

			$llms_subs = array();
			$llms_sub = array();
			$llms_sub['billing_cycle'] = $billing_cycle;
			$llms_sub['billing_freq'] = $billing_freq;
			$llms_sub['billing_period'] = $billing_period;
			$llms_sub['total_price'] = ( ( $subscription_price * $billing_cycle ) + $subscription_first_payment );
			$llms_sub['sub_price'] = $subscription_price;
			$llms_sub['first_payment'] = $subscription_first_payment;

			$llms_subs[] = $llms_sub;
			update_post_meta( $post_id, '_llms_subscriptions', $llms_subs );

		} else {
			update_post_meta( $post_id, '_llms_recurring_enabled', '' );
			update_post_meta( $post_id, '_llms_subscription_price', '' );
			update_post_meta( $post_id, '_llms_subscription_first_payment', '' );
			update_post_meta( $post_id, '_llms_billing_period', '' );
			update_post_meta( $post_id, '_llms_billing_freq', '' );
			update_post_meta( $post_id, '_llms_billing_cycle', '' );

			$llms_subs = array();
			update_post_meta( $post_id, '_llms_subscriptions', $llms_subs );
		}

		// Unique SKU
		if ( isset( $_POST['_sku'] ) ) {
			$sku = get_post_meta( $post_id, '_sku', true );
			$new_sku = llms_clean( stripslashes( $_POST['_sku'] ) );

			if ( $new_sku == '' ) {
				update_post_meta( $post_id, '_sku', '' );
			} elseif ( $new_sku !== $sku ) {
				if ( ! empty( $new_sku ) ) {
					if (
						$wpdb->get_var( $wpdb->prepare("
							SELECT $wpdb->posts.ID
						    FROM $wpdb->posts
						    LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
						    WHERE ($wpdb->posts.post_type = 'course'
						    OR $wpdb->posts.post_type = 'llms_membership')
						    AND $wpdb->posts.post_status = 'publish'
						    AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
						 ", $new_sku ) )
						) {

						LLMS_Admin_Meta_Boxes::get_error( __( 'The SKU used already exists. Please create a unique SKU.', 'lifterlms' ) );

					} else {
						update_post_meta( $post_id, '_sku', $new_sku );
					}
				} else {
					update_post_meta( $post_id, '_sku', '' );
				}
			}
		}

		// custom text for price checkbox
		if (isset( $_POST['_is_custom_single_price'] ) &&
			(isset( $_POST['_custom_single_price_html'] ) && strlen( trim( $_POST['_custom_single_price_html'] ) ) > 0)) {
			$is_custom_single_price = llms_clean( $_POST['_is_custom_single_price'] );
			$custom_single_price = llms_clean( $_POST['_custom_single_price_html'] );
			update_post_meta( $post_id, '_is_custom_single_price', $is_custom_single_price );
			update_post_meta( $post_id, '_custom_single_price_html', $custom_single_price );
		} else {
			update_post_meta( $post_id, '_is_custom_single_price', '' );
			update_post_meta( $post_id, '_custom_single_price_html', '' );
		}

		do_action( 'lifterlms_after_save_product_meta_box', $post_id, $post );
	}
}
