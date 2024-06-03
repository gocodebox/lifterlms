<?php
/**
 * LLMS_Forms Data class file
 *
 * @package LifterLMS/Classes
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manage data associated with llms_form posts
 *
 * @since 5.0.0
 */
class LLMS_Forms_Data {

	/**
	 * Reference to the LLMS_Forms instance
	 *
	 * @var LLMS_Forms
	 */
	private $forms = null;

	/**
	 * Constructor
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		$this->forms = LLMS_Forms::instance();

		add_action( "save_post_{$this->forms->get_post_type()}", array( $this, 'save_username_locations' ), 10, 2 );

	}

	/**
	 * Locate a username/user login block within a list of blocks
	 *
	 * Checks into innerBlocks recursively.
	 *
	 * @since 5.0.0
	 *
	 * @param array[] $blocks Array of WP_Block definition arrays.
	 * @return boolean Returns `true` when a username block is found, otherwise returns `false`.
	 */
	private function has_username_block( $blocks ) {

		foreach ( $blocks as $block ) {

			if ( 'llms/form-field-user-login' === $block['blockName'] ) {
				return true;
			} elseif ( $block['innerBlocks'] ) {
				if ( $this->has_username_block( $block['innerBlocks'] ) ) {
					return true;
				}
			}
		}

		return false;

	}

	/**
	 * When saving a form store a form reference in the options table
	 *
	 * This will be used to LLMS_Forms::are_usernames_enabled() to determine
	 * if the site allows login via usernames.
	 *
	 * Callback function for save_post_llms_forms and delete_post hooks.
	 *
	 * @since 5.0.0
	 *
	 * @param int     $post_id ID of the form being saved.
	 * @param WP_Post $post    Form post object.
	 * @return int[] Returns an array of WP_Post IDs representing all the forms where the username block existss.
	 */
	public function save_username_locations( $post_id, $post ) {

		// Load existing locations.
		$locations = get_option( 'llms_forms_username_locations', array() );

		$post_id            = absint( $post_id );
		$blocks             = $this->forms->parse_blocks( $post->post_content );
		$has_username_block = $this->has_username_block( $blocks );

		// Add or remove the location depending on the presence of the block.
		if ( $has_username_block ) {
			$locations[] = $post_id;
		} else {
			$locations = array_diff( $locations, array( $post_id ) );
		}

		$locations = array_unique( $locations );

		// Store it.
		update_option( 'llms_forms_username_locations', $locations );

		return $locations;

	}

}

return new LLMS_Forms_Data();
