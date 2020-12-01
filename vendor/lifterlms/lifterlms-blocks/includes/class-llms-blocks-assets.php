<?php
/**
 * Enqueue assets
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @package LifterLMS_Blocks/Main
 *
 * @since 1.0.0
 * @version 1.10.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue assets
 *
 * @since 1.0.0
 * @since 1.4.1 Fix double slash in asset path; remove invalid frontend css dependency.
 * @since 1.8.0 Update asset paths & remove redundant CSS from frontend.
 * @since 1.10.0 Use the `LLMS_Assets` class to define, register, and enqueue plugin assets.
 */
class LLMS_Blocks_Assets {

	/**
	 * Instances of `LLMS_Assets`
	 *
	 * @var null
	 */
	public $assets;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @since 1.8.0 Stop outputting editor CSS on the frontend.
	 * @since 1.10.0 Load `LLMS_Assets` and define plugin assets.
	 *
	 * @return void
	 */
	public function __construct() {

		// Load an instance of the LLMS_Assets class.
		$this->assets = new LLMS_Assets(
			'llms-blocks',
			array(
				// Base defaults shared by all asset types.
				'base'   => array(
					'base_file' => LLMS_BLOCKS_PLUGIN_FILE,
					'base_url'  => LLMS_BLOCKS_PLUGIN_DIR_URL,
					'version'   => LLMS_BLOCKS_VERSION,
					'suffix'    => '', // Only minified files are distributed.
				),
				// Script specific defaults.
				'script' => array(
					'translate' => true, // All scripts in the blocks plugin are translated.
				),
			)
		);

		// Define plugin assets.
		$this->define();

		// Enqueue editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ), 999 );

	}

	/**
	 * Define block plugin assets.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	protected function define() {

		$asset = include LLMS_BLOCKS_PLUGIN_DIR . '/assets/js/llms-blocks.asset.php';

		$this->assets->define(
			'scripts',
			array(
				'llms-blocks-editor' => array(
					'dependencies' => $asset['dependencies'],
					'file_name'    => 'llms-blocks',
					'version'      => $asset['version'],
				),
			)
		);

		$this->assets->define(
			'styles',
			array(
				'llms-blocks-editor' => array(
					'dependencies' => array( 'wp-edit-blocks' ),
					'file_name'    => 'llms-blocks',
					'version'      => $asset['version'],
				),
			)
		);

	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 1.0.0
	 * @since 1.4.1 Fix double slash in asset path.
	 * @since 1.8.0 Update asset paths and improve script dependencies.
	 * @since 1.10.0 Use `LLMS_Assets` class methods for asset enqueues.
	 *
	 * @return void
	 */
	public function editor_assets() {

		$this->assets->enqueue_script( 'llms-blocks-editor' );
		$this->assets->enqueue_style( 'llms-blocks-editor' );

	}

}

return new LLMS_Blocks_Assets();
