<?php
/**
* Admin Post Types Class
*
* Sets up post type custom messages and includes base metabox class
*
* @author codeBOX
* @project LifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Types {

	/**
	* Constructor
	*
	* Adds functions to actions and sets filter on post_updated_messages
	*/
	public function __construct() {
		add_action( 'admin_init', array( $this, 'include_post_type_metabox_class' ) );
		add_action( 'metabox_init', array( $this, 'meta_metabox_init' ) );
		add_filter( 'post_updated_messages', array( $this, 'llms_post_updated_messages' ) );

		add_filter( 'pre_get_posts', array( $this, 'modify_admin_search' ), 10, 1 );

		add_filter( 'manage_order_posts_columns', array( $this, 'llms_add_order_columns' ), 10, 1 );
		add_action( 'manage_order_posts_custom_column', array( $this, 'llms_manage_order_columns' ), 10, 2 );
		add_filter( 'manage_edit-order_sortable_columns', array( $this, 'llms_order_sortable_columns' ) );
		add_action( 'load-edit.php', array( $this, 'llms_edit_order_load' ) );
		add_filter( 'manage_lesson_posts_columns', array( $this, 'llms_add_lesson_columns' ), 10, 1 );
		add_action( 'manage_lesson_posts_custom_column', array( $this, 'llms_manage_lesson_columns' ), 10, 2 );
		add_filter( 'manage_section_posts_columns', array( $this, 'llms_add_section_columns' ), 10, 1 );
		add_action( 'manage_section_posts_custom_column', array( $this, 'llms_manage_section_columns' ), 10, 2 );
	}

	/**
	* Admin Menu
	*
	* Includes base metabox class
	*
	* @return void
	*/
	public function include_post_type_metabox_class() {
		include( 'post-types/class.llms.meta.boxes.php' );
	}

	/**
	 * Initializes core for metaboxes
	 *
	 * @return void
	 */
	public function meta_metabox_init() {
		include_once( 'llms.class.admin.metabox.php' );
		echo "<h1>Hello I'm here!</h1>";
	}

	/**
	* Customize post type messages.
	*
	* TODO: Tidy up post types array and make a db option. Allow users to customize them.
	*
	* @return array $messages
	*/
	public function llms_post_updated_messages( $messages ) {
		global $post;

		$llms_post_types = array(
			'course'			=> 'Course',
			'section' 			=> 'Section',
			'lesson' 			=> 'Lesson',
			'order'	 			=> 'Order',
			'llms_email'		=> 'Email',
			'llms_email'		=> 'Email',
			'llms_certificate' 	=> 'Certificate',
			'llms_achievement' 	=> 'Achievement',
			'llms_engagement' 	=> 'Engagement',
			'llms_quiz' 		=> 'Quiz',
			'llms_question' 	=> 'Question',
			'llms_coupon'		=> 'Coupon',
		);

		foreach ( $llms_post_types as $type => $title ) {

			$messages[ $type ] = array(
				0 => '',
				1 => sprintf( __( $title . ' updated. <a href="%s">View ' . $title . '</a>', 'lifterlms' ), esc_url( get_permalink( $post->ID ) ) ),
				2 => __( 'Custom field updated.', 'lifterlms' ),
				3 => __( 'Custom field deleted.', 'lifterlms' ),
				4 => __( $title . ' updated.', 'lifterlms' ),
				5 => isset( $_GET['revision'] ) ? sprintf( __( $title .' restored to revision from %s', 'lifterlms' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __( $title . ' published. <a href="%s">View ' . $title .'</a>', 'lifterlms' ), esc_url( get_permalink( $post->ID ) ) ),
				7 => __( $title . ' saved.', 'lifterlms' ),
				8 => sprintf( __( $title . ' submitted. <a target="_blank" href="%s">Preview ' . $title . '</a>', 'lifterlms' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
				9 => sprintf( __( $title . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . $title . '</a>', 'lifterlms' ),
				date_i18n( __( 'M j, Y @ G:i', 'lifterlms' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
				10 => sprintf( __( $title . ' draft updated. <a target="_blank" href="%s">Preview ' . $title . '</a>', 'lifterlms' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
			);

		}

		return $messages;
	}

	/**
	 * Order post. Appends custom columns to post grid
	 *
	 * @param  array $columns [array of columns]
	 *
	 * @return array $columns.
	 */
	public function llms_add_order_columns( $columns ) {
	    $columns = array(
			'cb' => '<input type="checkbox" />',
			'order' => __( 'Order', 'lifterlms' ),
			// 'title' => __( 'Order', 'lifterlms' ),
			'product' => __( 'Product', 'lifterlms' ),
			// 'customer' => __( 'Customer', 'lifterlms' ),
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
	 */
	public function llms_manage_order_columns( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'order' :

				echo '<a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '">';

				printf( _x( '#%d', 'order number display', 'lifterlms' ), $post_id );

				echo '</a> ';

				_e( 'by', 'lifterlms' );

				echo ' ';

				$user_id = get_post_meta( $post_id, '_llms_user_id', true );

				if ( empty( $user_id ) ) {

					_e( 'Unknown', 'lifterlms' );

				} else {

					$user = get_user_by( 'id', $user_id );

					if ( $user ) {

						$name = $user->first_name . ' ' . $user->last_name;

						if ( ' ' === $name ) {

							$name = $user->display_name;

						}

						echo '<a href="' . get_edit_user_link( $user_id ) . '">' . $name . '</a>';
						echo '<br>';
						echo '<a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';

					} else {

						_e( 'Unknown', 'lifterlms' );

					}

				}

			break;

			case 'product' :

				$product_title = get_post_meta( $post_id, '_llms_product_title', true );
				$product_id = get_post_meta( $post_id, '_llms_order_product_id', true );

				if ( empty( $product_title ) ) {
					echo __( 'Unknown', 'lifterlms' );
				} else {
					echo '<a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '">' . $product_title . '</a>';
				}

				break;

			case 'total' :

				$order_total = get_post_meta( $post_id, '_llms_order_total', true );
				$method = get_post_meta( $post_id, '_llms_payment_method', true );

				if ( $method ) {

					$gateways = LLMS()->payment_gateways();
					$g = $gateways->get_gateway_by_id( $method );
					if ( $g ) {

						$method = $g->get_title();

					}

				}

				$total = '';

				if ( empty( $order_total ) ) {

					$total .= __( 'Free', 'lifterlms' );

				} else {

					$total .= sprintf( '%s%0.2f', get_lifterlms_currency_symbol(), $order_total );
					$total .= ' <small>' . sprintf( __( 'via %s', 'lifterlms' ), $method ) . '</small>';

				}

				echo apply_filters( 'lifterlms_order_posts_table_column_total', $total, $post_id );

				break;

			case 'order_date' :

				$order_date = get_post_meta( $post_id, '_llms_order_date', true );

				if ( empty( $order_date ) ) {
					echo __( 'Unknown' );
				} else {
					echo $order_date;
				}

				break;

			default :
				break;
		}
	}

	/**
	 * Order post: Creates array of columns that will be sortable.
	 *
	 * @param  array $columns [Sortable columns]
	 *
	 * @return array $columns
	 */
	public function llms_order_sortable_columns( $columns ) {

		$columns['product'] = 'product';

		return $columns;
	}

	/**
	 * Order post: Adds custom sortable columns to WP request.
	 *
	 * @return void
	 */
	public function llms_edit_order_load() {
		add_filter( 'request', array( $this, 'llms_sort_orders' ) );
	}

	/**
	 * Order post: Applies custom query variables for sorting custom columns.
	 *
	 * @param  array $vars [Post Query Arguments]
	 *
	 * @return array $vars
	 */
	public function llms_sort_orders( $vars ) {

		if ( isset( $vars['post_type'] ) && 'order' == $vars['post_type'] ) {

			if ( isset( $vars['orderby'] ) && 'product' == $vars['orderby'] ) {

				$vars = array_merge(
					$vars,
					array(
						'meta_key' => '_llms_product_title',
						'orderby' => 'meta_value',
					)
				);
			}

		}

		return $vars;
	}

	/**
	 * Lesson post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 */
	public function llms_add_lesson_columns( $columns ) {
	    $columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Lesson Title' ),
		'course' => __( 'Assigned Course' ),
		'section' => __( 'Assigned Section' ),
		'prereq' => __( 'Prerequisite' ),
		'memberships' => __( 'Memberships Required' ),
		'date' => __( 'Date' ),
		);
		return $columns;
	}

	/**
	 * Lesson post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 */
	public function llms_manage_lesson_columns( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'course' :

				$course = get_post_meta( $post_id, '_parent_course', true );
				$edit_link = get_edit_post_link( $course );

				if ( empty( $course ) ) {
					echo ''; } else { 					printf( __( '<a href="%s">%s</a>' ), $edit_link , get_the_title( $course ) ); }

				break;

			case 'section' :

				$section = get_post_meta( $post_id, '_parent_section', true );
				$edit_link = get_edit_post_link( $section );

				if ( empty( $section ) ) {
					echo ''; } else { 					printf( __( '<a href="%s">%s</a>' ), $edit_link, get_the_title( $section ) ); }

				break;

			case 'prereq' :

				$prereq = get_post_meta( $post_id, '_prerequisite', true );
				$edit_link = get_edit_post_link( $prereq );

				if ( empty( $prereq ) ) {
					echo ''; } else { 					printf( __( '<a href="%s">%s</a>' ), $edit_link, get_the_title( $prereq ) ); }

				break;

			case 'memberships' :

				$memberships = llms_get_post_memberships( $post_id );

				if ( empty( $memberships ) ) {
					echo '';
				} else {
					$membership_list = array();
					foreach ($memberships as $key => $value) {

						array_push( $membership_list, get_the_title( $value ) );
					}
					printf( __( '%s ' ), implode( ', ', $membership_list ) );
				}

				break;

			default :
				break;
		}
	}

	/**
	 * Section post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 */
	public function llms_add_section_columns( $columns ) {
	    $columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Lesson Title' ),
		'course' => __( 'Assigned Course' ),
		'date' => __( 'Date' ),
		);
		return $columns;
	}

	/**
	 * Section post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 */
	public function llms_manage_section_columns( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'course' :

				$course = get_post_meta( $post_id, '_parent_course', true );
				$edit_link = get_edit_post_link( $course );
				if ( empty( $course ) ) {
					echo ''; } else { 					printf( __( '<a href="%s">%s</a>' ), $edit_link, get_the_title( $course ) ); }

				break;

			default :
				break;
		}
	}


	/**
	 * Modify the search query for varios post types before retriving posts
	 * @param  obj    $query  WP_Query obj
	 * @return obj
	 *
	 * @since  2.5.0
	 */
	public function modify_admin_search( $query ) {

		// on the admin posts order table
		// allow searching of custom fields
		if ( is_admin() && 'order' === $query->query_vars['post_type'] && ! empty( $query->query_vars['s'] ) ) {

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

return new LLMS_Admin_Post_Types();
