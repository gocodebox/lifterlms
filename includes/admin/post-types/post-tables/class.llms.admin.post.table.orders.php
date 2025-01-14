<?php
/**
 * Add, Customize, and Manage LifterLMS Order Post Type Post Table Columns.
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 3.0.0
 * @version 7.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Post_Table_Orders class.
 *
 * @since 3.0.0
 */
class LLMS_Admin_Post_Table_Orders {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 * @since 3.24.3 Unknown.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'load-edit.php', array( $this, 'edit_load' ) );
		add_filter( 'manage_llms_order_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_order_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
		add_filter( 'manage_edit-llms_order_sortable_columns', array( $this, 'sortable_columns' ) );
		add_filter( 'pre_get_posts', array( $this, 'modify_admin_search' ), 10, 1 );
		add_filter( 'post_row_actions', array( $this, 'modify_actions' ), 10, 2 );
	}

	/**
	 * Order post. Appends custom columns to post grid.
	 *
	 * @since 3.0.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param array $columns Array of columns.
	 * @return array
	 */
	public function add_columns( $columns ) {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'order'          => __( 'Order', 'lifterlms' ),
			'payment_status' => __( 'Payment Status', 'lifterlms' ),
			'access_status'  => __( 'Access Status', 'lifterlms' ),
			'product'        => __( 'Product', 'lifterlms' ),
			'revenue'        => __( 'Revenue', 'lifterlms' ),
			'type'           => __( 'Order Type', 'lifterlms' ),
			'order_date'     => __( 'Date', 'lifterlms' ),
		);

		return $columns;
	}

	/**
	 * Order post: Queries data based on column name.
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 * @since 5.4.0 Inform about deleted products.
	 * @since 7.0.0 Treat the case when the order has no WordPress user associated yet.
	 *
	 * @param string $column  Custom column name.
	 * @param int    $post_id ID of the individual post.
	 * @return void
	 */
	public function manage_columns( $column, $post_id ) {
		global $post;

		$order = new LLMS_Order( $post_id );

		switch ( $column ) {

			case 'order':
				echo '<a href="' . esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ) . '">';
					printf( esc_html_x( '#%d', 'order number display', 'lifterlms' ), esc_html( $post_id ) );
				echo '</a> ';

				esc_html_e( 'by', 'lifterlms' );
				echo ' ';

				if ( llms_parse_bool( $order->get( 'anonymized' ) ) || empty( llms_get_student( $order->get( 'user_id' ) ) ) ) {
					echo esc_html( $order->get_customer_name() );
				} else {
					$edit_user_link = $order->get( 'user_id' ) ? get_edit_user_link( $order->get( 'user_id' ) ) : '';
					echo ! $edit_user_link ? esc_html( $order->get_customer_name() ) . '<br>' : '<a href="' . esc_url( $edit_user_link ) . '">' . esc_html( $order->get_customer_name() ) . '</a><br>';
					echo '<a href="' . esc_url( 'mailto:' . $order->get( 'billing_email' ) ) . '">' . esc_html( $order->get( 'billing_email' ) ) . '</a>';
				}

				break;

			case 'payment_status':
				$status = $order->get( 'status' );
				echo '<span class="llms-status llms-size--large ' . esc_attr( $status ) . ' ">' . esc_html( llms_get_order_status_name( $status ) ) . '</span>';

				break;

			case 'access_status':
				$date = $order->get_access_expiration_date( 'F j, Y' );
				$ts   = strtotime( $date );

				// Timestamp will be false if date is not a date.
				if ( $ts ) {

					if ( $ts < current_time( 'timestamp' ) ) {
						echo esc_html_x( 'Expired:', 'access plan expiration', 'lifterlms' );
					} else {
						echo esc_html_x( 'Expires:', 'access plan expiration', 'lifterlms' );
					}

					echo ' ' . esc_html( $date );

				} else {

					echo esc_html( $date );

				}

				break;

			case 'product':
				if ( llms_get_post( $order->get( 'product_id' ) ) ) {
					echo '<a href="' . esc_url( get_edit_post_link( $order->get( 'product_id' ) ) ) . '">' . esc_html( $order->get( 'product_title' ) ) . '</a>';
				} else {
					echo esc_html__( '[DELETED]', 'lifterlms' ) . ' ' . esc_html( $order->get( 'product_title' ) );
				}
				echo ' (' . esc_html( ucfirst( $order->get( 'product_type' ) ) ) . ')';

				break;

			case 'revenue':
				$grosse = $order->get_revenue( 'grosse' );
				$net    = $order->get_revenue( 'net' );

				if ( $grosse !== $net ) {
					echo '<del>' . wp_kses( llms_price( $grosse ), LLMS_ALLOWED_HTML_PRICES ) . '</del> ';
				}

				echo wp_kses( llms_price( $net ), LLMS_ALLOWED_HTML_PRICES );

				break;

			case 'type':
				if ( $order->is_recurring() ) {
					esc_html_e( 'Recurring', 'lifterlms' );
				} else {
					esc_html_e( 'One-time', 'lifterlms' );
				}

				break;

			case 'order_date':
				echo esc_html( $order->get_date( 'date' ) );

				break;

		}// End switch().
	}

	/**
	 * Order post: Creates array of columns that will be sortable.
	 *
	 * @since 3.0.0
	 *
	 * @param array $columns Array of sortable columns.
	 * @return array
	 */
	public function sortable_columns( $columns ) {

		$columns['order']      = 'order';
		$columns['product']    = 'product';
		$columns['order_date'] = 'order_date';

		return $columns;
	}

	/**
	 * Order post: Adds custom sortable columns to WP request.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function edit_load() {
		add_filter( 'request', array( $this, 'llms_sort_orders' ) );
	}

	/**
	 * Order post: Applies custom query variables for sorting custom columns.
	 *
	 * @since 3.0.0
	 *
	 * @param array $vars Fost query args.
	 * @return array
	 */
	public function llms_sort_orders( $vars ) {

		if ( isset( $vars['post_type'] ) && 'llms_order' == $vars['post_type'] ) {

			if ( isset( $vars['orderby'] ) && 'order' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'orderby' => 'ID',
					)
				);
			} elseif ( isset( $vars['orderby'] ) && 'product' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => '_llms_product_title',
						'orderby'  => 'meta_value',
					)
				);
			} elseif ( isset( $vars['orderby'] ) && 'order_date' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'orderby' => 'date',
					)
				);
			}
		}

		return $vars;
	}

	/**
	 * Modify the actions for the orders.
	 *
	 * @since 3.0.0
	 *
	 * @param array   $actions Existing actions.
	 * @param WP_Post $post    Post object.
	 * @return string[]
	 */
	public function modify_actions( $actions, $post ) {

		if ( 'llms_order' !== $post->post_type ) {
			return $actions;
		}

		unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}


	/**
	 * Modify the search query for various post types before retrieving posts.
	 *
	 * @since 2.5.0
	 * @since 3.24.3 Unknown
	 * @since 3.35.0 Sanitize $_GET data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @param WP_Query $query Query object.
	 * @return WP_Query
	 */
	public function modify_admin_search( $query ) {

		// On the admin posts order table.
		// Allow searching of custom fields.
		if ( is_admin() && ! empty( $query->query_vars['s'] ) && isset( $query->query_vars['post_type'] ) && 'llms_order' === $query->query_vars['post_type'] ) {

			// What we are searching for.
			$term = $query->query_vars['s'];

			// We have to kill this value so that the query actually works.
			$query->query_vars['s'] = '';

			// Add a filter back in so we don't have 'Search results for ""' on the top of the screen.
			// @note we're not super proud of this incredible piece of duct tape.
			add_filter(
				'get_search_query',
				function ( $q ) {
					if ( '' === $q ) {
						return llms_filter_input_sanitize_string( INPUT_GET, 's' );
					}
				}
			);

			if ( is_numeric( $term ) ) {
				$query->query_vars['p'] = trim( intval( $term ) );
				return $query;
			}

			// Search wp_users.
			$user_query = new WP_User_Query(
				array(
					'search'         => '*' . esc_attr( $term ) . '*',
					'search_columns' => array( 'user_login', 'user_url', 'user_email', 'user_nicename', 'display_name' ),
					'fields'         => 'ID',
				)
			);

			// Search wp_usermeta for First and Last names.
			$user_query2 = new WP_User_Query(
				array(
					'fields'     => 'ID',
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'key'     => 'first_name',
							'value'   => $term,
							'compare' => 'LIKE',
						),
						array(
							'key'     => 'last_name',
							'value'   => $term,
							'compare' => 'LIKE',
						),
					),
				)
			);

			$results = wp_parse_id_list( array_merge( (array) $user_query->get_results(), (array) $user_query2->get_results() ) );

			// Add metaquery for the user id.
			$meta_query = array(
				'relation' => 'OR',
				array(
					'key'     => '_llms_user_id',
					'value'   => $results,
					'compare' => 'IN',
				),
			);

			// Set the query.
			$query->set( 'meta_query', $meta_query );

		}

		return $query;
	}
}

return new LLMS_Admin_Post_Table_Orders();
