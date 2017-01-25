<?php
/**
 * General post table management
 *
 * @since  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Tables {

	/**
	 * Constructor
	 *
	 * @since  3.0.0
	 */
	public function __construct() {

		// load all post table classes
		foreach ( glob( LLMS_PLUGIN_DIR . '/includes/admin/post-types/post-tables/*.php' ) as $filename ) {
			include_once $filename;
		}

		add_filter( 'post_row_actions', array( $this, 'add_links' ), 777, 2 );
		add_action( 'admin_init', array( $this, 'handle_link_actions' ) );

	}

	/**
	 * Adds clone links to post types which support lifterlms post cloning
	 * @param    array     $actions  existing actions
	 * @param    obj       $post    WP_Post object
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function add_links( $actions, $post ) {

		if ( post_type_supports( $post->post_type, 'llms-clone-post' ) ) {
			$url = add_query_arg( array(
				'post_type' => $post->post_type,
				'action' => 'llms-clone-post',
				'post' => $post->ID,
			) , admin_url( 'edit.php' ) );
			$actions['llms-clone'] = '<a href="' . esc_url( $url ) . '">' . __( 'Clone', 'lifterlms' ) . '</a>';
		}

		if ( post_type_supports( $post->post_type, 'llms-export-post' ) ) {
			$url = add_query_arg( array(
				'post_type' => $post->post_type,
				'action' => 'llms-export-post',
				'post' => $post->ID,
			) , admin_url( 'edit.php' ) );
			$actions['llms-export'] = '<a href="' . esc_url( $url ) . '">' . __( 'Export', 'lifterlms' ) . '</a>';
		}

		return $actions;

	}

	/**
	 * Handle events for our custom postrow actions
	 * @return   void
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function handle_link_actions() {

		if ( ! isset( $_GET['action'] ) ) {
			return;
		}

		if ( 'llms-clone-post' !== $_GET['action'] && 'llms-export-post' !== $_GET['action'] ) {
			return;
		}

		if ( ! isset( $_GET['post'] ) ) {
			wp_die( __( 'Missing post ID.', 'lifterlms' ) );
		}

		$post = get_post( $_GET['post'] );

		if ( ! $post ) {
			wp_die( __( 'Invalid post ID.', 'lifterlms' ) );
		}

		if ( ! post_type_supports( $post->post_type, $_GET['action'] ) ) {
			wp_die( __( 'Action cannot be executed on the current post.', 'lifterlms' ) );
		}

		$post = llms_get_post( $post );

		switch ( $_GET['action'] ) {

			case 'llms-export-post':
				$post->export();
			break;

			case 'llms-clone-post':
				$r = $post->clone_post();
				if ( is_wp_error( $r ) ) {
					LLMS_Admin_Notices::flash_notice( $r->get_error_message(), 'error' );
				}
				wp_redirect( admin_url( 'edit.php?post_type=' . $post->get( 'type' ) ) );
				exit;
			break;

		}

	}


}
return new LLMS_Admin_Post_Tables();
