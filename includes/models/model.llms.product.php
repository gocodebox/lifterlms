<?php
/**
 * LifterLMS Product Model
 * Both Courses and Memberships are sellable and can be instantiated as a product.
 *
 * @package  LifterLMS/Models
 * @since    1.0.0
 * @version  3.25.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Product model.
 */
class LLMS_Product extends LLMS_Post_Model {

	protected $properties = array();

	protected $db_post_type = 'product'; // maybe fix this
	protected $model_post_type = 'product';

	/**
	 * Retrieve the max number of access plans that can be created
	 * for this product
	 * @return int
	 * @since 3.0.0
	 * @version 3.0.0
	 */
	public function get_access_plan_limit() {
		return apply_filters( 'llms_get_product_access_plan_limit', 6, $this );
	}

	/**
	 * Get all access plans for the product
	 * @param    boolean  $free_only     only include free access plans if true
	 * @param    boolean  $visible_only  excludes hidden access plans from results
	 * @return   array
	 * @since    3.0.0
	 * @version  3.25.2
	 */
	public function get_access_plans( $free_only = false, $visible_only = true ) {

		$args = array(
			'meta_key' => '_llms_product_id',
			'meta_value' => $this->get( 'id' ),
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'posts_per_page' => $this->get_access_plan_limit(),
			'post_type' => 'llms_access_plan',
			'status' => 'publish',
		);

		// filter results to only free access plans
		if ( $free_only ) {
			$args['meta_query'] = array(
				array(
					'key' => '_llms_is_free',
					'value' => 'yes',
				),
			);
		}

		// exclude hidden access plans from the results
		if ( $visible_only ) {
			$args['tax_query'] = array(
				array(
					'field' => 'name',
					'operator' => 'NOT IN',
					'terms' => array( 'hidden' ),
					'taxonomy' => 'llms_access_plan_visibility',
				),
			);
		}

		$query = new WP_Query( apply_filters( 'llms_get_product_access_plans_args', $args, $this, $free_only, $visible_only ) );

		// retup return
		$plans = array();

		// if we have plans, setup access plan instances
		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$plans[] = new LLMS_Access_Plan( $post );
			}
		}

		return apply_filters( 'llms_get_product_access_plans', $plans, $this, $free_only, $visible_only );

	}

	/**
	 * Retrieve the product's catalog visibility term
	 * @return   string
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function get_catalog_visibility() {

		$terms = wp_get_post_terms( $this->get( 'id' ), 'llms_product_visibility' );

		if ( $terms && is_array( $terms ) ) {
			$obj = $terms[0];
			if ( isset( $obj->name ) ) {
				return $obj->name;
			}
		}

		return 'catalog_search';
	}

	/**
	 * Retrieve the product's catalog visibility name for display
	 * @return   string
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function get_catalog_visibility_name() {

		$visibility = $this->get_catalog_visibility();
		$options = llms_get_product_visibility_options();
		if ( isset( $options[ $visibility ] ) ) {
			return $options[ $visibility ];
		}
		return $visibility;

	}


	/**
	 * Get the number of columns for the pricing table
	 * @param    boolean    $free_only  only include free access plans if true
	 * @return   int
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_pricing_table_columns_count( $free_only = false ) {

		$count = count( $this->get_access_plans( $free_only ) );

		switch ( $count ) {

			case 0:
				$cols = 1;
			break;

			case 6:
				$cols = 3;
			break;

			default:
				$cols = $count;
		}
		return apply_filters( 'llms_get_product_pricing_table_columns_count', $cols, $this, $count, $free_only );
	}

	/**
	 * Determine if the product has at least one free access plan
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.25.2
	 */
	public function has_free_access_plan() {
		return apply_filters( 'llms_product_has_free_access_plan', ( 0 !== count( $this->get_access_plans( true ) ) ) );
	}

	/**
	 * Deterime if the product is purchasable
	 * At least one gateway must be enabled and at least one access plan must exist
	 * If the product is a course, additionally checks to ensure course enrollment is open and has capacity
	 * @return  boolean
	 * @since   3.0.0
	 * @version 3.25.2
	 */
	public function is_purchasable() {

		// Default to true.
		$ret = true;

		// Courses must have open enrollment & available capacity.
		if ( 'course' === $this->get( 'type' ) ) {

			$course = new LLMS_Course( $this->get( 'id' ) );
			$ret    = ( $course->is_enrollment_open() && $course->has_capacity() );

		}

		// if we're still true, make sure we have a purchaseable plan & active gateways.
		if ( $ret ) {
			$gateways = LLMS()->payment_gateways();
			$ret      = ( $this->get_access_plans( false, false ) && $gateways->has_gateways( true ) );
		}

		return apply_filters( 'llms_product_is_purchasable', $ret, $this );

	}

	/**
	 * Update the product's catalog visibility setting
	 * @param    string    $visibility  visibility term name
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function set_catalog_visibility( $visibility ) {
		if ( ! in_array( $visibility, array_keys( llms_get_product_visibility_options() ) ) ) {
			return;
		}
		wp_set_object_terms( $this->get( 'id' ), $visibility, 'llms_product_visibility', false );
	}

}
