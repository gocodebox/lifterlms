<?php
/**
* bbPress Integration
* @since    3.0.0
* @version  3.4.3
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Integration_bbPress {

	/**
	 * Integration ID
	 * @var  string
	 */
	public $id = 'bbpress';

	/**
	 * Integration title
	 * @var  string
	 */
	public $title = '';

	/**
	 * Constructor
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct() {

		$this->title = __( 'bbPress', 'lifterlms' );

		if ( $this->is_available() ) {

			add_filter( 'llms_membership_restricted_post_types', array( $this, 'add_membership_restrictions' ) );
			add_filter( 'llms_page_restricted_before_check_access', array( $this, 'topic_restriction_check' ) );

		}

	}

	/**
	 * Add the membership restrictions metabox to bbPress forums on admin panel
	 * @param    array     $post_types    array of existing post types
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function add_membership_restrictions( $post_types ) {
		$post_types[] = bbp_get_forum_post_type();
		return $post_types;
	}

	/**
	 * Determine if the integration is available for use
	 * Must be enabled and the bbPress plugin must be installed & activated
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_available() {
		return ( $this->is_enabled() && $this->is_installed() );
	}

	/**
	 * Determine if the integration is enabled via integration settings
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_enabled() {
		return ( 'yes' === get_option( 'lifterlms_bbpress_enabled', 'no' ) );
	}

	/**
	 * Determine if bbPress is installed and activated
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_installed() {
		return ( class_exists( 'bbPress' ) );
	}

	/**
	 * Check membership restrictions of a form when visitors attempt to access a bbPress Topic
	 * @param    array     $results  array of restriction results
	 * @return   array
	 * @since    3.0.0
	 * @version  3.4.3
	 */
	public function topic_restriction_check( $results ) {

		if ( bbp_is_topic( $results['content_id'] ) ) {

			$forum_id = bbp_get_topic_forum_id( $results['content_id'] );
			$restriction_id = llms_is_post_restricted_by_membership( $forum_id, get_current_user_id() );

			if ( $restriction_id ) {

				$results['restriction_id'] = $restriction_id;
				$results['reason'] = 'membership';

			}

		}

		return $results;
	}

}
