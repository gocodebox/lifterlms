<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Order class
	 *
	 * Manages Ordering process.
	 */
class LLMS_Order {

	/**
		 * protected instance of class
		 * @var null
		 */
	protected static $_instance = null;

	/**
		 * Set private instance of class
		 * @return self
		 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();

		}

		return self::$_instance;
	}

	/**
	 * Creates a order post to associate with the enrollment of the user.
	 * Used to create order and skip checkout process
	 *
	 * @param int $user_id [ID of the user]
	 * @param int $post_id [ID of the post]
	 * @param string $payment_method
	 * @return void
	 */
	public function create( $user_id, $post_id, $payment_method = '' ) {
		global $wpdb;

		$post = get_post( $post_id );

		$sku = get_post_meta( $post_id, '_sku', true );

		$order_data = apply_filters( 'lifterlms_new_order', array(
			'post_type' 	=> 'order',
			'post_title' 	=> sprintf( __( 'Order - %s', 'lifterlms' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'lifterlms' ) ) ),
			'post_status' 	=> 'publish',
			'ping_status'	=> 'closed',
			'post_author' 	=> 1,
			'post_password'	=> uniqid( 'order_' ),
		) );

		$order_post_id = wp_insert_post( $order_data, true );

		$result = $wpdb->insert( $wpdb->prefix .'lifterlms_order',
			array(
				'user_id'			=> $user_id,
				'created_date' 		=> current_time( 'mysql' ),
				'completed_date' 	=> current_time( 'mysql' ),
				'order_completed' 	=> 'yes',
				'product_id'		=> $post_id,
				'order_post_id'		=> $order_post_id,
			)
		);

		$result = $wpdb->update( $wpdb->prefix .'lifterlms_order',
			array(
				'completed_date' 	=> current_time( 'mysql' ),
				'order_completed' 	=> 'yes',
				'order_post_id'		=> $order_post_id,
			),
			array(
				'user_id' 			=> $user_id,
				'product_id' 		=> $post_id,
			)
		);

		update_post_meta( $order_post_id,'_llms_user_id', $user_id );
		if (empty( $payment_method )) {
			$payment_method = 'assigned_by_admin'; // previously was typoed "assigned_by_admin" preserving in case we need to migrate later
		}
		update_post_meta( $order_post_id, '_llms_payment_method', $payment_method );
		update_post_meta( $order_post_id, '_llms_product_title', $post->post_title );
		update_post_meta( $order_post_id, '_llms_order_total', '0' );
		update_post_meta( $order_post_id, '_llms_product_sku', $sku );
		update_post_meta( $order_post_id, '_llms_order_currency', get_lifterlms_currency_symbol() );
		update_post_meta( $order_post_id, '_llms_order_product_id', $post_id );
		update_post_meta( $order_post_id, '_llms_order_date', current_time( 'mysql' ) );
	}

	/**
		 * Process order
		 *
		 * Inserts order details in database
		 *
		 * @param  object $order [order data object]
		 *
		 * @return void
		 */
	public function process_order( $order ) {
		global $wpdb;

		if ( isset( $order ) ) {
			$order = $order;
		} elseif ( LLMS()->session->get( 'llms_order', array() ) ) {
			$order = LLMS()->session->get( 'llms_order', array() );
		} else {
			return false;
		}

		$order_exists = $wpdb->get_results( 'SELECT user_id, product_id, order_completed
            FROM ' . $wpdb->prefix . 'lifterlms_order
            WHERE user_id = ' . $order->user_id . ' AND product_id = ' . $order->product_id );

		if ( ! $order_exists ) {
			$result = $wpdb->insert( $wpdb->prefix . 'lifterlms_order', array(
				'user_id'         => $order->user_id,
				'created_date'    => current_time( 'mysql' ),
				'order_completed' => $order->order_completed,
				'product_id'      => $order->product_id,

			) );
		}
	}


	/**
	 * Complete order processing
	 *
	 * @accepts $order (object)
	 * @return Created Order post Id
	 */
	public function update_order( $order ) {
		global $wpdb;

		//check if user is already enrolled in the course.
		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
		$meta_key   = '_status';
		$meta_value = 'Enrolled';

		$user_enrolled = $wpdb->get_results( $wpdb->prepare(
			'SELECT * FROM ' . $table_name . ' WHERE user_id = %d AND post_id = %d AND meta_key = %s AND meta_value = %s ORDER BY updated_date DESC',
		$order->user_id, $order->product_id, $meta_key, $meta_value ) );

		if ( ! empty( $user_enrolled ) ) {
			return;
		}

		if ( isset( $order ) ) {
			$order = $order;
		} elseif ( LLMS()->session->get( 'llms_order', array() ) ) {
			$order = LLMS()->session->get( 'llms_order', array() );
		} else {
			return false;
		}

		//get the type of product ( course / membership )Dange

		$product_obj = get_post( $order->product_id );
		if ( $product_obj->post_type === 'course' ) {
			$order->product_type = 'course';
		} elseif ( $product_obj->post_type === 'llms_membership' ) {
			$order->product_type = 'membership';
		}

		// create order post
		$order_data = apply_filters( 'lifterlms_new_order', array(
			'post_type'     => 'order',
			'post_title'    => sprintf( __( 'Order - %s, %s', 'lifterlms' ), $order->product_type, LLMS_Date::get_localized_date_string() ),
			'post_status'   => 'publish',
			'ping_status'   => 'closed',
			'post_author'   => 1,
			'post_password' => uniqid( 'order_' ),
		) );

		$order_post_id = wp_insert_post( $order_data, true );

		$result = $wpdb->update( $wpdb->prefix . 'lifterlms_order',
			array(
				'completed_date'  => current_time( 'mysql' ),
				'order_completed' => 'yes',
				'order_post_id'   => $order_post_id,
			),
			array(
				'user_id'    => $order->user_id,
				'product_id' => $order->product_id,
			)
		);

		//update coupon post meta
		$coupon = LLMS()->session->get( 'llms_coupon', array() );
		if ( ! empty( $coupon ) ) {
			update_post_meta( $order_post_id, '_llms_order_coupon_id', $coupon->id );
			update_post_meta( $order_post_id, '_llms_order_coupon_type', $coupon->type );
			update_post_meta( $order_post_id, '_llms_order_coupon_amount', $coupon->amount );
			update_post_meta( $order_post_id, '_llms_order_coupon_limit', $coupon->limit );
			update_post_meta( $order_post_id, '_llms_order_coupon_code', $coupon->coupon_code );

			//now that the coupon has been used. post the new coupon limit
			if ( $coupon->limit !== 'unlimited' ) {
				update_post_meta( $coupon->id, '_llms_usage_limit', $coupon->limit );
			}
		}

		// Add order metadata to the order post
		update_post_meta( $order_post_id, '_llms_user_id', $order->user_id );
		if (isset( $order->payment_method )) {
			update_post_meta( $order_post_id, '_llms_payment_method', $order->payment_method );
		}
		update_post_meta( $order_post_id, '_llms_product_title', $order->product_title );

		//calculate order total based on coupon
		if ( ! empty( $coupon ) ) {
			$product = new LLMS_Product( $order->product_id );

			$order->adjusted_price = $product->adjusted_price( $order->total );

			//set total to adjusted price and save coupon total
			update_post_meta( $order_post_id, '_llms_order_total', $product->adjusted_price( $order->total ) );
			update_post_meta( $order_post_id, '_llms_order_coupon_value', $product->get_coupon_discount_total( $order->total ) );

		} else {
			update_post_meta( $order_post_id, '_llms_order_total', $order->total );
		}
		update_post_meta( $order_post_id, '_llms_order_product_price', $order->product_price );
		update_post_meta( $order_post_id, '_llms_order_original_total', $order->total );

		update_post_meta( $order_post_id, '_llms_product_sku', $order->product_sku );
		update_post_meta( $order_post_id, '_llms_order_currency', $order->currency );
		update_post_meta( $order_post_id, '_llms_order_product_id', $order->product_id );
		update_post_meta( $order_post_id, '_llms_order_date', current_time( 'mysql' ) );
		update_post_meta( $order_post_id, '_llms_order_type', $order->payment_option );
		update_post_meta( $order_post_id, '_llms_payment_type', $order->payment_type );
		update_post_meta( $order_post_id, '_llms_product_type', $order->product_type );

		if ( $order->payment_option == 'recurring' ) {
			update_post_meta( $order_post_id, '_llms_order_recurring_price', $order->product_price );
			update_post_meta( $order_post_id, '_llms_order_first_payment', $order->first_payment );
			update_post_meta( $order_post_id, '_llms_order_billing_period', $order->billing_period );
			update_post_meta( $order_post_id, '_llms_order_billing_cycle', $order->billing_cycle );
			update_post_meta( $order_post_id, '_llms_order_billing_freq', $order->billing_freq );
			update_post_meta( $order_post_id, '_llms_order_billing_start_date', $order->billing_start_date );
		}

		// trigger order complete action
		do_action( 'lifterlms_order_complete', $order_post_id );

		// enroll student
		llms_enroll_student( $order->user_id, $order->product_id );

		// trigger purchase action
		do_action( 'lifterlms_product_purchased', $order->user_id, $order->product_id );

		//kill sessions
		unset( LLMS()->session->llms_coupon );
		unset( LLMS()->session->llms_order );

		return $order_post_id;

	}

}
