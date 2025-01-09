<?php
/**
 * LifterLMS Course/Membership Pricing Table Module HTML
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/PricingTable/Classes
 *
 * @since 1.3.0
 * @version 1.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Course/Membership Pricing Table Module HTML class.
 *
 * @since 1.3.0
 */
class LLMS_Lab_Pricing_Table_Module extends FLBUilderModule {

	/**
	 * Constructor.
	 *
	 * @since 1.3.0
	 * @since 1.7.0 Escape strings.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'          => esc_html__( 'Pricing Table', 'lifterlms' ),
				'description'   => esc_html__( 'LifterLMS Course / Membership Pricing Table', 'lifterlms' ),
				'category'      => esc_html__( 'LifterLMS Modules', 'lifterlms' ),
				'dir'           => LLMS_BB_MODULES_DIR . 'pricing-table/',
				'url'           => LLMS_BB_MODULES_URL . 'pricing-table/',
				'editor_export' => false,
				'enabled'       => true,
			)
		);

		// Ensure pricing tables always display when used within a BB module.
		add_action( 'llms_lab_bb_before_pricing_table', array( $this, 'add_force_show_table_filter' ) );
		add_action( 'lifterlms_after_access_plans', array( $this, 'remove_force_show_table_filter' ) );

		// Ensure pricing tables always display when the frontend builder is active.
		add_filter( 'llms_product_pricing_table_enrollment_status', array( $this, 'show_table' ) );
	}

	/**
	 * Force display of pricing tables within BB modules.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function add_force_show_table_filter() {
		add_filter( 'llms_product_pricing_table_enrollment_status', '__return_false' );
	}

	/**
	 * Remove force display after pricing tables within BB modules.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function remove_force_show_table_filter() {
		remove_filter( 'llms_product_pricing_table_enrollment_status', '__return_false' );
	}

	/**
	 * Get the product ID to be used based of BB module settings.
	 *
	 * @since 1.3.0
	 * @since 1.3.0 Use strict comparison for `in_array`.
	 *
	 * @param obj $settings BB node settings object.
	 * @return int|false
	 */
	public function get_product_id( $settings ) {

		$type = $settings->llms_product_type;
		if ( ! $type ) {
			$id = get_the_ID();
		} elseif ( 'course' === $type || 'membership' === $type ) {
			$key = sprintf( 'llms_%s_id', $type );
			$id  = $settings->$key;
		}

		if ( in_array( get_post_type( $id ), array( 'lesson', 'llms_quiz' ), true ) ) {
			$course = llms_get_post_parent_course( $id );
			$id     = $course->get( 'id' );
		}

		// If the current id isn't a course or membership don't proceed.
		if ( ! in_array( get_post_type( $id ), array( 'course', 'llms_membership' ), true ) ) {
			return false;
		}

		return $id;
	}

	/**
	 * Always show the pricing table when the builder is active.
	 *
	 * @since 1.3.0
	 *
	 * @param bool $enrollment Enrollment status of the current user.
	 * @return bool
	 */
	public function show_table( $enrollment ) {

		if ( FLBuilderModel::is_builder_active() ) {
			return false;
		}

		return $enrollment;
	}
}

FLBuilder::register_module(
	'LLMS_Lab_Pricing_Table_Module',
	array(
		'general' => array(
			'title'    => esc_html__( 'General', 'lifterlms' ),
			'sections' => array(
				'general' => array(
					'title'  => esc_html__( 'General', 'lifterlms' ),
					'fields' => array(
						'llms_product_type'  => array(
							'type'    => 'select',
							'label'   => esc_html__( 'Product Type', 'lifterlms' ),
							'options' => array(
								''           => esc_html__( 'Current Course or Membership', 'lifterlms' ),
								'course'     => esc_html__( 'Course', 'lifterlms' ),
								'membership' => esc_html__( 'Memebership', 'lifterlms' ),
							),
							'toggle'  => array(
								'course'     => array(
									'fields' => array( 'llms_course_id' ),
								),
								'membership' => array(
									'fields' => array( 'llms_membership_id' ),
								),
							),
							'preview' => array(
								'type' => 'none',
							),
						),
						'llms_course_id'     => array(
							'type'    => 'suggest',
							'action'  => 'fl_as_posts',
							'data'    => 'course',
							'limit'   => 1,
							'label'   => esc_html__( 'Course', 'lifterlms' ),
							'help'    => esc_html__( 'Choose which course to display a pricing table for.', 'lifterlms' ),
							'preview' => array(
								'type' => 'none',
							),
						),
						'llms_membership_id' => array(
							'type'    => 'suggest',
							'action'  => 'fl_as_posts',
							'data'    => 'llms_membership',
							'limit'   => 1,
							'label'   => esc_html__( 'Membership', 'lifterlms' ),
							'help'    => esc_html__( 'Choose which membership to display a pricing table for.', 'lifterlms' ),
							'preview' => array(
								'type' => 'none',
							),
						),
					),
				),
			),
		),
	)
);
