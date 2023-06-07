<?php
/**
 * LifterLMS Shortcodes Blocks
 *
 * @package LifterLMS/Classes/Shortcodes
 *
 * @since 7.2.0
 * @version 7.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcodes_Blocks class.
 *
 * @since 7.2.0
 */
class LLMS_Shortcodes_Blocks {

	/**
	 * Instance of the class.
	 *
	 * @since 7.2.0
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Get instance of the class.
	 *
	 * @since 7.2.0
	 *
	 * @return self
	 */
	public static function instance(): LLMS_Shortcodes_Blocks {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new LLMS_Shortcodes_Blocks();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 7.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'init', array( __CLASS__, 'add_editor_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_editor_styles' ) );
		add_filter( 'llms_hide_registration_form', array( $this, 'show_form_preview' ) );
		add_filter( 'llms_hide_login_form', array( $this, 'show_form_preview' ) );
	}

	/**
	 * Available shortcode blocks.
	 *
	 * @since 7.2.0
	 *
	 * @return array
	 */
	private function get_config(): array {
		$config = array(
			'access-plan-button'   => array(
				'render' => array( 'LLMS_Shortcodes', 'access_plan_button' ),
			),
			'checkout'             => array(
				'render' => array( 'LLMS_Shortcodes', 'checkout' ),
			),
			'courses'              => array(
				'render' => array( 'LLMS_Shortcode_Courses', 'output' ),
			),
			'course-author'        => array(
				'render' => array( 'LLMS_Shortcode_Course_Author', 'output' ),
			),
			'course-continue'      => array(
				'render' => array( 'LLMS_Shortcode_Course_Continue', 'output' ),
			),
			'course-meta-info'     => array(
				'render' => array( 'LLMS_Shortcode_Course_Meta_Info', 'output' ),
			),
			'course-outline'       => array(
				'render' => array( 'LLMS_Shortcode_Course_Outline', 'output' ),
			),
			'course-prerequisites' => array(
				'render' => array( 'LLMS_Shortcode_Course_Prerequisites', 'output' ),
			),
			'course-reviews'       => array(
				'render' => array( 'LLMS_Shortcode_Course_Reviews', 'output' ),
			),
			'course-syllabus'      => array(
				'render' => array( 'LLMS_Shortcode_Course_Syllabus', 'output' ),
			),
			'login'                => array(
				'render' => array( 'LLMS_Shortcodes', 'login' ),
			),
			'memberships'          => array(
				'render' => array( 'LLMS_Shortcodes', 'memberships' ),
			),
			'my-account'           => array(
				'render' => array( 'LLMS_Shortcodes', 'my_account' ),
			),
			'my-achievements'      => array(
				'render' => array( 'LLMS_Shortcode_My_Achievements', 'output' ),
			),
			'registration'         => array(
				'render' => array( 'LLMS_Shortcode_Registration', 'output' ),
			),
		);

		/**
		 * Filters shortcode blocks config.
		 *
		 * @since 7.2.0
		 *
		 * @param array $config Array of shortcode blocks.
		 */
		return apply_filters( 'llms_shortcode_blocks', $config );
	}

	/**
	 * Registers shortcode blocks.
	 *
	 * @since 7.2.0
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		$blocks = $this->get_config();

		foreach ( $blocks as $name => $args ) {
			$block_dir = $args['path'] ?? LLMS_PLUGIN_DIR . "blocks/$name";

			if ( file_exists( "$block_dir/block.json" ) ) {
				register_block_type(
					$block_dir,
					array(
						'render_callback' => array( $this, 'render_block' ),
					)
				);
			}
		}
	}

	/**
	 * Loads front end CSS in the editor.
	 *
	 * @since 7.2.0
	 *
	 * @return void
	 */
	public static function add_editor_styles(): void {
		$plugins_dir = basename( WP_PLUGIN_DIR );
		$plugin_dir  = basename( LLMS_PLUGIN_DIR );
		$rtl         = is_rtl() ? '-rtl' : '';
		$path        = "../../$plugins_dir/$plugin_dir/assets/css/lifterlms{$rtl}.min.css";

		add_editor_style( $path );
	}

	/**
	 * Enqueues editor styles.
	 *
	 * @since 7.2.0
	 *
	 * @return void
	 */
	public static function enqueue_editor_styles(): void {
		if ( ! llms_is_block_editor() ) {
			return;
		}

		$path = '/assets/css/editor.min.css';

		if ( ! file_exists( LLMS()->plugin_path() . $path ) ) {
			return;
		}

		wp_enqueue_style(
			'llms-editor',
			LLMS()->plugin_url() . $path,
			array(),
			filemtime( LLMS()->plugin_path() . $path )
		);
	}

	/**
	 * Determines whether to show the registration and login form in editor preview.
	 *
	 * @since 7.2.0
	 *
	 * @param bool $hide Whether to hide the registration form.
	 * @return bool
	 */
	public function show_form_preview( bool $hide ): bool {
		if ( llms_is_editor_block_rendering() ) {
			$hide = false;
		}

		return $hide;
	}

	/**
	 * Renders a shortcode block.
	 *
	 * @since 7.2.0
	 *
	 * @param array    $attributes The block attributes.
	 * @param string   $content    The block default content.
	 * @param WP_Block $block      The block instance.
	 * @return string
	 */
	public function render_block( array $attributes, string $content, WP_Block $block ): string {
		if ( ! property_exists( $block, 'name' ) ) {
			return '';
		}

		$name   = str_replace( 'llms/', '', $block->name );
		$config = $this->get_config();
		$render = $config[ $name ]['render'] ?? array();

		if ( method_exists( $render[0] ?? '', 'instance' ) ) {
			$render = array( $render[0]::instance(), $render[1] ?? '' );
		}

		if ( ! is_callable( $render ) ) {
			return '';
		}

		$text = $attributes['text'] ?? '';

		unset( $attributes['text'] );

		$args = array( $attributes );

		if ( $text ) {
			$args[] = $text;
		}

		$html = call_user_func( $render, ...$args );

		// This allows emptyResponsePlaceholder to be used when no content is returned.
		if ( ! $html ) {
			return '';
		}

		// Use emptyResponsePlaceholder for Courses block instead of shortcode message.
		if ( false !== strpos( $html, __( 'No products were found matching your selection.', 'lifterlms' ) ) ) {
			return '';
		}

		return sprintf(
			'<div %1$s>%2$s</div>',
			get_block_wrapper_attributes(),
			trim( $html )
		);
	}
}

return LLMS_Shortcodes_Blocks::instance();
