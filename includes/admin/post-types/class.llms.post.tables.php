<?php
/**
 * Post Table management for LifterLMS custom post types
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.0.0
 * @version 3.33.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Post_Tables class.
 *
 * @since 3.0.0
 * @since 3.13.0 Unknown.
 * @since 3.33.1 Use `llms_filter_input`
 * @since 3.33.1 Use specific caps (`edit_course`) instead of generic caps (`edit_post`) for exporting and cloning courses.
 */
class LLMS_Admin_Post_Tables {

	/**
	 * Constructor
	 *
	 * @since 3.0.0
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
	 *
	 * @since 3.3.0
	 * @since 3.13.0 Unknown.
	 * @since 3.33.1 Use `edit_course` instead of `edit_post` when checking capabilities.
	 *
	 * @param array   $actions Existing actions.
	 * @param WP_Post $post Post object.
	 * @return string[]
	 */
	public function add_links( $actions, $post ) {

		if ( current_user_can( 'edit_course', $post->ID ) && post_type_supports( $post->post_type, 'llms-clone-post' ) ) {
			$url                   = add_query_arg(
				array(
					'post_type' => $post->post_type,
					'action'    => 'llms-clone-post',
					'post'      => $post->ID,
				),
				admin_url( 'edit.php' )
			);
			$actions['llms-clone'] = '<a href="' . esc_url( $url ) . '">' . __( 'Clone', 'lifterlms' ) . '</a>';
		}

		if ( current_user_can( 'edit_course', $post->ID ) && post_type_supports( $post->post_type, 'llms-export-post' ) ) {
			$url                    = add_query_arg(
				array(
					'post_type' => $post->post_type,
					'action'    => 'llms-export-post',
					'post'      => $post->ID,
				),
				admin_url( 'edit.php' )
			);
			$actions['llms-export'] = '<a href="' . esc_url( $url ) . '">' . __( 'Export', 'lifterlms' ) . '</a>';
		}

		return $actions;

	}

	/**
	 * Handle events for our custom postrow actions
	 *
	 * @since 3.3.0
	 * @since 3.33.1 Use `llms_filter_input` to access `$_GET` and `$_POST` data.
	 * @since 3.33.1 Use `edit_course` cap instead of `edit_post` cap.
	 *
	 * @return void
	 */
	public function handle_link_actions() {

		$action = llms_filter_input( INPUT_GET, 'action' );

		// bail early if request doesn't concern us.
		if ( empty( $action ) ) {
			return;
		}

		// bail early if it isn't a clone/ export request.
		if ( 'llms-clone-post' !== $action && 'llms-export-post' !== $action ) {
			return;
		}

		$post_id = llms_filter_input( INPUT_GET, 'post' );

		// bail if there's no post ID.
		if ( empty( $post_id ) ) {
			wp_die( __( 'Missing post ID.', 'lifterlms' ) );
		}

		$post = get_post( $post_id );

		// bail if post ID is invalid.
		if ( ! $post ) {
			wp_die( __( 'Invalid post ID.', 'lifterlms' ) );
		}

		// bail if the action isn't supported on post type.
		if ( ! post_type_supports( $post->post_type, $action ) ) {
			wp_die( __( 'Action cannot be executed on the current post.', 'lifterlms' ) );
		}

		// bail if user doesn't have permissions.
		if ( ! current_user_can( 'edit_course', $post->ID ) ) {
			wp_die( __( 'You are not authorized to perform this action on the current post.', 'lifterlms' ) );
		}

		$post = llms_get_post( $post );

		// run export or clone action as needed.
		switch ( $action ) {

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

	/**
	 * Get the HTML for a post type select2 filter
	 *
	 * @since 3.12.0
	 *
	 * @param string $name Name of the select element.
	 * @param string $post_type Post type to search by.
	 * @param int[]  $selected Array of POST IDs to use for the pre-selected options on page load.
	 * @return string
	 */
	public static function get_post_type_filter_html( $name, $post_type = 'course', $selected = array() ) {
		$obj   = get_post_type_object( $post_type );
		$label = $obj->labels->singular_name;
		ob_start();
		?>
		<span class="llms-post-table-post-filter">
			<label for="<?php printf( 'filter-by-llms-post-%s', $post_type ); ?>" class="screen-reader-text">
				<?php printf( esc_html__( 'Filter by %s', 'lifterlms' ), $label ); ?>
			</label>
			<select class="llms-select2-post" data-allow_clear="true" data-placeholder="<?php printf( esc_attr__( 'Filter by %s', 'lifterlms' ), $label ); ?>" data-post-type="<?php echo $post_type; ?>" name="<?php echo $name; ?>" id="<?php printf( 'filter-by-llms-post-%s', $post_type ); ?>">
				<?php if ( $selected ) : ?>
					<?php foreach ( llms_make_select2_post_array( $selected ) as $data ) : ?>
						<option value="<?php echo $data['key']; ?>"><?php echo $data['title']; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</span>
		<?php
		return ob_get_clean();
	}


}
return new LLMS_Admin_Post_Tables();
