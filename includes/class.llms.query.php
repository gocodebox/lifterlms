<?php
/**
 * LLMS_Query class file.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query base class
 *
 * Handles queries and endpoints.
 *
 * @since 1.0.0
 * @since 4.0.0 Remove previously deprecated methods.
 */
class LLMS_Query {

	/**
	 * Query var
	 *
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 3.28.2 Unknown.
	 * @since 3.36.3 Changed `pre_get_posts` callback from `10 (default) to `15`,
	 *               so to avoid conflicts with the Divi theme whose callback runs at `10`,
	 *               but since themes are loaded after plugins it overrode our one.
	 * @since 4.5.0 Added action to serve 404s on unviewable certificates.
	 * @since 6.0.0 Add callback to redirect old `llms_my_certificates` requests to the new url.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'add_endpoints' ) );

		if ( ! is_admin() ) {

			add_filter( 'query_vars', array( $this, 'set_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			add_action( 'wp', array( $this, 'maybe_404_certificate' ), 50 );
			add_action( 'wp', array( $this, 'maybe_redirect_certificate' ), 50 );

		}

		$this->init_query_vars();

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 15 );
		add_filter( 'the_posts', array( $this, 'exclude_protected_posts_from_feeds' ), 10, 2 );
		add_filter( 'is_post_embeddable', array( $this, 'maybe_disable_certificate_embed' ), 10, 2 );
		add_filter( 'oembed_response_data', array( $this, 'maybe_hide_certificate_oembed' ), 20, 2 );
		add_filter( 'redirect_canonical', array( $this, 'maybe_prevent_certificate_canonical_redirect' ), 10, 2 );
		add_filter( 'get_previous_post_where', array( $this, 'exclude_hidden_llms_products' ) );
		add_filter( 'get_next_post_where', array( $this, 'exclude_hidden_llms_products' ) );
	}

	/**
	 * Add Query Endpoints
	 *
	 * @since 1.0.0
	 * @since 3.28.2 Handle dashboard tab pagination via a rewrite rule.
	 * @since 5.0.2 Add support for slugs with non-latin characters.
	 *
	 * @return void
	 */
	public function add_endpoints() {

		foreach ( $this->get_query_vars() as $key => $var ) {
			add_rewrite_endpoint( $var, EP_PAGES, $key );
		}

		global $wp_rewrite;
		foreach ( LLMS_Student_Dashboard::get_tabs() as $id => $tab ) {
			if ( ! empty( $tab['paginate'] ) ) {
				$regex    = sprintf( '(.?.+?)/%1$s/%2$s/?([0-9]{1,})/?$', urldecode( $tab['endpoint'] ), $wp_rewrite->pagination_base );
				$redirect = sprintf( 'index.php?pagename=$matches[1]&%s=$matches[3]&paged=$matches[2]', $id );
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
	 * @since 3.6.0
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
			if ( isset( $_GET[ $var ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}

	/**
	 * Sets the WP_Query variables for "post_type" on LifterLMS custom taxonomy archive pages for Courses and Memberships.
	 *
	 * @since 1.4.4 Moved from LLMS_Post_Types.
	 * @since 3.16.8
	 * @since 3.33.0 Added `post_title` as a secondary sort when the primary sort is `menu_order`
	 * @since 3.36.3 Changed `pre_get_posts` callback from `10 (default) to `15`,
	 *               so to avoid conflicts with the Divi theme whose callback runs at `10`,
	 *               but since themes are loaded after plugins it overrode our one.
	 * @since 3.36.4 Don't remove this callback from within the callback itself.
	 *               Rather use a static variable to make sure the business logic of this
	 *               method is executed only once.
	 *
	 * @param WP_Query $query Main WP_Query Object.
	 * @return void
	 */
	public function pre_get_posts( $query ) {

		static $done      = false;
		$modify_tax_query = false;

		if ( $done ) {
			return;
		}

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

			// Do it once.
			$done = true;

		}

		if ( $modify_tax_query ) {

			$query->set( 'tax_query', $this->get_tax_query( $query->get( 'tax_query' ) ) );

		}
	}

	/**
	 * Serve a 404 for certificates that are not viewable by the current user
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function maybe_404_certificate() {

		if ( 'llms_my_certificate' === get_post_type() ) {
			$cert = new LLMS_User_Certificate( get_the_ID() );
			if ( ! $cert->can_user_view() ) {

				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();

				// set_404() keeps is_feed and the posts do_feed() would print.
				if ( $wp_query->is_feed() ) {
					$wp_query->is_feed       = false;
					$wp_query->posts         = array();
					$wp_query->post_count    = 0;
					$wp_query->found_posts   = 0;
					$wp_query->max_num_pages = 0;
				}
			}
		}
	}

	/**
	 * Keep an awarded certificate out of embeds unless the current user can view it.
	 *
	 * `maybe_404_certificate()` runs on `wp`. oEmbed is a REST request and never gets there.
	 * `is_post_embeddable` exists since WordPress 6.8; older versions rely on `maybe_hide_certificate_oembed()`.
	 *
	 * @since 10.3.1
	 *
	 * @param bool    $is_embeddable Whether the post is embeddable.
	 * @param WP_Post $post          Post object.
	 * @return bool
	 */
	public function maybe_disable_certificate_embed( $is_embeddable, $post ) {

		if ( $this->is_unviewable_awarded_certificate( $post ) ) {
			return false;
		}

		return $is_embeddable;
	}

	/**
	 * Remove oEmbed data for an awarded certificate the current user cannot view.
	 *
	 * Runs after WordPress adds the embed HTML. A false value becomes a 404 from the oEmbed endpoint.
	 *
	 * @since 10.3.1
	 *
	 * @param array|false $data Response data.
	 * @param WP_Post     $post Post object.
	 * @return array|false
	 */
	public function maybe_hide_certificate_oembed( $data, $post ) {

		if ( $this->is_unviewable_awarded_certificate( $post ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * Stop a canonical redirect to an awarded certificate the current user cannot view.
	 *
	 * `redirect_canonical()` runs on `template_redirect`, after `maybe_404_certificate()` has already
	 * set a 404, and still redirects a public post to its permalink.
	 *
	 * @since 10.3.1
	 *
	 * @param string|false $redirect_url  Redirect URL.
	 * @param string       $requested_url Requested URL.
	 * @return string|false
	 */
	public function maybe_prevent_certificate_canonical_redirect( $redirect_url, $requested_url ) {

		if ( ! $redirect_url ) {
			return $redirect_url;
		}

		$post_ids = array(
			url_to_postid( $redirect_url ),
			url_to_postid( $requested_url ),
			(int) get_query_var( 'p' ),
			(int) get_query_var( 'page_id' ),
			(int) get_query_var( 'attachment_id' ),
		);

		foreach ( array_unique( array_filter( $post_ids ) ) as $post_id ) {
			if ( $this->is_unviewable_awarded_certificate( $post_id ) ) {
				return false;
			}
		}

		return $redirect_url;
	}

	/**
	 * Whether the post is an awarded certificate the current user cannot view.
	 *
	 * @since 10.3.1
	 *
	 * @param int|WP_Post|null $post Post ID or object.
	 * @return bool
	 */
	private function is_unviewable_awarded_certificate( $post ) {

		$post = get_post( $post );
		if ( ! $post instanceof WP_Post || 'llms_my_certificate' !== $post->post_type ) {
			return false;
		}

		return ! ( new LLMS_User_Certificate( $post->ID ) )->can_user_view();
	}

	/**
	 * Drop restricted LifterLMS post types from feed queries.
	 *
	 * These types are publicly queryable so their permalinks work, which also makes them
	 * appear in list feeds. Feeds are commonly page or object cached with no per-user key,
	 * so a check against the current user cannot be trusted here. The content is only
	 * viewable on its own URL.
	 *
	 * @since 10.3.0
	 *
	 * @param WP_Post[] $posts Array of post objects.
	 * @param WP_Query  $query Query object.
	 * @return WP_Post[]
	 */
	public function exclude_protected_posts_from_feeds( $posts, $query ) {

		if ( is_admin() || ! $query instanceof WP_Query || ! $query->is_feed() || ! $posts ) {
			return $posts;
		}

		$filtered = array();

		foreach ( $posts as $post ) {
			if ( $post instanceof WP_Post && in_array( $post->post_type, self::get_feed_excluded_post_types(), true ) ) {
				continue;
			}
			$filtered[] = $post;
		}

		return $filtered;
	}

	/**
	 * Retrieve post types that are never output in feeds.
	 *
	 * Courses and memberships are not listed: their sales page description is public.
	 *
	 * @since 10.3.0
	 *
	 * @return string[]
	 */
	public static function get_feed_excluded_post_types() {
		/**
		 * Filters the post types that are never output in feeds.
		 *
		 * @since 10.3.0
		 *
		 * @param string[] $post_types Post type names.
		 */
		return apply_filters(
			'llms_feed_excluded_post_types',
			array(
				'lesson',
				'llms_quiz',
				'llms_certificate',
				'llms_my_certificate',
			)
		);
	}

	/**
	 * Redirect requests to old llms_my_certificate URLs to the new url.
	 *
	 * Redirects `/my_certificate/slug` to `/certificate/slug` maintaining
	 * translations.
	 *
	 * This will only redirect if `$wp_query` detects a 404 and a certificate
	 * exists with the parsed slug. This check is important to prevent against
	 * collisions which are theoretically possible, though probably unlikely.
	 *
	 * @since 6.0.0
	 * @since 7.5.0 Fixed passing null to parameter #1 ($haystack) using `strpos`.
	 *
	 * @return void
	 */
	public function maybe_redirect_certificate() {

		global $wp, $wp_query;

		$old  = sprintf( '/%s/', _x( 'my_certificate', 'slug', 'lifterlms' ) );
		$path = wp_parse_url( home_url( $wp->request ), PHP_URL_PATH );
		if ( $wp_query->is_404() && $path && 0 === strpos( $path, $old ) ) {
			$slug     = str_replace( $old, '', $path );
			$new_post = get_page_by_path( $slug, 'OBJECT', 'llms_my_certificate' );
			if ( $new_post ) {
				llms_redirect_and_exit( get_permalink( $new_post->ID ) );
			}
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
	 * Avoid showing hidden products in the previous/next post queries.
	 *
	 * @since 9.0.0
	 * @param $where
	 *
	 * @return string
	 */
	public function exclude_hidden_llms_products( $where ) {
		global $wpdb;

		$where .= " AND p.ID NOT IN (
		SELECT object_id
		FROM {$wpdb->term_relationships} AS tr
		INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
		INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
		WHERE tt.taxonomy = 'llms_product_visibility'
		AND t.slug IN ('hidden')
	)";

		return $where;
	}
}

return new LLMS_Query();
