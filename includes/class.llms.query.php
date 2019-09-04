<?php
/**
 * Query base class
 * Handles queries and endpoints.
 *
 * @since 1.0.0
 * @version 3.33.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Query class
 *
 * @since 1.0.0
 * @since 3.31.0 Deprecated `add_query_vars() method and added sanitizing functions when accessing `$_GET` vars.
 * @since 3.33.0 Added catalog secondary sorting by `post_title` when the primary sort is `menu_order`.
 */
class LLMS_Query {

	/**
	 * Query var
	 *
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @version 3.28.2
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'add_endpoints' ) );

		if ( ! is_admin() ) {

			add_filter( 'query_vars', array( $this, 'set_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );

		}

		$this->init_query_vars();

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

	}

	/**
	 * Add Query Endpoints
	 *
	 * @since 1.0.0
	 * @since 3.28.2 Handle dashboard tab pagination via a rewrite rule.
	 *
	 * @return void
	 */
	public function add_endpoints() {

		foreach ( $this->get_query_vars() as $key => $var ) {
			add_rewrite_endpoint( $var, EP_PAGES );
		}

		global $wp_rewrite;
		foreach ( LLMS_Student_Dashboard::get_tabs() as $id => $tab ) {
			if ( ! empty( $tab['paginate'] ) ) {
				$regex    = sprintf( '(.?.+?)/%1$s/%2$s/?([0-9]{1,})/?$', $tab['endpoint'], $wp_rewrite->pagination_base );
				$redirect = sprintf( 'index.php?pagename=$matches[1]&%s=$matches[3]&paged=$matches[2]', $tab['endpoint'] );
				add_rewrite_rule( $regex, $redirect, 'top' );
			}
		}

	}

	/**
	 * Get query variables
	 *
	 * @since Unknown
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'llms_get_endpoints', $this->query_vars );
	}

	/**
	 * Get a taxonomy query that filters out courses & memberships based on catalog / search visibility settings
	 *
	 * @since    3.6.0
	 *
	 * @param array $query Existing taxonomy query from the global $wp_query.
	 * @return array
	 */
	private function get_tax_query( $query = array() ) {

		if ( ! is_array( $query ) ) {
			$query = array(
				'relation' => 'AND',
			);
		}

		$terms = wp_list_pluck(
			get_terms(
				array(
					'taxonomy'   => 'llms_product_visibility',
					'hide_empty' => false,
				)
			),
			'term_taxonomy_id',
			'name'
		);

		$not_in = ( is_search() ) ? array( $terms['hidden'], $terms['catalog'] ) : array( $terms['hidden'], $terms['search'] );

		$query[] = array(
			'field'    => 'term_taxonomy_id',
			'operator' => 'NOT IN',
			'taxonomy' => 'llms_product_visibility',
			'terms'    => $not_in,
		);

		return $query;

	}

	/**
	 * Init queries
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function init_query_vars() {

		$this->query_vars = array(
			'confirm-payment' => get_option( 'lifterlms_myaccount_confirm_payment_endpoint', 'confirm-payment' ),
			'lost-password'   => get_option( 'lifterlms_myaccount_lost_password_endpoint', 'lost-password' ),
		);

	}

	/**
	 * Parse the request for query variables
	 *
	 * @since unknown
	 * @since 3.31.0 sanitize and unslash `$_GET` vars.
	 *
	 * @return void
	 */
	public function parse_request() {

		global $wp;

		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) );
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}

	}

	/**
	 * Sets the WP_Query variables for "post_type" on LifterLMS custom taxonomy archive pages for Courses and Memberships
	 *
	 * @since 1.4.4 Moved from LLMS_Post_Types.
	 * @since 3.16.8
	 * @since 3.33.0 Added `post_title` as a secondary sort when the primary sort is `menu_order`
	 *
	 * @param WP_Query $query Main WP_Query Object.
	 * @return void
	 */
	public function pre_get_posts( $query ) {

		$modify_tax_query = false;

		if ( ! is_admin() && $query->is_main_query() ) {

			if ( is_search() ) {
				$modify_tax_query = true;
			}

			if ( is_tax( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track', 'membership_tag', 'membership_cat' ) ) ) {

				$query->set( 'post_type', array( 'course', 'llms_membership' ) );
				$modify_tax_query = true;

			}

			if ( is_post_type_archive( 'course' ) || $query->get( 'page_id' ) == llms_get_page_id( 'courses' ) || is_tax( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track' ) ) ) {

				$query->set( 'posts_per_page', get_option( 'lifterlms_shop_courses_per_page', 10 ) );

				$sorting = explode( ',', get_option( 'lifterlms_shop_ordering', 'menu_order,ASC' ) );

				$orderby = empty( $sorting[0] ) ? 'menu_order' : $sorting[0];
				if ( 'menu_order' === $orderby ) {
					$orderby .= ' post_title';
				}
				$order = empty( $sorting[1] ) ? 'ASC' : $sorting[1];

				$query->set( 'orderby', apply_filters( 'llms_courses_orderby', $orderby ) );
				$query->set( 'order', apply_filters( 'llms_courses_order', $order ) );

				$modify_tax_query = true;

			} elseif ( is_post_type_archive( 'llms_membership' ) || $query->get( 'page_id' ) == llms_get_page_id( 'memberships' ) || is_tax( array( 'membership_tag', 'membership_cat' ) ) ) {

				$query->set( 'posts_per_page', get_option( 'lifterlms_memberships_per_page', 10 ) );

				$sorting = explode( ',', get_option( 'lifterlms_memberships_ordering', 'menu_order,ASC' ) );

				$orderby = empty( $sorting[0] ) ? 'menu_order' : $sorting[0];
				if ( 'menu_order' === $orderby ) {
					$orderby .= ' post_title';
				}
				$order = empty( $sorting[1] ) ? 'ASC' : $sorting[1];

				$query->set( 'orderby', apply_filters( 'llms_memberships_orderby', $orderby ) );
				$query->set( 'order', apply_filters( 'llms_memberships_order', $order ) );

				$modify_tax_query = true;

			}

			// remove action when finished
			remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		}// End if().

		if ( $modify_tax_query ) {

			$query->set( 'tax_query', $this->get_tax_query( $query->get( 'tax_query' ) ) );

		}

	}

	/**
	 * Set query variables
	 *
	 * @since Unknown
	 *
	 * @param  array $vars WP query variables available for query.
	 * @return array
	 */
	public function set_query_vars( $vars ) {

		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}

		return $vars;

	}

	/**
	 * Handles setting the "paged" variable on Student Dashboard endpoints
	 * which utilize page/{n} style pagination
	 *
	 * @since 3.14.0
	 * @deprecated 3.28.2 $paged automatically set via add_rewrite_rule() in $this->add_endpoints() method.
	 *
	 * @return void
	 */
	public function set_dashboard_pagination() {

		llms_deprecated_function( 'LLMS_Query::set_dashboard_pagination()', '3.28.2' );
		$tab = LLMS_Student_Dashboard::get_current_tab( 'slug' );
		$var = get_query_var( $tab );
		if ( $var ) {
			global $wp_rewrite;
			$paged = explode( '/', $var );
			// this should work on localized sites
			if ( $wp_rewrite->pagination_base === $paged[0] ) {
				set_query_var( 'paged', $paged[1] );
			}
		}

	}

	/**
	 * Add query variables
	 *
	 * @since 1.0.0
	 * @deprecated 3.31.0 Use LLMS_Query::set_query_vars() instead.
	 *
	 * @param array $vars WP query variables available for query.
	 * @return array
	 */
	public function add_query_vars( $vars ) {

		llms_deprecated_function( 'LLMS_Query::add_query_vars()', '3.31.0', 'LLMS_Query::set_query_vars()' );
		return $this->set_query_vars( $vars );

	}

}

return new LLMS_Query();
