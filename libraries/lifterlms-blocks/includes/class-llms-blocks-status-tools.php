<?php
/**
 * Add Blocks specific LifterLMS Status Page tools.
 *
 * @package  LifterLMS_Blocks/Admin/Classes
 *
 * @since 1.4.0
 * @version 1.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Blocks_Status_Tools class.
 *
 * @since 1.4.0
 */
class LLMS_Blocks_Status_Tools {

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {

		if ( class_exists( 'Classic_Editor' ) ) {

			add_filter( 'llms_status_tools', array( $this, 'add_tools' ) );
			add_action( 'llms_status_tool', array( $this, 'maybe_toggle_mode' ) );

		}

	}

	/**
	 * Add status page tools
	 *
	 * @since 1.4.0
	 *
	 * @param array $tools array of tools.
	 * @return array
	 */
	public function add_tools( $tools ) {

		global $llms_blocks_migrate;
		$posts = $llms_blocks_migrate->get_migrated_posts();

		if ( $posts->found_posts ) {

			$desc  = __( 'Removes block editor code from all courses and lessons which were migrated to the block editor during an upgrade to WordPress 5.0 or later. If you installed the Classic Editor plugin after upgrading and see duplicate content items (such as the course syllabus or lesson mark complete button) this tool will remove the duplicates.', 'lifterlms' );
			$desc .= '<br><br>';
			// Translators: %d = number of affected courses/lessons.
			$desc .= sprintf( __( 'Currently %d courses and/or lessons are affected.', 'lifterlms' ), $posts->found_posts );

			$tools['blocks-unmigrate'] = array(
				'description' => $desc,
				'label'       => __( 'Remove LifterLMS Block Code', 'lifterlms' ),
				'text'        => __( 'Remove Block Code', 'lifterlms' ),
			);

		}

		return $tools;

	}

	/**
	 * Run tool actions on tool page form submission.
	 *
	 * @since 1.4.0
	 *
	 * @param string $tool ID of the tool being run.
	 * @return void
	 */
	public function maybe_toggle_mode( $tool ) {

		if ( 'blocks-unmigrate' !== $tool ) {
			return;
		}

		do_action( 'llms_blocks_unmigrate_posts' );

	}

}

return new LLMS_Blocks_Status_Tools();
