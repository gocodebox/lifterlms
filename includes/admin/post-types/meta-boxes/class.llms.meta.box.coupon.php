<?php
/**
* Meta Box Builder
*
* Generates main metabox and builds forms
*
* @version  3.0.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Include the file for the parent class
if ( ! defined( 'LLMS_Admin_Metabox' ) ) {
	include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

class LLMS_Meta_Box_Coupon extends LLMS_Admin_Metabox {

	public static $prefix = '_llms_';

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 */
	public static function output( $post ) {
		global $post;
		parent::new_output( $post, self::metabox_options() );
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return array array of metabox fields
	 *
	 * @version  3.0.0
	 */
	public static function metabox_options() {

		global $post;

		/**
		 * Array containing the different types of discounts
		 * @var array
		 */
		$discount_types = array(
			array(
				'key' 	=> 'percent',
				'title' => __( 'Percentage Discount', '%' ),
			),
			array(
				'key' 	=> 'dollar',
				'title' => sprintf( __( '%s Discount', 'lifterlms' ), get_lifterlms_currency_symbol() ),
			),
		);

		$payment_types = array(
			array(
				'key' 	=> 'single',
				'title' => __( 'Single Payments', '%' ),
			),
			array(
				'key' 	=> 'recurring',
				'title' => __( 'Recurring Payments', '%' ),
			),
		);

		$courses = LLMS_Analytics::get_posts( 'course' );

		$courses_select = array();
		if ( ! empty( $courses )) {
			foreach ($courses as $course) {
				$courses_select[] = array(
						'key' => $course->ID,
						'title' => $course->post_title,
				);
			}
		}

		$memberships = LLMS_Analytics::get_posts( 'llms_membership' );

		$memberships_select = array();
		if ( ! empty( $memberships )) {
			foreach ($memberships as $membership) {
				$memberships_select[] = array(
						'key' => $membership->ID,
						'title' => $membership->post_title,
				);
			}
		}

		$selected_products = get_post_meta( $post->ID, '_llms_coupon_products', true );

		$meta_fields_coupon = array(

			array(
				'title' 	=> 'General',
				'fields' 	=> array(
					array(
						'type'		=> 'select',
						'label'		=> __( 'Discount Type', 'lifterlms' ),
						'desc' 		=> __( 'Select a dollar or percentage discount.', 'lifterlms' ),
						'id' 		=> self::$prefix . 'discount_type',
						'class' 	=> 'llms-chosen-select',
						'value' 	=> $discount_types,
						'desc_class' => 'd-all',
						'group' 	=> '',
						'allow_null' => false,
					),
					array(
						'type'  	=> 'number',
						'label'  	=> __( 'Single Payment Discount Amount', 'lifterlms' ),
						'desc'  	=> sprintf( __( 'The value of the coupon for single payment purchases. If left blank, no discount will be applied to single payment purchases. Do not include symbols such as %s or %%.', 'lifterlms' ), get_lifterlms_currency_symbol() ),
						'id'    	=> self::$prefix . 'coupon_amount',
						'section' 	=> 'coupon_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'number',
						'label'  	=> __( 'First Payment Discount Amount', 'lifterlms' ),
						'desc'  	=> sprintf( __( 'The value of the coupon for the first payment of a recurring subscription. If left blank, no discount will be applied to the first payment. Do not include symbols such as %s or %%.', 'lifterlms' ), get_lifterlms_currency_symbol() ),
						'id'    	=> self::$prefix . 'recurring_first_payment_amount',
						'section' 	=> 'coupon_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'number',
						'label'  	=> __( 'Recurring Payments Discount Amount', 'lifterlms' ),
						'desc'  	=> sprintf( __( 'The value of the coupon for recurring payments on a subscription. If left blank, no discount will be applied to recurring payments. Do not include symbols such as %s or %%.', 'lifterlms' ), get_lifterlms_currency_symbol() ),
						'id'    	=> self::$prefix . 'recurring_payments_amount',
						'section' 	=> 'coupon_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
				),
			),

			array(
				'title'  => __( 'Restrictions', 'lifterlms' ),
				'fields' => array(
					array(
						'type'  => 'select',
						'label' => __( 'Courses', 'lifterlms' ),
						'desc'  => __( 'Limit coupon to the following courses.', 'lifterlms' ),
						'id'    => self::$prefix . 'coupon_courses',
						'class' => 'input-full llms-meta-select',
						'value' => $courses_select,
						'multi' => true,
						'selected' => $selected_products,
					),
					array(
						'type'  => 'select',
						'label' => __( 'Membership', 'lifterlms' ),
						'desc'  => __( 'Limit coupon to the following memberships.', 'lifterlms' ),
						'id'    => self::$prefix . 'coupon_membership',
						'class' => 'input-full llms-meta-select',
						'value' => $memberships_select,
						'multi' => true,
						'selected' => $selected_products,
					),
					array(
						'type'		=> 'date',
						'label'		=> __( 'Coupon Expiration Date' ),
						'desc' 		=> __( 'Coupon will no longer be usable after this date. Leave blank for no expiration.', 'lifterlms' ),
						'id' 		=> self::$prefix . 'expiration_date',
						'class' 	=> 'llms-datepicker input-full',
						'value' 	=> '',
						'desc_class' => 'd-all',
						'group' 	=> '',
					),
					array(
						'type'  	=> 'number',
						'label'  	=> __( 'Usage Limit', 'lifterlms' ),
						'desc'  	=> __( 'The amount of times this coupon can be used. Leave empty if unlimited.', 'lifterlms' ),
						'id'    	=> self::$prefix . 'usage_limit',
						'section' 	=> 'coupon_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
				),
			),

			array(
				'title' => __( 'Description', 'lifterlms' ),
				'fields' => array(
					array(
						'type'  	=> 'textarea',
						'label' 	=> __( 'Description', 'lifterlms' ),
						'desc' 		=> __( 'Optional description for internal notes. This is never displayed to your students.', 'lifterlms' ),
						'id' 		=> self::$prefix . 'description',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
						'required'	=> false,
					),
				),
			),
		);

		if (has_filter( 'llms_meta_fields_coupon' )) {
			$meta_fields_coupon = apply_filters( 'llms_meta_fields_coupon', $meta_fields_coupon );
		}

		return $meta_fields_coupon;
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using LLMS_Coupon Model
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 *
	 * @version  3.0.0
	 */
	public static function save( $post_id, $post ) {

		$c = new LLMS_Coupon( $post );

		// dupcheck title
		if ( $c->find_by_code( $post->post_title, $post_id ) ) {

			LLMS_Admin_Meta_Boxes::add_error( __( 'Coupon code already exists. Customers will use the most recently created coupon with this code.', 'lifterlms' ) );

		}

		// discount type (percent/dollar)
		$c->discount_type = isset( $_POST[ self::$prefix . 'discount_type' ] ) ? llms_clean( $_POST[ self::$prefix . 'discount_type' ] ) : 'percent';

		// single payment amount
		$c->coupon_amount = isset( $_POST[ self::$prefix . 'coupon_amount' ] ) ? llms_clean( $_POST[ self::$prefix . 'coupon_amount' ] ) : '';

		// recurring payment amounts
		$c->recurring_first_payment_amount = isset( $_POST[ self::$prefix . 'recurring_first_payment_amount' ] ) ? llms_clean( $_POST[ self::$prefix . 'recurring_first_payment_amount' ] ) : '';
		$c->recurring_payments_amount = isset( $_POST[ self::$prefix . 'recurring_payments_amount' ] ) ? llms_clean( $_POST[ self::$prefix . 'recurring_payments_amount' ] ) : '';

		// product restrictions
		$courses = isset( $_POST['_llms_coupon_courses'] ) ? $_POST[ self::$prefix . 'coupon_courses' ] : array();
		$memberships = isset( $_POST[ self::$prefix . 'coupon_membership' ] ) ? $_POST[ self::$prefix . 'coupon_membership' ] : array();
		$c->coupon_products = array_merge( $courses, $memberships );

		// expiration date
		$c->expiration_date = isset( $_POST[ self::$prefix . 'expiration_date' ] ) ? llms_clean( $_POST[ self::$prefix . 'expiration_date' ] ) : '';

		// usage limit
		$c->usage_limit = isset( $_POST[ self::$prefix . 'usage_limit' ] ) ? llms_clean( $_POST[ self::$prefix . 'usage_limit' ] ) : '';

		// description
		$c->description = isset( $_POST[ self::$prefix . 'description' ] ) ? strip_tags( llms_clean( $_POST[ self::$prefix . 'description' ] ) ) : '';

		// save coupon action
		do_action( 'lifterlms_after_save_coupon_meta_box', $post_id, $post );

	}

}
