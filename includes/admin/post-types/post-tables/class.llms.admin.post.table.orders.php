<?php
/**
 * Add, Customize, and Manage LifterLMS Order Post Type Post Table Columns
 *
 * Some functions were migrated from non-classed functions
 *
 * @since  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Table_Orders {


	/**
	 * Constructor
	 * @return  voide
	 * @since  3.0.0
	 */
	public function __construct() {

		add_action( 'load-edit.php', array( $this, 'edit_load' ) );
		add_filter( 'manage_llms_order_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_order_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
		add_filter( 'manage_edit-llms_order_sortable_columns', array( $this, 'sortable_columns' ) );
		add_filter( 'pre_get_posts', array( $this, 'modify_admin_search' ), 10, 1 );

	}

	/**
	 * Order post. Appends custom columns to post grid
	 *
	 * @param  array $columns [array of columns]
	 *
	 * @return array $columns.
	 * @since  3.0.0
	 */
	public function add_columns( $columns ) {

	    $columns = array(
			'cb' => '<input type="checkbox" />',
			'order' => __( 'Order', 'lifterlms' ),
			'status' => __( 'Status', 'lifterlms' ),
			'product' => __( 'Product', 'lifterlms' ),
			'total' => __( 'Total', 'lifterlms' ),
			'order_date' => __( 'Date', 'lifterlms' ),
		);

		return $columns;
	}

	/**
	 * Order post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 * @since  3.0.0
	 */
	public function manage_columns( $column, $post_id ) {
		global $post;

		$order = new LLMS_Order( $post_id );

		switch ( $column ) {


			case 'order' :

				echo '<a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '">';
					printf( _x( '#%d', 'order number display', 'lifterlms' ), $post_id );
				echo '</a> ';

				_e( 'by', 'lifterlms' );
				echo ' ';

				echo '<a href="' . get_edit_user_link( $order->get_user_id() ) . '">' . $order->get_billing_name() . '</a><br>';
				echo '<a href="mailto:' . $order->get_billing_email() . '">' . $order->get_billing_email() . '</a>';

			break;

			case 'status' :

				$status = $order->get_status();

				switch( $status ) {
					case 'llms-active':
					case 'llms-completed':
						$icon = 'dashicons dashicons-yes';
					break;

					case 'llms-refunded':
						$icon = 'dashicons dashicons-update';
					break;

					case 'llms-cancelled':
					case 'llms-expired':
						$icon = 'dashicons dashicons-warning';
					break;

					case 'llms-failed':
						$icon = 'dashicons dashicons-dismiss';
					break;

					case 'llms-pending':
						$icon = 'dashicons dashicons-clock';
					break;

					default:
						$icon = 'dashicons dashicons-editor-help';
				}

				echo apply_filters( 'lifterlms_order_status_icon', '<span class="llms-order-status-icon ' . $status . ' ' . $icon . '"></span>' );
				echo ' <small>' . llms_get_formatted_order_status( $status ) . '</small>';

			break;


			case 'product' :

				echo '<a href="' . admin_url( 'post.php?post=' . $order->get_product_id() . '&action=edit' ) . '">' . $order->get_product_title() . '</a>';
				echo ' (' . ucfirst( $order->get_product_type() ) . ')';

			break;


			case 'total' :

				switch( $order->get_type() ) {

					case 'recurring':

						printf( __( 'First: %s', 'lifterlms' ), $order->format_price( $order->get_first_payment_total() ) );
						echo '<br>';
						printf( __( 'Recurring: %s', 'lifterlms' ), $order->format_price( $order->get_recurring_payment_total() ) );

					break;


					case 'single':

						echo $order->format_price( $order->get_total() );

					break;

					default:

						_e( 'Free', 'lifterlms' );


				}

				echo ' <small>' . sprintf( _x( 'via %s', 'payment gateway used to complete transaction', 'lifterlms' ), $order->get_payment_gateway_title() ) . '</small>';



				// echo apply_filters( 'lifterlms_order_posts_table_column_total', $total, $post_id );

			break;

			case 'order_date' :

				echo $order->get_date();

			break;

		}
	}

	/**
	 * Order post: Creates array of columns that will be sortable.
	 *
	 * @param  array $columns [Sortable columns]
	 *
	 * @return array $columns
	 * @since  3.0.0
	 */
	public function sortable_columns( $columns ) {

		$columns['order'] = 'order';
		$columns['product'] = 'product';
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
	 * @param  array $vars [Post Query Arguments]
	 *
	 * @return array $vars
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
			}
			// order product
			elseif ( isset( $vars['orderby'] ) && 'product' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => '_llms_product_title',
						'orderby' => 'meta_value',
					)
				);
			}
			// date field
			elseif ( isset( $vars['orderby'] ) && 'order_date' == $vars['orderby'] ) {
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
	 * Modify the search query for varios post types before retriving posts
	 * @param  obj    $query  WP_Query obj
	 * @return obj
	 *
	 * @since  2.5.0  moved from a non-classed function
	 * @version  3.0.0
	 */
	public function modify_admin_search( $query ) {

		// on the admin posts order table
		// allow searching of custom fields
		if ( is_admin() && 'llms_order' === $query->query_vars['post_type'] && ! empty( $query->query_vars['s'] ) ) {

			$s = $query->query_vars['s'];

			// if the term is an email, find orders for the user
			if ( is_email( $s ) ) {

				// get the user obj
				$user = get_user_by( 'email', $s );

				if ( $user ) {

					// add metaquery for the user id
					$metaquery = array(
						'relation' => 'OR',
						array(
							'key' => '_llms_user_id',
							'value' => $user->ID,
							'compare' => '=',
						)
					);

					// we have to kill this value so that the query actually works
					$query->query_vars['s'] = '';

					// set the query
					$query->set( 'meta_query', $metaquery );

					// add a filter back in so we don't have 'Search results for ""' on the top of the screen
					// @note we're not super proud of this incredible piece of duct tape
					add_filter( 'get_search_query', function( $q ) {

						if ( '' === $q ) {

							return $_GET['s'];

						}

					} );

				}

			}

		}

		return $query;

	}

}

return new LLMS_Admin_Post_Table_Orders();
