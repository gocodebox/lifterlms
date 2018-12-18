<?php
/**
 * Manage block visibilty options.
 *
 * @package  LifterLMS_Blocks/Classes
 * @since    1.0.0
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Blocks_Visibility class.
 */
class LLMS_Blocks_Visibility {

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function __construct() {

		add_filter( 'render_block', array( $this, 'maybe_filter_block' ), 10, 2 );

	}

	/**
	 * Retrieve visibility attributes.
	 * Used when registering dynamic blocks via PHP.
	 *
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
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
	 * @param   int    $uid  WP_User ID.
	 * @param   string $type post type.
	 * @return  int
	 * @since   1.0.0
	 * @version 1.0.0
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
	 * Parse post ids from block visibility in attrs.
	 *
	 * @param   array $attrs block attrs.
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
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
	 * @param   string $content block inner content.
	 * @param   array  $block   block info.
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_filter_block( $content, $block ) {

		// No attributes or no llms visibility settings (visibile to "all").
		if ( empty( $block['attrs'] ) || empty( $block['attrs']['llms_visibility'] ) ) {
			return $content;
		}

		$uid = get_current_user_id();

		// Enrolled checks.
		if ( 'enrolled' === $block['attrs']['llms_visibility'] && ! empty( $block['attrs']['llms_visibility_in'] ) ) {

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

		return apply_filters( 'llms_blocks_visibility_render_block', $content, $block );

	}


}

return new LLMS_Blocks_Visibility();
