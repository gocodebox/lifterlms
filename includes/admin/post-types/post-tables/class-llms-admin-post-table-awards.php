<?php
/**
 * LLMS_Admin_Post_Table_Awards class
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 6.0.0
 * @version 6.10.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post table customizations for awarded achievements and certificates.
 *
 * @since 6.0.0
 */
class LLMS_Admin_Post_Table_Awards {

	/**
	 * Array of supported post types.
	 *
	 * @var string[]
	 */
	private $post_types = array(
		'llms_my_achievement',
		'llms_my_certificate',
	);

	/**
	 * Query string variable used when filtering by the post's parent.
	 *
	 * @var string
	 */
	public const TEMPLATE_FILTER_QUERY_VAR = 'llms_filter_template';

	/**
	 * Constructor
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 1, 2 );
		add_filter( 'post_date_column_status', array( $this, 'date_col_status' ), 10, 3 );

		foreach ( $this->post_types as $post_type ) {

			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_cols' ), 10, 1 );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'manage_cols' ), 10, 2 );

			add_filter( "bulk_actions-edit-{$post_type}", array( $this, 'bulk_actions' ) );

			add_filter( "views_edit-{$post_type}", array( $this, 'modify_views' ) );
		}

		add_filter( 'parse_query', array( $this, 'parse_query' ), 10, 1 );
		add_action( 'restrict_manage_posts', array( $this, 'add_filters' ), 10, 2 );
	}

	/**
	 * Add post table columns.
	 *
	 * @since 6.0.0
	 *
	 * @param array $cols Array of post table columns.
	 * @return array
	 */
	public function add_cols( $cols ) {

		$cols = llms_assoc_array_insert( $cols, 'title', 'user', __( 'User', 'lifterlms' ) );
		$cols = llms_assoc_array_insert( $cols, 'user', 'template', __( 'Template', 'lifterlms' ) );

		return $cols;
	}

	/**
	 * Add filters to the top of the post table
	 *
	 * @since 6.0.0
	 *
	 * @param string $post_type Post Type of the current posts table.
	 * @param string $which     Positioning of the filters, either "top" or "bottom".
	 * @return void
	 */
	public function add_filters( $post_type, $which ) {

		if ( 'top' !== $which || ! $this->is_post_type( $post_type ) ) {
			return;
		}

		$template_post_type = str_replace( 'my_', '', $post_type );

		$selected = (int) llms_filter_input( INPUT_GET, self::TEMPLATE_FILTER_QUERY_VAR, FILTER_SANITIZE_NUMBER_INT );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the function.
		echo LLMS_Admin_Post_Tables::get_post_type_filter_html( self::TEMPLATE_FILTER_QUERY_VAR, $template_post_type, $selected );
	}

	/**
	 * Manage bulk actions.
	 *
	 * Changes the language for "Move to trash" to "Delete permanently" since the post type doesn't support trash.
	 *
	 * @since 6.0.0
	 *
	 * @param array $actions Array of bulk actions.
	 * @return array
	 */
	public function bulk_actions( $actions ) {

		if ( ! empty( $actions['trash'] ) ) {
			$actions['trash'] = __( 'Delete Permanently', 'lifterlms' );
		}

		return $actions;
	}

	/**
	 * Modify the post status language.
	 *
	 * Displays "Awarded" in favor of "Published".
	 *
	 * @since 6.0.0
	 *
	 * @param string  $text        Default status text.
	 * @param WP_Post $post        Post object.
	 * @param string  $column_name Column name/id. Hardcoded to `date` in the WP core but
	 *                             passing and checking it anyway in case that changes at
	 *                             some point.
	 * @return string
	 */
	public function date_col_status( $text, $post, $column_name ) {
		if ( 'date' === $column_name && $this->is_post_type( $post->post_type ) && 'publish' === $post->post_status ) {
			return __( 'Awarded', 'lifterlms' );
		}
		return $text;
	}

	/**
	 * Retrieves the post object given the current screen.
	 *
	 * @since 6.0.0
	 * @since 6.4.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 6.10.0 When no INPUT_GET `post_type` variable set, retrieve the post_type from the `$id` (WP_Post ID) parameter.
	 *
	 * @param int     $id       WP_Post ID.
	 * @param boolean $template Whether or not a template is being requested.
	 * @return LLMS_User_Achievement|LLMS_User_Certificate|boolean Returns the object or `false` for invalid post types.
	 */
	private function get_object( $id, $template = false ) {

		$post_type = llms_filter_input( INPUT_GET, 'post_type' );
		$post_type = $post_type ? $post_type : get_post_type( $id );

		if ( 'llms_my_achievement' === $post_type ) {
			return new LLMS_User_Achievement( $id );
		} elseif ( 'llms_my_certificate' === $post_type ) {
			return llms_get_certificate( $id, $template );
		}

		return false;
	}

	/**
	 * Determines if the specified post type is one of the post types affected by this class.
	 *
	 * @since 6.0.0
	 *
	 * @param string $post_type A post type to test.
	 * @return boolean
	 */
	private function is_post_type( $post_type ) {
		return in_array( $post_type, $this->post_types, true );
	}

	/**
	 * Manage content of awarded certificate columns.
	 *
	 * @since 6.0.0
	 *
	 * @param string $column  Column key/name.
	 * @param int    $post_id WP Post ID of the llms_my_certificate for the row.
	 * @return void
	 */
	public function manage_cols( $column, $post_id ) {

		if ( 'template' === $column ) {
			$this->manage_cols_template( $post_id );
		} elseif ( 'user' === $column ) {
			$this->manage_cols_user( $post_id );
		}
	}

	/**
	 * Output the content for a template column.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id WP_Post ID for the row.
	 * @return void
	 */
	private function manage_cols_template( $post_id ) {
		$obj       = $this->get_object( $post_id );
		$parent_id = $obj->get( 'parent' );
		$parent    = $parent_id ? $this->get_object( $parent_id, true ) : false;
		if ( $parent ) {
			printf(
				'<a href="%1$s">%2$s</a>',
				esc_url( get_edit_post_link( $parent_id ) ),
				_draft_or_post_title( $parent_id ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the function.
			);
		} else {
			echo '&mdash;';
		}
	}

	/**
	 * Output the content for a user column.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id WP_Post ID for the row.
	 * @return void
	 */
	private function manage_cols_user( $post_id ) {
		$obj  = $this->get_object( $post_id );
		$uid  = $obj->get_user_id();
		$user = $uid ? llms_get_student( $uid ) : false;
		if ( $user ) {
			$url = LLMS_Admin_Reporting::get_current_tab_url(
				array(
					'student_id' => $uid,
				)
			);
			printf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $url ),
				esc_html( $user->get_name() )
			);
		} else {
			echo '&mdash;';
		}
	}

	/**
	 * Removes the trash link for the post table.
	 *
	 * We intend these post types to be permanently deleted but due to issues with how the block editor handles
	 * moving a post to the trash we cannot disable the trash for these post types. Instead, we allow items to be
	 * moved into the trash and we're hiding the trash link. Items will be deleted automatically via the WP core's
	 * cron and we've updated "Move to trash" language to "Delete permanently". Users will be able to navigate
	 * to the trash page and restore from the trash if they can know/guess the link, and that's okay. I guess.
	 *
	 * @since 6.0.0
	 *
	 * @link https://github.com/WordPress/gutenberg/issues/13024
	 *
	 * @param array $views Array of table views.
	 * @return array
	 */
	public function modify_views( $views ) {
		unset( $views['trash'] );
		return $views;
	}

	/**
	 * Modify the main WP Query.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query $query The WordPress Query.
	 * @return WP_Query
	 */
	public function parse_query( $query ) {

		// Only modify admin & main query.
		if ( ! ( is_admin() && $query->is_main_query() ) ) {
			return $query;
		}

		// Don't proceed if it's not our post type.
		if ( ! isset( $query->query['post_type'] ) || ! $this->is_post_type( $query->query['post_type'] ) ) {
			return $query;
		}

		$template_id = (int) llms_filter_input( INPUT_GET, self::TEMPLATE_FILTER_QUERY_VAR, FILTER_SANITIZE_NUMBER_INT );

		// Don't proceed if no template is being filtered.
		if ( ! $template_id ) {
			return $query;
		}

		$query->set( 'post_parent', $template_id );

		return $query;
	}

	/**
	 * Modify post row actions.
	 *
	 * @since 6.0.0
	 * @since 6.10.0 Added missing textdomain for the 'Move {post_title} to the Trash' string.
	 *
	 * @param array   $actions Existing actions.
	 * @param WP_Post $post    Post object.
	 * @return array
	 */
	public function row_actions( $actions, $post ) {

		if ( $this->is_post_type( $post->post_type ) && ! empty( $actions['trash'] ) ) {

			$actions['trash'] = sprintf(
				'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				get_delete_post_link( $post->ID ),
				// Translators: %s: Post title.
				esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash', 'lifterlms' ), _draft_or_post_title( $post ) ) ),
				__( 'Delete Permanently', 'lifterlms' )
			);

		}

		return $actions;
	}
}

return new LLMS_Admin_Post_Table_Awards();
