<?php
/**
 * Enqueue assets
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @package LifterLMS_Blocks/Main
 *
 * @since 1.0.0
 * @version 2.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue assets
 *
 * @since 1.0.0
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
	 * @since 2.0.0 Maybe define backwards compatibility script.
	 * @since 2.1.0 Adjust `editor_assets()` priority from 999 to 5.
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
		$this->define_bc();

		// Enqueue editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ), 5 );

	}

	/**
	 * Define block plugin assets.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	private function define() {

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
	 * Define backwards compatibility assets
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	protected function define_bc() {

		if ( ! $this->use_bc_assets() ) {
			return;
		}

		$asset = include LLMS_BLOCKS_PLUGIN_DIR . '/assets/js/llms-blocks-backwards-compat.asset.php';

		$this->assets->define(
			'scripts',
			array(
				'llms-blocks-editor-bc' => array(
					'dependencies' => $asset['dependencies'],
					'file_name'    => 'llms-blocks-backwards-compat',
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
	 * @since 2.0.0 Maybe load backwards compatibility script.
	 * @since 2.2.0 Only load assets on post screens.
	 *
	 * @return void
	 */
	public function editor_assets() {

		$screen = get_current_screen();
		if ( $screen && 'post' !== $screen->base ) {
			return;
		}

		if ( $this->use_bc_assets() ) {
			$this->assets->enqueue_script( 'llms-blocks-editor-bc' );
		}

		$this->assets->enqueue_script( 'llms-blocks-editor' );
		$this->assets->enqueue_style( 'llms-blocks-editor' );

	}

	/**
	 * Determines if WP Core backwards compatibility scripts should defined & be loaded.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	private function use_bc_assets() {
		return ( ! LLMS_Forms::instance()->are_requirements_met() &&
			/**
			 * Filter allowing opt-out of block editor backwards compatibility scripts.
			 *
			 * @since 2.0.0
			 *
			 * @example
			 * ```
			 * // Disable backwards compatibility scripts.
			 * add_filter( 'llms_blocks_load_bc_scripts', '__return_false' );
			 * ```
			 *
			 * @param boolean $load_scripts Whether or not to load the scripts.
			 */
			apply_filters( 'llms_blocks_load_bc_scripts', true )
		);
	}


}

return new LLMS_Blocks_Assets();
