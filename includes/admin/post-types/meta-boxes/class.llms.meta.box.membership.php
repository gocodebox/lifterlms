<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'LLMS_Admin_Metabox' ) ) 
{
	// Include the file for the parent class
	include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

/**
* Meta Box Builder
* 
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Membership extends LLMS_Admin_Metabox{

	public static $prefix = '_';

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 * 
	 * @param object $post WP global post object
	 * @return void
	 */
	public static function output ( $post ) {
		global $post;
		parent::new_output( $post, self::metabox_options() );
	}	

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 * 
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {
		global $post, $wpdb, $thepostid;

		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$thepostid = $post->ID;

		$sku = get_post_meta( $thepostid, '_sku', true );
		$regular_price = get_post_meta( $thepostid, '_regular_price', true );
		$sale_price = get_post_meta( $thepostid, '_sale_price', true );

		$sale_price_dates_from 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_from', true ) ) ? $date : '';
		$sale_price_dates_to 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_to', true ) ) ? $date : '';

		$recurring_enabled  		= get_post_meta( $thepostid, '_llms_recurring_enabled', true );
		$subscription_price 		= get_post_meta( $thepostid, '_llms_subscription_price', true );
		$subscription_first_payment = get_post_meta( $thepostid, '_llms_subscription_first_payment', true );
		$billing_period 			= get_post_meta( $thepostid, '_llms_billing_period', true );
		$billing_freq 				= get_post_meta( $thepostid, '_llms_billing_freq', true );
		$billing_cycle				= get_post_meta( $thepostid, '_llms_billing_cycle', true );

		//billing period options
		////needs to move to paypal class
		$billing_periods = array(
			array (
				'key' 	=> 'day',
				'title' => 'Day'
			),
			array (
				'key' 	=> 'week',
				'title' => 'Week'
			),
			array (
				'key' 	=> 'month',
				'title' => 'Month'
			),
			array (
				'key' 	=> 'year',
				'title' => 'Year'
			),
		);

		$membership_expiration_periods = array(
			array (
				'key' 	=> 'day',
				'title' => 'Day'
			),
			array (
				'key' 	=> 'month',
				'title' => 'Month'
			),
			array (
				'key' 	=> 'year',
				'title' => 'Year'
			),
		);

		$llms_meta_fields_llms_membership_settings = array(
			array(
				'title' 	=> 'Price Single',
				'fields' 	=> array(
					array(
						'type'		=> 'text',
						'label'		=> 'SKU',
						'desc' 		=> 'Enter an SKU for your membership.',
						'id' 		=> self::$prefix . 'sku',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Single Payment Price ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter a price to offer your membership for a one time purchase.',
						'id' 		=> self::$prefix . 'regular_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Membership is on sale',
						'desc' 		=> 'Enable single payment sale for this membership.',
						'id' 		=> self::$prefix . 'on_sale',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class'=> 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Sale Price ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter a sale price for the membership.',
						'id' 		=> self::$prefix . 'sale_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'date',
						'label'		=> 'Sale Price Start Date',
						'desc' 		=> 'Enter the date your sale will begin.',
						'id' 		=> self::$prefix . 'sale_price_dates_from',
						'class' 	=> 'datepicker input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'date',
						'label'		=> 'Sale Price End Date',
						'desc' 		=> 'Enter the date your sale will end.',
						'id' 		=> self::$prefix . 'sale_price_dates_to',
						'class' 	=> 'datepicker input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
				)
			),
			array(
				'title' 	=> 'Price Recurring',
				'fields' 	=> array(
					array(
						'type'		=> 'checkbox',
						'label'		=> 'Enable Recurring Payment',
						'desc' 		=> 'Enable recurring payment options.',
						'id' 		=> self::$prefix . 'llms_recurring_enabled',
						'class' 	=> '',
						'value' 	=> '1',
						'desc_class'=> 'd-3of4 t-3of4 m-1of2',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Recurring Payment ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter the amount you will bill at set intervals.',
						'id' 		=> self::$prefix . 'llms_subscription_price',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'First Payment ( ' . get_lifterlms_currency_symbol() . ' )',
						'desc' 		=> 'Enter the payment amount you will charge on product purchase. This can be 0 to give users a free trial period.',
						'id' 		=> self::$prefix . 'llms_subscription_first_payment',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Billing Period',
						'desc' 		=> 'Combine billing period and billing frequency set billing interval. IE: Billing period =  week and frequency 2 will bill every 2 weeks.',
						'id' 		=> self::$prefix . 'llms_billing_period',
						'class' 	=> 'input-full',
						'value' 	=> $billing_periods,
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Billing Frequency',
						'desc' 		=> 'Use with billing period to set billing interval',
						'id' 		=> self::$prefix . 'llms_billing_freq',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					),
					array(
						'type'		=> 'text',
						'label'		=> 'Billing Cycles',
						'desc' 		=> 'Enter 0 to charge indefinately. IE: 12 would bill for 12 months.',
						'id' 		=> self::$prefix . 'llms_billing_cycle',
						'class' 	=> 'input-full',
						'value' 	=> '',
						'desc_class'=> 'd-all',
						'group' 	=> '',
					)
				)
			),
			array(
				'title' 	=> 'Membership Expiration',
				'fields' 	=> array(
					array(
						'label' 	=> 'Interval',
						'desc' 		=> 'Enter the interval. IE: enter 1 and select year below to set expiration to 1 year.',
						'id' 		=> self::$prefix . 'llms_expiration_interval',
						'type'  	=> 'text',
						'section' 	=> 'expiration_interval',
						'group' 	=> '',
						'desc_class'=> 'd-all',
						'class' 	=> 'input-full',
					),
					array(
						'label' 	=> 'Expiration Period',
						'desc' 		=> 'Combine the period with the interval above to set an expiration time line.',
						'id' 		=> self::$prefix . 'llms_expiration_period',
						'type'  	=> 'select',
						'section' 	=> 'expiration_period',
						'value' 	=> $membership_expiration_periods,
						'group' 	=> '',
						'desc_class'=> 'd-all',
						'class' 	=> 'input-full',
					)				
				)
			)
		);

		if(has_filter('llms_meta_fields_llms_membership_settings')) {
			//Add Fields to the membership Meta Box
			$llms_meta_fields_llms_membership_settings = apply_filters('llms_meta_fields_llms_membership_settings', $llms_meta_fields_llms_membership_settings);
		} 
		
		return $llms_meta_fields_llms_membership_settings;
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
	public static function save( $post_id, $post ) {
		global $wpdb;

		// $prefix = '_';
		// $title = $prefix . 'certificate_title';
		// $image = $prefix . 'certificate_image';

		// //update title
		// $update_title = ( llms_clean( $_POST[$title]  ) );
		// update_post_meta( $post_id, $title, ( $update_title === '' ) ? '' : $update_title );
		// Congrats Mark! You found me!
		// //update background image
		// $update_image = ( llms_clean( $_POST[$image]  ) );
		// update_post_meta( $post_id, $image, ( $update_image === '' ) ? '' : $update_image );

	}

}