<?php
/**
 * Parent Course (Back to Course) block.
 *
 * @package LifterLMS/Blocks
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parent Course block class.
 *
 * Renders a "Back to: Course Name" link using the existing
 * `lifterlms_template_single_parent_course()` template function.
 *
 * @since [version]
 */
class LLMS_Block_Parent_Course {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'parent-course';

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register the block type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register() {

		$block_dir = LLMS_PLUGIN_DIR . 'blocks/' . $this->id;
		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type(
				$block_dir,
				array(
					'render_callback' => array( $this, 'render_callback' ),
				)
			);
		}
	}

	/**
	 * Renders the block output.
	 *
	 * @since [version]
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block content.
	 * @return string
	 */
	public function render_callback( $attributes = array(), $content = '' ) {

		ob_start();
		lifterlms_template_single_parent_course();
		$html = ob_get_clean();

		if ( ! $html ) {
			return '';
		}

		return $html;
	}
}

return new LLMS_Block_Parent_Course();
