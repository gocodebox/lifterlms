<?php
/**
 * Add, Customize, and Manage LifterLMS Order Post Type Post Table Columns.
 *
 * @package  LifterLMS\Admin\Classes
 * @since    3.0.0
 * @version  3.24.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Post_Table_Orders class
 */
class LLMS_Admin_Post_Table_Orders {

	/**
	 * Constructor.
	 *
	 * @return  void
	 * @since   3.0.0
	 * @since   3.24.3
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
	 * Order post. Appends custom columns to post grid/
	 *
	 * @param   array $columns  array of columns
	 * @return  array
	 * @since   3.0.0
	 * @version 3.24.0
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
	 * @param    string $column  custom column name
	 * @param    int    $post_id    ID of the individual post
	 * @return   void
	 * @since    3.0.0
	 * @version  3.19.0
	 */
	public function manage_columns( $column, $post_id ) {
		global $post;

		$order = new LLMS_Order( $post_id );

		switch ( $column ) {

			case 'order':
				echo '<a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '">';
					printf( _x( '#%d', 'order number display', 'lifterlms' ), $post_id );
				echo '</a> ';

				_e( 'by', 'lifterlms' );
				echo ' ';

				if ( 'yes' === $order->get( 'anonymized' ) ) {
					echo $order->get_customer_name();
				} else {
					echo '<a href="' . get_edit_user_link( $order->get( 'user_id' ) ) . '">' . $order->get_customer_name() . '</a><br>';
					echo '<a href="mailto:' . $order->get( 'billing_email' ) . '">' . $order->get( 'billing_email' ) . '</a>';
				}

				break;

			case 'payment_status':
				$status = $order->get( 'status' );
				echo '<span class="llms-status llms-size--large ' . $status . ' ">' . llms_get_order_status_name( $status ) . '</span>';

				break;

			case 'access_status':
				$date = $order->get_access_expiration_date( 'F j, Y' );
				$ts   = strtotime( $date );

				// timestamp will be false if date is not a date
				if ( $ts ) {

					if ( $ts < current_time( 'timestamp' ) ) {
						_ex( 'Expired:', 'access plan expiration', 'lifterlms' );
					} else {
						_ex( 'Expires:', 'access plan expiration', 'lifterlms' );
					}

					echo ' ' . $date;

				} else {

					echo $date;

				}

				break;

			case 'product':
				echo '<a href="' . admin_url( 'post.php?post=' . $order->get( 'product_id' ) . '&action=edit' ) . '">' . $order->get( 'product_title' ) . '</a>';
				echo ' (' . ucfirst( $order->get( 'product_type' ) ) . ')';

				break;

			case 'revenue':
				$grosse = $order->get_revenue( 'grosse' );
				$net    = $order->get_revenue( 'net' );

				if ( $grosse !== $net ) {
					echo '<del>' . llms_price( $grosse ) . '</del> ';
				}

				echo llms_price( $net );

				break;

			case 'type':
				if ( $order->is_recurring() ) {
					_e( 'Recurring', 'lifterlms' );
				} else {
					_e( 'One-time', 'lifterlms' );
				}

				break;

			case 'order_date':
				echo $order->get_date( 'date' );

				break;

		}// End switch().
	}

	/**
	 * Order post: Creates array of columns that will be sortable.
	 *
	 * @param  array $columns  array of sortable columns
	 * @return array $columns
	 * @since  3.0.0
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
	 * @return void
	 * @since  3.0.0
	 */
	public function edit_load() {
		add_filter( 'request', array( $this, 'llms_sort_orders' ) );
	}

	/**
	 * Order post: Applies custom query variables for sorting custom columns.
	 *
	 * @param  array $vars  post query args
	 * @return array
	 * @since  3.0.0
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
	 * @param    array $actions   existing actions
	 * @param    obj   $post      WP_Post Object
	 * @return   string[]
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
	 *
	 * @param    obj $query  WP_Query
	 * @return   obj
	 */
	public function modify_admin_search( $query ) {

		// on the admin posts order table
		// allow searching of custom fields
		if ( is_admin() && ! empty( $query->query_vars['s'] ) && isset( $query->query_vars['post_type'] ) && 'llms_order' === $query->query_vars['post_type'] ) {

			// What we are searching for
			$term = $query->query_vars['s'];

			// Search wp_users
			$user_query = new WP_User_Query(
				array(
					'search'         => '*' . esc_attr( $term ) . '*',
					'search_columns' => array( 'user_login', 'user_url', 'user_email', 'user_nicename', 'display_name' ),
					'fields'         => 'ID',
				)
			);

			// Search wp_usermeta for First and Last names
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

			// add metaquery for the user id
			$meta_query = array(
				'relation' => 'OR',
				array(
					'key'     => '_llms_user_id',
					'value'   => $results,
					'compare' => 'IN',
				),
			);

			// we have to kill this value so that the query actually works
			$query->query_vars['s'] = '';

			// set the query
			$query->set( 'meta_query', $meta_query );

			// add a filter back in so we don't have 'Search results for ""' on the top of the screen
			// @note we're not super proud of this incredible piece of duct tape
			add_filter(
				'get_search_query',
				function( $q ) {
					if ( '' === $q ) {
						return llms_filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
					}
				}
			);

		} // End if().

		return $query;

	}

}

return new LLMS_Admin_Post_Table_Orders();
