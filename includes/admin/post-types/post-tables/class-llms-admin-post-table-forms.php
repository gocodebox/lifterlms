<?php
/**
 * Add, Customize, and Manage LifterLMS Forms Post Table Columns
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Post_Table_Forms
 *
 * @since 5.0.0
 */
class LLMS_Admin_Post_Table_Forms {

	/**
	 * Constructor
	 *
	 * @since 5.0.0
	 *
	 * @return  void
	 */
	public function __construct() {

		add_filter( 'manage_llms_form_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_filter( 'bulk_actions-edit-llms_form', array( $this, 'manage_bulk_actions' ), 10, 1 );
		add_filter( 'post_row_actions', array( $this, 'manage_post_row_actions' ), 10, 2 );

		add_action( 'manage_llms_form_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

		add_action( 'pre_get_posts', array( 'LLMS_Admin_Post_Table_Forms', 'pre_get_posts' ) );
	}

	/**
	 * Add Custom Columns
	 *
	 * @since 5.0.0
	 *
	 * @param array $columns Array of default columns.
	 * @return array
	 */
	public function add_columns( $columns ) {

		if ( apply_filters( 'llms_forms_disable_post_table_cb', true ) ) {
			unset( $columns['cb'] );
		}

		return llms_assoc_array_insert( $columns, 'title', 'location', __( 'Location', 'lifterlms' ) );
	}

	/**
	 * Manage available bulk actions.
	 *
	 * @since 5.0.0
	 *
	 * @param array $actions Array of actions.
	 * @return array
	 */
	public function manage_bulk_actions( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}

	/**
	 * Manage content of custom columns
	 *
	 * @since 5.0.0
	 *
	 * @param string $column Table column name.
	 * @param int    $post_id WP Post ID of the form for the current row.
	 * @return void
	 */
	public function manage_columns( $column, $post_id ) {

		if ( 'location' === $column ) {
			$locs = LLMS_Forms::instance()->get_locations();
			$loc  = get_post_meta( $post_id, '_llms_form_location', true );

			if ( isset( $locs[ $loc ] ) ) {
				printf( '<strong>%1$s</strong><br><em>%2$s</em>', esc_html( $locs[ $loc ]['name'] ), esc_html( $locs[ $loc ]['description'] ) );
			} else {
				echo esc_html( $loc );
			}
		}
	}


	/**
	 * Manage available bulk actions.
	 *
	 * @since 5.0.0
	 * @since 6.4.0 Use `LLMS_Forms::is_a_core_form()` to determine whether a form is a core form and cannot be deleted.
	 *
	 * @param array $actions Array of actions.
	 * @return array
	 */
	public function manage_post_row_actions( $actions, $post ) {

		if ( 'llms_form' !== $post->post_type ) {
			return $actions;
		}

		// Core forms cannot be deleted.
		if ( LLMS_Forms::instance()->is_a_core_form( $post ) ) {
			unset( $actions['trash'] );
		}

		unset( $actions['inline hide-if-no-js'] );

		$link = get_permalink( $post );
		if ( $link ) {
			$label           = sprintf( esc_attr__( 'View "%s"', 'lifterlms' ), $post->post_title );
			$actions['view'] = sprintf( '<a href="%1$s" rel="bookmark" aria-label="%2$s">%3$s</a>', $link, $label, __( 'View', 'lifterlms' ) );
		}

		return $actions;
	}

	/**
	 * Ensure only core forms are displayed in the forms list.
	 *
	 * @since 5.0.0
	 *
	 * @param WP_Query $query Query object.
	 * @return void
	 */
	public static function pre_get_posts( $query ) {

		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'edit-llms_form' !== $screen->id || ! $query->is_main_query() ) {
			return;
		}

		$query->set( 'meta_key', '_llms_form_is_core' );
		$query->set( 'meta_value', 'yes' );
	}
}
return new LLMS_Admin_Post_Table_Forms();
