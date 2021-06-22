<?php
/**
 * LLMS_Admin_Tool_Install_Forms class file
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin tool to reinstall / revert user information forms to their default states
 *
 * @since 5.0.0
 */
class LLMS_Admin_Tool_Install_Forms extends LLMS_Abstract_Admin_Tool {

	/**
	 * Tool ID.
	 *
	 * @var string
	 */
	protected $id = 'install-forms';

	/**
	 * Retrieve a description of the tool
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Restores LifterLMS user information forms and reusable field blocks to their default versions. Caution: any existing form and field customizations will be lost!', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's label
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Reinstall User Forms', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's button text
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Reinstall Forms', 'lifterlms' );
	}

	/**
	 * Retrieves a list of core reusable blocks ordered by their field ID.
	 *
	 * @since 5.0.0
	 *
	 * @return int[] List of the WP_Post IDs.
	 */
	public function get_reusable_blocks() {

		$query = new WP_Query(
			array(
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'post_type'      => 'wp_block',
				'meta_key'       => '_llms_field_id',
				'meta_compare'   => 'EXISTS',
				'orderby'        => 'meta_value',
			)
		);

		return wp_list_pluck( $query->posts, 'ID' );

	}

	/**
	 * Process the tool.
	 *
	 * Deletes all core reusable blocks and then recreates the core forms,
	 * which additionally recreates the core reusable blocks.
	 *
	 * @since 5.0.0
	 *
	 * @return boolean
	 */
	protected function handle() {

		// Retrieve original reusable blocks.
		$original_blocks = $this->get_reusable_blocks();

		// Delete them all.
		foreach ( $original_blocks as $id ) {
			wp_delete_post( $id, true );
		}

		// Recreate the forms (and the blocks).
		LLMS_Forms::instance()->install( true );

		return true;

	}

}

return new LLMS_Admin_Tool_Install_Forms();
