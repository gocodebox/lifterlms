<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! defined( 'LLMS_Admin_Metabox' ) ) {
	// Include the file for the parent class
	include_once LLMS_PLUGIN_DIR . '/includes/admin/llms.class.admin.metabox.php';
}

/**
* Meta Box Builder
*
* Generates main metabox and builds forms
*/
class LLMS_Meta_Box_Coupon extends LLMS_Admin_Metabox{

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
		global $post;

		/**
		 * Array containing the different types of discounts
		 * @var array
		 */
		$discount_types = array(
			array(
				'key' 	=> 'percent',
				'title' => '% Discount',
			),
			array(
				'key' 	=> 'dollar',
				'title' => sprintf( __( '%s Discount', 'lifterlms' ), get_lifterlms_currency_symbol() ),
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
						'type'  	=> 'text',
						'label' 	=> 'Coupon Code',
						'desc' 		=> 'Enter a code that users will enter to apply this coupon to their item.',
						'id' 		=> self::$prefix . 'llms_coupon_title',
						'section' 	=> 'coupon_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
						'required'	=> true,
					),
					array(
						'type' => 'select',
						'label' => 'Courses',
						'desc' => "Limit coupon to courses and/or memberships, if none selected coupon won't be restricted.",
						'id' => self::$prefix . 'llms_coupon_courses',
						'class' => 'input-full llms-meta-select',
						'value' => $courses_select,
						'multi' => true,
						'selected' => $selected_products,
					),
					array(
						'type' => 'select',
						'label' => 'Membership',
						'id' => self::$prefix . 'llms_coupon_membership',
						'class' => 'input-full llms-meta-select',
						'value' => $memberships_select,
						'multi' => true,
						'selected' => $selected_products,
					),
					array(
						'type'		=> 'select',
						'label'		=> 'Discount Type',
						'desc' 		=> 'Select a dollar or percentage discount.',
						'id' 		=> self::$prefix . 'llms_discount_type',
						'class' 	=> 'llms-chosen-select',
						'value' 	=> $discount_types,
						'desc_class' => 'd-all',
						'group' 	=> '',
						'allow_null' => false,
					),
					array(
						'type'  	=> 'number',
						'label'  	=> 'Coupon Amount',
						'desc'  	=> 'The value of the coupon. Do not include symbols such as $ or %.',
						'id'    	=> self::$prefix . 'llms_coupon_amount',
						'section' 	=> 'coupon_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
					),
					array(
						'type'  	=> 'number',
						'label'  	=> 'Usage Limit',
						'desc'  	=> 'The amount of times this coupon can be used. Leave empty if unlimited.',
						'id'    	=> self::$prefix . 'llms_usage_limit',
						'section' 	=> 'coupon_meta_box',
						'class' 	=> 'code input-full',
						'desc_class' => 'd-all',
						'group' 	=> '',
						'value' 	=> '',
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
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {

	}

}
