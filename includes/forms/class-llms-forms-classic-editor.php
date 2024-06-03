<?php
/**
 * Disables Classic Editor plugin functionality for forms post types
 *
 * We do not support the classic editor for form building.
 *
 * @package LifterLMS/Classes
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Forms_Classic_Editor
 *
 * @since 5.0.0
 */
class LLMS_Forms_Classic_Editor {

	/**
	 * Static "constructor"
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public static function init() {

		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'force_block_editor' ), 200, 2 );
		add_filter( 'classic_editor_enabled_editors_for_post_type', array( __CLASS__, 'disable_classic_editor' ), 20, 2 );

	}

	/**
	 * Force the block editor to be used for forms post type editing
	 *
	 * The classic editor uses this filter (at priority 100) to disable the block editor
	 * when the default editor for all users is the classic editor and users are not
	 * allowed to switch editors.
	 *
	 * @since 5.0.0
	 *
	 * @link https://developer.wordpress.org/reference/functions/use_block_editor_for_post_type
	 *
	 * @param boolean $use_block_editor Whether or not to use the block editor for the post type.
	 * @param string  $post_type        The post type being checked.
	 * @return boolean
	 */
	public static function force_block_editor( $use_block_editor, $post_type ) {
		return LLMS_Forms::instance()->get_post_type() === $post_type ? true : $use_block_editor;
	}

	/**
	 * Prevent users from being allowed to choose the classic editor for forms post types
	 *
	 * The classic editor uses this filter to determine which editors are available for the given custom
	 * post type when users are allowed to choose which editor to use.
	 *
	 * @since 5.0.0
	 *
	 * @param array  $editors   Associative array. The array key identifies the editor and the array value is a boolean
	 *                          specifying whether or not the editor is enabled for the given post type.
	 * @param string $post_type The post type being checked.
	 * @return array
	 */
	public static function disable_classic_editor( $editors, $post_type ) {
		if ( LLMS_Forms::instance()->get_post_type() === $post_type ) {
			$editors['classic_editor'] = false;
		}
		return $editors;
	}

}

return LLMS_Forms_Classic_Editor::init();
