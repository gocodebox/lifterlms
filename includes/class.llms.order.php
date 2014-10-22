<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Order class
*
* Manages Ordering process.
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Order {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();

		}

		return self::$_instance;
	}

	public function get_order() {

	}

	public function process_order($order) {
		global $wpdb;

		LLMS_log('process_order($order) begin');
		LLMS_log($order);
		LLMS_log('process_order($order) end');

		if (isset($order) ) {
			$order = $order;
		}
		
		elseif ( LLMS()->session->get( 'llms_order', array() ) ) {
			$order = LLMS()->session->get( 'llms_order', array() );
		}
		
		else {
			return false;
		}
		

		$order_exists = $wpdb->get_results("SELECT user_id, product_id, order_completed 
			FROM " . $wpdb->prefix ."lifterlms_order
			WHERE user_id = " . $order->user_id . " AND product_id = " . $order->product_id);

		if ( ! $order_exists ) {

			$result = $wpdb->insert( $wpdb->prefix .'lifterlms_order', array( 
				'user_id' 			=> $order->user_id,  
				'created_date' 		=> current_time('mysql'),
				'order_completed' 	=> $order->order_completed,
				'product_id' 		=> $order->product_id, 

			) );

		}
	}

	public function update_order($order) {
		global $wpdb;

		LLMS_log('update_order($order) begin');
		LLMS_log($order);
		LLMS_log('update_order($order) end');

		if (isset($order) ) {
			$order = $order;
		}
		
		elseif ( LLMS()->session->get( 'llms_order', array() ) ) {
			$order = LLMS()->session->get( 'llms_order', array() );
		}
		
		else {
			return false;
		}

		$order_data = apply_filters( 'lifterlms_new_order', array(
			'post_type' 	=> 'order',
			'post_title' 	=> sprintf( __( 'Order &ndash; %s', 'lifterlms' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'lifterlms' ) ) ),
			'post_status' 	=> 'publish',
			'ping_status'	=> 'closed',
			'post_author' 	=> 1,
			'post_password'	=> uniqid( 'order_' )
		) );

		$order_post_id = wp_insert_post( $order_data, true );

		$result = $wpdb->update( $wpdb->prefix .'lifterlms_order', 
			array( 
				'completed_date' 	=> current_time('mysql'),
				'order_completed' 	=> 'yes',
				'order_post_id'		=> $order_post_id,
			),
			array( 
				'user_id' 			=> $order->user_id, 
				'product_id' 		=> $order->product_id, 
			)
		);
		//llms_log($order);
		//Assign user to the purchased course post
		//update_user_meta($order->user_id,'_llms_student', $order->product_id);

		// Add order metadata to the order post
		update_post_meta($order_post_id,'_llms_user_id', $order->user_id);
		update_post_meta($order_post_id,'_llms_payment_method', $order->payment_method);
		update_post_meta($order_post_id,'_llms_product_title', $order->product_title);
		update_post_meta($order_post_id,'_llms_order_total', $order->total);
		update_post_meta($order_post_id,'_llms_product_sku', $order->product_sku);
		update_post_meta($order_post_id,'_llms_order_currency', $order->currency);
		update_post_meta($order_post_id,'_llms_order_product_id', $order->product_id);

		$user_metadatas = array(
			'_start_date' => 'yes',
			'_status' => 'Enrolled',
			'_progress' => '0'
		);

		foreach ($user_metadatas as $key => $value) {
			$update_user_postmeta = $wpdb->insert( $wpdb->prefix .'lifterlms_user_postmeta', 
				array( 
					'user_id' 			=> $order->user_id,
					'post_id' 			=> $order->product_id,
					'meta_key'			=> $key,
					'meta_value'		=> $value,
					'updated_date'		=> current_time('mysql'),
				)
			);
		}

	}

}
