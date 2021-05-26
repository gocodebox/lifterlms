<?php
/**
 * LLMS_Admin_Tool_Install_Forms class file
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin tool to reinstall / revert user information forms to their default states
 *
 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Reinstall User Forms', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's button text
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Reinstall Forms', 'lifterlms' );
	}

	/**
	 * Delete all core-created reusable blocks
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function delete_reusable_blocks() {

		$blocks = new WP_Query(
			array(
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'post_type'      => 'wp_block',
				'meta_key'       => '_llms_field_id',
				'meta_compare'   => 'EXISTS',
			)
		);

		foreach ( $blocks->posts as $post ) {
			wp_delete_post( $post->ID, true );
		}

	}

	/**
	 * Process the tool.
	 *
	 * Deletes all core reusable blocks and then recreates the core forms,
	 * which additionally recreates the core reusable blocks.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	protected function handle() {

		$this->delete_reusable_blocks();
		LLMS_Forms::instance()->install( true );

		return true;

	}

}

return new LLMS_Admin_Tool_Install_Forms();
