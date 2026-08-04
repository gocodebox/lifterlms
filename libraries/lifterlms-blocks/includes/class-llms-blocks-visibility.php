<?php
/**
 * Manage block visibility options.
 *
 * @package LifterLMS_Blocks/Classes
 *
 * @since 1.0.0
 * @version 2.4.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Blocks_Visibility class.
 *
 * @since 1.0.0
 * @since 1.6.0 Add logic for `logged_in` and `logged_out` block visibility options.
 *               Adjusted priority of `render_block` filter to 20.
 */
class LLMS_Blocks_Visibility {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 1.6.0 Adjusted priority of `render_block` filter to 20.
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'render_block', array( $this, 'maybe_filter_block' ), 20, 2 );
	}

	/**
	 * Retrieve visibility attributes.
	 *
	 * Used when registering dynamic blocks via PHP.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_attributes() {
		return array(
			'llms_visibility'       => array(
				'default' => 'all',
				'type'    => 'string',
			),
			'llms_visibility_in'    => array(
				'default' => '',
				'type'    => 'string',
			),
			'llms_visibility_posts' => array(
				'default' => '[]',
				'type'    => 'string',
			),
		);
	}

	/**
	 * Get the number of enrollments for a user by post type.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $uid  WP_User ID.
	 * @param string $type Post type.
	 * @return int
	 */
	private function get_enrollment_count_by_type( $uid, $type ) {

		$found   = 0;
		$student = llms_get_student( $uid );

		$type = str_replace( 'any_', '', $type );

		if ( 'course' === $type || 'membership' === $type ) {
			$enrollments = $student->get_enrollments( $type, array( 'limit' => 1 ) );
			$found       = $enrollments['found'];
		} elseif ( 'any' === $type ) {
			$found = $this->get_enrollment_count_by_type( $uid, 'course' );
			if ( ! $found ) {
				$found = $this->get_enrollment_count_by_type( $uid, 'membership' );
			}
		}

		return $found;

	}

	/**
	 * Parse post ids from block visibility in attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs Block attributes.
	 * @return array
	 */
	private function get_post_ids_from_block_attributes( $attrs ) {

		$ids = array();
		if ( 'this' === $attrs['llms_visibility_in'] ) {
			$ids[] = get_the_ID();
		} elseif ( ! empty( $attrs['llms_visibility_posts'] ) ) {
			$ids = wp_list_pluck( json_decode( $attrs['llms_visibility_posts'] ), 'id' );
		}

		return $ids;

	}

	/**
	 * Filter block output.
	 *
	 * @since 1.0.0
	 * @since 1.6.0 Add logic for `logged_in` and `logged_out` block visibility options.
	 * @since 2.0.0 Added a conditional prior to checking the block's visibility attributes.
	 * @since 2.4.2 Set the `user_login` field block's visibility to its default 'logged_out' if not set.
	 *
	 * @param string $content Block inner content.
	 * @param array  $block   Block data array.
	 * @return string
	 */
	public function maybe_filter_block( $content, $block ) {

		// Allow conditionally filtering the block based on external context.
		if ( ! $this->should_filter_block( $block ) ) {
			return $content;
		}

		// Set the `user_login` field block's visibility to its default 'logged_out' if not set.
		// The WordPress serializer `getCommentAttributes()` function removes the attribute before being
		// serialized into `post_content` if the attribute can have only one value and it's the default.
		if ( 'llms/form-field-user-login' === $block['blockName'] && empty( $block['attrs']['llms_visibility'] ) ) {
			$block['attrs']['llms_visibility'] = 'logged_out';
		}

		// No attributes or no llms visibility settings (visible to "all").
		if ( empty( $block['attrs'] ) || empty( $block['attrs']['llms_visibility'] ) ) {
			return $content;
		}

		$uid = get_current_user_id();

		// Show only to logged in users.
		if ( 'logged_in' === $block['attrs']['llms_visibility'] && ! $uid ) {

			$content = '';

			// Show only to logged out users.
		} elseif ( 'logged_out' === $block['attrs']['llms_visibility'] && $uid ) {
			$content = '';

			// Enrolled checks.
		} elseif ( 'enrolled' === $block['attrs']['llms_visibility'] && ! empty( $block['attrs']['llms_visibility_in'] ) ) {

			// Don't have to run any further checks if we don't have a user.
			if ( ! $uid ) {

				$content = '';

				// Checks for the "any" conditions.
			} elseif ( in_array( $block['attrs']['llms_visibility_in'], array( 'any', 'any_course', 'any_membership' ), true ) ) {

				$found = $this->get_enrollment_count_by_type( $uid, $block['attrs']['llms_visibility_in'] );
				if ( ! $found ) {
					$content = '';
				}

				// Checks for specifics.
			} elseif ( in_array( $block['attrs']['llms_visibility_in'], array( 'this', 'list_all', 'list_any' ), true ) ) {

				$relation = 'list_any' === $block['attrs']['llms_visibility_in'] ? 'any' : 'all'; // "this" becomes an "all" relationship
				if ( ! llms_is_user_enrolled( $uid, $this->get_post_ids_from_block_attributes( $block['attrs'] ), $relation ) ) {
					$content = '';
				}
			}

			// Not-Enrolled checks.
		} elseif ( 'not_enrolled' === $block['attrs']['llms_visibility'] && ! empty( $block['attrs']['llms_visibility_in'] ) ) {

			// Only need to check logged in users.
			if ( $uid ) {

				// Checks for the "any" conditions.
				if ( in_array( $block['attrs']['llms_visibility_in'], array( 'any', 'any_course', 'any_membership' ), true ) ) {

					$found = $this->get_enrollment_count_by_type( $uid, $block['attrs']['llms_visibility_in'] );
					if ( $found ) {
						$content = '';
					}

					// Checks for specifics.
				} elseif ( in_array( $block['attrs']['llms_visibility_in'], array( 'this', 'list_all', 'list_any' ), true ) ) {

					$relation = 'list_any' === $block['attrs']['llms_visibility_in'] ? 'any' : 'all'; // "this" becomes an "all" relationship
					if ( llms_is_user_enrolled( $uid, $this->get_post_ids_from_block_attributes( $block['attrs'] ), $relation ) ) {
						$content = '';
					}
				}
			}
		}

		/**
		 * Filters a blocks content after it has been run through visibility attribute filters
		 *
		 * @since 1.0.0
		 *
		 * @param string $content The HTML content for a block. May be an empty string if the block should be invisible to the current user.
		 * @param array  $block   Block data array.
		 */
		return apply_filters( 'llms_blocks_visibility_render_block', $content, $block );

	}

	/**
	 * Determine whether or not a block's rendering should be modified by block-level visibility settings
	 *
	 * This method does not determine whether or not the block will be rendered, it only determines whether
	 * or not we should check if it should be rendered.
	 *
	 * This method is primarily used to ensure that LifterLMS core dynamic blocks (pricing table, course syllabus, etc...)
	 * are *always* displayed to creators when editing content within the block editor. This parses data from a block-renderer
	 * WP Core API request.
	 *
	 * @since 2.0.0
	 * @since 2.3.1 Don't use deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @link https://developer.wordpress.org/rest-api/reference/rendered-blocks/
	 *
	 * @param array $block Block data array.
	 * @return boolean If `true`, block filters should be checked, other wise they will be skipped.
	 */
	private function should_filter_block( $block ) {

		// Always filter unless explicitly told not to.
		$should_filter = true;

		if ( llms_is_rest() ) {

			$context = llms_filter_input( INPUT_GET, 'context' );
			$post_id = llms_filter_input( INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT );

			// Always render blocks when a valid user is requesting the block in the edit context.
			if ( 'edit' === $context && $post_id && current_user_can( 'edit_post', $post_id ) ) {
				$should_filter = false;
			}
		}

		/**
		 * Filters whether or not a block's rendering should be modified by block-level visibility settings
		 *
		 * This filter does not determine whether or not the block will be rendered, it only determines whether
		 * or not we should check if it should be rendered.
		 *
		 * @since 2.0.0
		 *
		 * @param boolean $should_filter Whether or not to apply visibility filters.
		 * @param array   $block         Block data array.
		 */
		return apply_filters( 'llms_blocks_visibility_should_filter_block', $should_filter, $block );

	}

}

return new LLMS_Blocks_Visibility();
