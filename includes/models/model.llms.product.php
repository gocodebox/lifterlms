<?php
/**
 * LifterLMS Product Model.
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 1.0.0
 * @version 6.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Product model class.
 *
 * Both Courses and Memberships are sellable and can be instantiated as a product.
 *
 * @since 1.0.0
 * @since 3.25.2 Unknown.
 * @since 3.37.17 Fixed a typo in the `post_status` query arg when retrieving access plans for this product.
 *                Use `in_array` with strict comparison where possible.
 * @since 3.38.0 Add `get_restrictions()` and `has_restrictions()` methods.
 */
class LLMS_Product extends LLMS_Post_Model {

	/**
	 * Model properties.
	 *
	 * @var array
	 */
	protected $properties = array();

	/**
	 * Model DB Post Type.
	 *
	 * @todo The post type depends conditionally on whether it's a course or a membership so this is semantically incorrect.
	 *
	 * @var string
	 */
	protected $db_post_type = 'product';

	/**
	 * Model type.
	 *
	 * @var string
	 */
	protected $model_post_type = 'product';

	/**
	 * Retrieve the max number of access plans that can be created for this product.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_access_plan_limit() {

		/**
		 * Determine the number of access plans allowed on the product.
		 *
		 * This is a (somewhat) arbitrary limit chosen mostly based on the following 2 factors:
		 *
		 * 1) It looks visually unappealing to have a pricing table with 7+ items on it.
		 * 2) Having 7+ pricing plans creates a lot of decision fatigue for users.
		 *
		 * If you disagree with either of these two factors you can quite easily change the
		 * limit using this filter.
		 *
		 * Keep in mind that increasing the limit will likely require you to add CSS to accommodate
		 * 7+ plans on the automatically generated pricing tables.
		 *
		 * Also, since plans are limited by the core to 6, we have no pagination built in for any queries that
		 * lookup or list access plans. This means that if you greatly increase the limit (say 200) you
		 * could very quickly run into issues where the default queries "do not scale" well. In which
		 * case you should first consider if you really need 200 plans and then start investigating other
		 * filters to add pagination (and probably caching) to these (now slow) queries.
		 *
		 * @since 3.0.0
		 *
		 * @param int          $limit Number of plans.
		 * @param LLMS_Proudct $this  Product object.
		 */
		return apply_filters( 'llms_get_product_access_plan_limit', 6, $this );
	}

	/**
	 * Get all access plans for the product.
	 *
	 * @since 3.0.0
	 * @since 3.25.2 Unknown.
	 * @since 3.37.17 Fixed a typo in the `post_status` query arg when retrieving access plans for this product.
	 *
	 * @param bool $free_only    Optional. Only include free access plans if `true`. Defalt `false`
	 * @param bool $visible_only Optional. Excludes hidden access plans from results. Default `true`.
	 * @return array
	 */
	public function get_access_plans( $free_only = false, $visible_only = true ) {

		$args = array(
			'meta_key'       => '_llms_product_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $this->get( 'id' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'posts_per_page' => $this->get_access_plan_limit(),
			'post_type'      => 'llms_access_plan',
			'post_status'    => 'publish',
		);

		// Filter results to only free access plans.
		if ( $free_only ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_llms_is_free',
					'value' => 'yes',
				),
			);
		}

		// Exclude hidden access plans from the results.
		if ( $visible_only ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'field'    => 'name',
					'operator' => 'NOT IN',
					'terms'    => array( 'hidden' ),
					'taxonomy' => 'llms_access_plan_visibility',
				),
			);
		}

		/**
		 * Filter the product's access plan query args.
		 *
		 * @since Unknown
		 *
		 * @param array        $args         Query args.
		 * @param LLMS_Product $product      The LLMS_Product instance.
		 * @param bool         $free_only    Whether or not to include the free access plans only.
		 * @param bool         $visbile_only Whether or not to exclude the hidden access plans.
		 */
		$query = new WP_Query( apply_filters( 'llms_get_product_access_plans_args', $args, $this, $free_only, $visible_only ) );

		$plans = array();

		// If we have plans, setup access plan instances.
		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$plans[] = new LLMS_Access_Plan( $post );
			}
		}

		/**
		 * Filter the product's access plans.
		 *
		 * @since Unknown
		 *
		 * @param array        $plans        An array of LLMS_Access_Plan instances related to the product `$product`.
		 * @param LLMS_Product $product      The LLMS_Product instance.
		 * @param bool         $free_only    Whether or not to include the free access plans only.
		 * @param bool         $visbile_only Whether or not to exclude the hidden access plans.
		 */
		return apply_filters( 'llms_get_product_access_plans', $plans, $this, $free_only, $visible_only );

	}

	/**
	 * Retrieve the product's catalog visibility term.
	 *
	 * @since 3.6.0
	 *
	 * @return string
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
	 * Retrieve the product's catalog visibility name for display.
	 *
	 * @since 3.6.0
	 *
	 * @return string
	 */
	public function get_catalog_visibility_name() {

		$visibility = $this->get_catalog_visibility();
		$options    = llms_get_product_visibility_options();
		if ( isset( $options[ $visibility ] ) ) {
			return $options[ $visibility ];
		}
		return $visibility;

	}


	/**
	 * Get the number of columns for the pricing table.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $free_only Optional. Only include free access plans if true. Default `false`.
	 * @return int
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

		/**
		 * Filter the number of columns of the product's pricing table.
		 *
		 * @since 3.0.0
		 *
		 * @param int          $cols      The number of columns of the pricing table for the `$product`.
		 * @param LLMS_Product $product   The LLMS_Product instance.
		 * @param int          $count     The number of access plans related to the product `$product`.
		 * @param bool         $free_only Whether or not to include the free access plans only.
		 */
		return apply_filters( 'llms_get_product_pricing_table_columns_count', $cols, $this, $count, $free_only );
	}

	/**
	 * Retrieve a list of restrictions on the product.
	 *
	 * Restrictions are used to in conjunction with "is_purchasable()" to
	 * determine if purchase/enrollment should be allowed for a given product.
	 *
	 * Restrictions in the core currently only exist on courses:
	 * + Enrollment time period.
	 * + Student capacity.
	 *
	 * @since 3.38.0
	 *
	 * @return string[] An array of strings describing the restrictions placed on the product.
	 */
	public function get_restrictions() {

		$restrictions = array();

		if ( 'course' === $this->get( 'type' ) ) {

			$course = new LLMS_Course( $this->get( 'id' ) );

			// Is the course enrollment period open?
			if ( ! $course->is_enrollment_open() ) {
				$restrictions[] = 'enrollment_period';
			}

			// Does the course have capacity?
			if ( ! $course->has_capacity() ) {
				$restrictions[] = 'student_capacity';
			}
		}

		/**
		 * Filter the product's restrictions.
		 *
		 * @since 6.8.0
		 *
		 * @param string[]     $restrictions An array of strings describing the restrictions placed on the product.
		 * @param LLMS_Product $product      The LLMS_Product object.
		 */
		return apply_filters( 'llms_product_get_restrictions', $restrictions, $this );

	}


	/**
	 * Determine if the product has at least one free access plan.
	 *
	 * @since 3.0.0
	 * @since 3.25.2 Unknown.
	 *
	 * @return bool
	 */
	public function has_free_access_plan() {

		/**
		 * Filter whether the product has free access plans.
		 *
		 * @since Unknown
		 * @since 3.37.17 Added the `$product` param.
		 *
		 * @param bool         $has_free_access_plan Whether the product `$product` has free access plans.
		 * @param LLMS_Product $product              The LLMS_Product instance.
		 */
		return apply_filters( 'llms_product_has_free_access_plan', ( 0 !== count( $this->get_access_plans( true ) ) ), $this );

	}

	/**
	 * Determine if any restrictions exist on the product.
	 *
	 * @since 3.38.0
	 *
	 * @see LLMS_Proudct::get_restrictions()
	 *
	 * @return boolean `true` if there is at least one restriction on the product, `false` otherwise.
	 */
	public function has_restrictions() {

		$restrictions     = $this->get_restrictions();
		$has_restrictions = count( $restrictions ) > 0;

		/**
		 * Filter whether the product has any purchase restrictions.
		 *
		 * @since 3.38.0
		 *
		 * @param bool         $has_restrictions Whether the product `$product` has restrictions.
		 * @param string[]     $restrictions     Array of restrictions placed on the product.
		 * @param LLMS_Product $product          The LLMS_Product object.
		 */
		return apply_filters( 'llms_product_has_restrictions', $has_restrictions, $restrictions, $this );

	}

	/**
	 * Determine if the product is purchasable.
	 *
	 * At least one gateway must be enabled and at least one access plan must exist.
	 * If the product is a course, additionally checks to ensure course enrollment is open and has capacity.
	 *
	 * @since 3.0.0
	 * @since 3.25.2 Unknown.
	 * @since 3.38.0 Use `has_restrictions()` to determine if the product has additional restrictions.
	 *
	 * @return bool
	 */
	public function is_purchasable() {

		// Default to false.
		$purchasable = false;

		// If the product doesn't have any purchase restrictions, make sure we have a purchasable plan & active gateways.
		if ( ! $this->has_restrictions() ) {
			$gateways    = llms()->payment_gateways();
			$purchasable = ( $this->get_access_plans( false, false ) && $gateways->has_gateways( true ) );
		}

		/**
		 * Filter whether the product is purchasable.
		 *
		 * @since Unknown
		 *
		 * @param bool         $purchasable Whether the product `$product` is purchasable.
		 * @param LLMS_Product $product     The LLMS_Product instance.
		 */
		return apply_filters( 'llms_product_is_purchasable', $purchasable, $this );

	}

	/**
	 * Update the product's catalog visibility setting.
	 *
	 * @since 3.6.0
	 * @since 3.37.17 Use `in_array` with strict comparison.
	 *
	 * @param string $visibility Visibility term name.
	 * @return void
	 */
	public function set_catalog_visibility( $visibility ) {
		if ( ! in_array( $visibility, array_keys( llms_get_product_visibility_options() ), true ) ) {
			return;
		}
		wp_set_object_terms( $this->get( 'id' ), $visibility, 'llms_product_visibility', false );
	}

	/**
	 * Check if there are active subscriptions for this product.
	 *
	 * @since 5.4.0
	 *
	 * @param boolean $use_cache Whether or not leveraging the cache.
	 * @return boolean
	 */
	public function has_active_subscriptions( $use_cache = true ) {

		$found = false;
		if ( $use_cache ) {
			$subscriptions_count = wp_cache_get( $this->get( 'id' ), 'llms_product_subscriptions_count', true, $found );
		}

		if ( false === $found ) {

			global $wpdb;

			$subscriptions_count = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT COUNT(*) FROM {$wpdb->posts} as p
					JOIN {$wpdb->postmeta} as pm1
					JOIN {$wpdb->postmeta} as pm2
					WHERE p.ID=pm1.post_id
					AND p.post_type='llms_order'
					AND pm1.post_id=pm2.post_id
					AND pm1.meta_key='_llms_product_id' AND pm1.meta_value=%d
					AND pm2.meta_key='_llms_order_type' AND pm2.meta_value='recurring'
					AND p.post_status IN ( 'llms-active', 'llms-pending-cancel', 'llms-on-hold' )
					",
					$this->get( 'id' )
				)
			);

			wp_cache_set(
				$this->get( 'id' ),
				$subscriptions_count,
				'llms_product_subscriptions_count'
			);

		}

		return (bool) $subscriptions_count;

	}

}
