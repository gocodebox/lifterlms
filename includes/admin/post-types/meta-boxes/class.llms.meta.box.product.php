<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
* Meta Box Product info
*
* @version  3.0.0
*/
class LLMS_Meta_Box_Product extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-product';
		$this->title = __( 'Product Options', 'lifterlms' );
		$this->screens = array(
			'course',
			'llms_membership',
		);
		$this->priority = 'high';

		// output PHP variables for JS access
		add_action( 'admin_print_footer_scripts', array( $this, 'localize_js' ) );

	}

	/**
	 * Return an empty array because the metabox fields here are completely custom
	 * @return array
	 * @since  3.0.0
	 */
	public function get_fields() {
		return array();
	}

	public function localize_js() {
		$p = new LLMS_Product( $this->post );
		$limit = $p->get_access_plan_limit();
		echo '<script>window.llms = window.llms || {}; window.llms.product = { access_plan_limit: ' . $limit . ' };</script>';
	}

	/**
	 * Filter the available buttons in the Plan Description editors
	 * @param  array  $buttons array of default butotns
	 * @param  [type] $id      [description]
	 * @return [type]          [description]
	 */
	public function mce_buttons( $buttons, $id ) {

		if ( strpos( $id, '_llms_plans_content' ) !== false ) {

			$buttons = array(
				'bold',
				'italic',
				'underline',
				'blockquote',
				'strikethrough',
				'bullist',
				'numlist',
				'alignleft',
				'aligncenter',
				'alignright',
				'undo',
				'redo',
			);

		}

		return $buttons;
	}

	/**
	 * Output metabox content
	 * Overwrites abstract because of the requirments of the UI
	 * @return void
	 * @since  3.0.0
	 */
	public function output() {

		$gateways = LLMS()->payment_gateways();
		$product = new LLMS_Product( $this->post );

		if ( $gateways->has_gateways( true ) ) {

			add_filter( 'teeny_mce_buttons', array( $this, 'mce_buttons' ), 10, 2 );

			$course = ( 'course' === $product->get( 'type' ) ) ? new LLMS_Course( $product->post ) : false;

			llms_get_template( 'admin/post-types/product.php', array(
				'course' => $course,
				'gateways' => $gateways,
				'product' => $product,
			) );

			remove_filter( 'teeny_mce_buttons', array( $this, 'mce_buttons' ), 10, 2 );

		} else {

			printf(
				wp_kses(
					__( 'There are no LifterLMS Payment Gateways currently installed or configured. To start charging for access you must install and configure a <a href="%s" target="_blank">LifterLMS Payment Gateway.</a>', 'lifterlms' ),
					array(
						'a' => array(
							'href' => array(),
							'target' => array(),
						),
					)
				),
				'https://lifterlms.com/product-category/plugins/e-commerce/'
			);

		}

	}

	public function save( $post_id ) {

		if ( ! isset( $_POST[ $this->prefix . 'plans' ] ) ) {
			return;
		}

		$plans = $_POST[ $this->prefix . 'plans' ];

		if ( ! is_array( $plans ) ) {

			$this->add_error( __( 'Access Plan data was posted in an invalid format', 'lifterlms' ) );

		}

		foreach ( $plans as $data ) {

			// required fields
			if ( empty( $data['title'] ) ) {
				$this->add_error( __( 'Access Plan title is required', 'lifterlms' ) );
			}

			if ( empty( $data['price'] ) && '0' !== $data['price'] ) {
				$this->add_error( __( 'Access Plan price is required', 'lifterlms' ) );
			}

			if ( 'yes' === $data['on_sale'] && empty( $data['sale_price'] ) && '0' !== $data['sale_price'] ) {
				$this->add_error( __( 'Sale price is required if the plan is on sale', 'lifterlms' ) );
			}

			if ( ! empty( $data['trial_offer'] ) && 'yes' === $data['trial_offer'] && empty( $data['trial_price'] ) && '0' !== $data['trial_price'] ) {
				$this->add_error( __( 'Trial price is required if the plan has a trial', 'lifterlms' ) );
			}

			if ( $this->has_errors() ) {
				return;
			}

			if ( empty( $data['id'] ) ) {
				$id = 'new';
				$title = $data['title'];
			} else {
				$id = $data['id'];
				$title = '';
			}

			$plan = new LLMS_Access_Plan( $id, $title );

			$plan->set( 'product_id', $post_id );

			if ( empty( $data['featured'] ) ) {
				$plan->set( 'featured', 'no' );
			}

			foreach ( $data as $key => $val ) {
				$plan->set( $key, $val );
			}

		}

	}

















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
	// public static function save( $post_id, $post ) {

	// 	global $wpdb;
	// 	do_action( 'lifterlms_before_save_product_meta_box', $post_id, $post );

	// 	// Update post meta
	// 	if ( isset( $_POST['_regular_price'] ) ) {

	// 		/**
	// 		 * @todo  deprecate '_price' b/c all this logic should be handled from the Product Class
	// 		 */
	// 		update_post_meta( $post_id, '_regular_price', ( $_POST['_regular_price'] === '' ) ? '' : llms_format_decimal( $_POST['_regular_price'] ) );
	// 		update_post_meta( $post_id, '_price', ( $_POST['_regular_price'] === '' ) ? '' : llms_format_decimal( $_POST['_regular_price'] ) );

	// 	}

	// 	if ( isset( $_POST['_sale_price'] ) ) {

	// 		update_post_meta( $post_id, '_sale_price', ( $_POST['_sale_price'] === '' ? '' : llms_format_decimal( $_POST['_sale_price'] ) ) );

	// 	}

	// 	//Update Sales Price Dates
	// 	$date_from = isset( $_POST['_sale_price_dates_from'] ) && $_POST['_sale_price_dates_from'] ? LLMS_Date::db_date( $_POST['_sale_price_dates_from'] ) : '';
	// 	$date_to = isset( $_POST['_sale_price_dates_to'] ) && $_POST['_sale_price_dates_to'] ? LLMS_Date::db_date( $_POST['_sale_price_dates_to'] ) : '';

	// 	// Dates
	// 	update_post_meta( $post_id, '_sale_price_dates_from', $date_from );
	// 	update_post_meta( $post_id, '_sale_price_dates_to', $date_to );

	// 	if ( $date_to && ! $date_from ) {
	// 		update_post_meta( $post_id, '_sale_price_dates_from', LLMS_Date::db_date( strtotime( 'NOW', current_time( 'timestamp' ) ) ) );
	// 		$date_from = LLMS_Date::db_date( strtotime( 'NOW', current_time( 'timestamp' ) ) );
	// 	}

	// 	// can't be on sale without a sale price
	// 	if ( isset( $_POST['_on_sale'] ) && isset( $_POST['_sale_price'] ) ) {

	// 		$on_sale = llms_clean( $_POST['_on_sale'] );
	// 		update_post_meta( $post_id, '_on_sale', $on_sale );

	// 	} else {

	// 		update_post_meta( $post_id, '_on_sale', '' );

	// 	}

	// 	//Update Recurring Payments
	// 	if ( isset( $_POST['_llms_recurring_enabled'] )
	// 		&& ! empty( $_POST['_llms_subscription_price'] )
	// 		&& isset( $_POST['_llms_billing_period'] )
	// 		&& ! empty( $_POST['_llms_billing_freq'] )) {

	// 		$recurring_enabled 			= llms_clean( $_POST['_llms_recurring_enabled'] );
	// 		$subscription_price 		= llms_clean( $_POST['_llms_subscription_price'] );
	// 		$subscription_first_payment = ( ! $_POST['_llms_subscription_first_payment'] == '' ? llms_clean( $_POST['_llms_subscription_first_payment'] ) : '0' );
	// 		$billing_period 			= llms_clean( $_POST['_llms_billing_period'] );
	// 		$billing_freq 				= llms_clean( $_POST['_llms_billing_freq'] );
	// 		$billing_cycle				= llms_clean( $_POST['_llms_billing_cycle'] );

	// 		update_post_meta( $post_id, '_llms_recurring_enabled', $recurring_enabled );
	// 		update_post_meta( $post_id, '_llms_subscription_price', llms_format_decimal( $subscription_price ) );
	// 		update_post_meta( $post_id, '_llms_subscription_first_payment', llms_format_decimal( $subscription_first_payment ) );
	// 		update_post_meta( $post_id, '_llms_billing_period', $billing_period );
	// 		update_post_meta( $post_id, '_llms_billing_freq', $billing_freq );
	// 		update_post_meta( $post_id, '_llms_billing_cycle', $billing_cycle );

	// 		$llms_subs = array();
	// 		$llms_sub = array();
	// 		$llms_sub['billing_cycle'] = $billing_cycle;
	// 		$llms_sub['billing_freq'] = $billing_freq;
	// 		$llms_sub['billing_period'] = $billing_period;
	// 		$llms_sub['total_price'] = ( ( $subscription_price * $billing_cycle ) + $subscription_first_payment );
	// 		$llms_sub['sub_price'] = $subscription_price;
	// 		$llms_sub['first_payment'] = $subscription_first_payment;

	// 		$llms_subs[] = $llms_sub;
	// 		update_post_meta( $post_id, '_llms_subscriptions', $llms_subs );

	// 	} else {
	// 		update_post_meta( $post_id, '_llms_recurring_enabled', '' );
	// 		update_post_meta( $post_id, '_llms_subscription_price', '' );
	// 		update_post_meta( $post_id, '_llms_subscription_first_payment', '' );
	// 		update_post_meta( $post_id, '_llms_billing_period', '' );
	// 		update_post_meta( $post_id, '_llms_billing_freq', '' );
	// 		update_post_meta( $post_id, '_llms_billing_cycle', '' );

	// 		$llms_subs = array();
	// 		update_post_meta( $post_id, '_llms_subscriptions', $llms_subs );
	// 	}

	// 	// Unique SKU
	// 	if ( isset( $_POST['_sku'] ) ) {
	// 		$sku = get_post_meta( $post_id, '_sku', true );
	// 		$new_sku = llms_clean( stripslashes( $_POST['_sku'] ) );

	// 		if ( $new_sku == '' ) {
	// 			update_post_meta( $post_id, '_sku', '' );
	// 		} elseif ( $new_sku !== $sku ) {
	// 			if ( ! empty( $new_sku ) ) {
	// 				if (
	// 					$wpdb->get_var( $wpdb->prepare("
	// 						SELECT $wpdb->posts.ID
	// 					    FROM $wpdb->posts
	// 					    LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
	// 					    WHERE ($wpdb->posts.post_type = 'course'
	// 					    OR $wpdb->posts.post_type = 'llms_membership')
	// 					    AND $wpdb->posts.post_status = 'publish'
	// 					    AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
	// 					 ", $new_sku ) )
	// 					) {

	// 					LLMS_Admin_Meta_Boxes::add_error( __( 'The SKU used already exists. Please create a unique SKU.', 'lifterlms' ) );

	// 				} else {
	// 					update_post_meta( $post_id, '_sku', $new_sku );
	// 				}
	// 			} else {
	// 				update_post_meta( $post_id, '_sku', '' );
	// 			}
	// 		}
	// 	}

	// 	// custom text for price checkbox
	// 	if (isset( $_POST['_is_custom_single_price'] ) &&
	// 		(isset( $_POST['_custom_single_price_html'] ) && strlen( trim( $_POST['_custom_single_price_html'] ) ) > 0)) {
	// 		$is_custom_single_price = llms_clean( $_POST['_is_custom_single_price'] );
	// 		$custom_single_price = llms_clean( $_POST['_custom_single_price_html'] );
	// 		update_post_meta( $post_id, '_is_custom_single_price', $is_custom_single_price );
	// 		update_post_meta( $post_id, '_custom_single_price_html', $custom_single_price );
	// 	} else {
	// 		update_post_meta( $post_id, '_is_custom_single_price', '' );
	// 		update_post_meta( $post_id, '_custom_single_price_html', '' );
	// 	}

	// 	do_action( 'lifterlms_after_save_product_meta_box', $post_id, $post );
	// }
}
