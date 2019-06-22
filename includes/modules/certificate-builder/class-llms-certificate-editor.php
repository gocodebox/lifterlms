<?php
defined( 'ABSPATH' ) || exit;

// only load when module is active.
if ( LLMS_CERTIFICATE_BUILDER !== true ) {
	exit;
}

/**
 * Handles post editor & post table modifications.
 *
 * @since    [version]
 * @version  [version]
 */
class LLMS_Certificate_Editor {

	/**
	 * All available overlay configurations.
	 *
	 * @var array $overlay_configs
	 */
	private $overlay_configs = array();

	/**
	 * Current overlay configuration.
	 *
	 * @var array $current_overlay_configs
	 */
	private $current_overlay_config = array();

	/**
	 * Constructor
	 *
	 * Hooks editor related modifications to Certificate post type.
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct() {

		$overlay_config = array(
			'add' => array(
				'title' => __( 'New certificates must be saved before using the builder', 'lifterlms' ),
				'buttons' => array(
					array(
						'class' => array( 'llms-certificate-switch', 'button-secondary' ),
						'text' => __( 'Switch to WP Editor', 'lifterlms' ),
					),
					array(
						'class' => array( 'llms-certificate-save', 'button-primary' ),
						'text' => __( 'Save Draft & Launch Builder', 'lifterlms' ),
					),
				),
			),
			'edit' => array(
				'title' => __( "LifterLMS's Builder is active on this certificate.", 'lifterlms' ),
				'buttons' => array(
					array(
						'class' => array( 'llms-certificate-switch', 'button-secondary' ),
						'text' => __( 'Switch to WP Editor', 'lifterlms' ),
					),
					array(
						'class' => array( 'llms-certificate-build', 'button-primary' ),
						'text' => __( 'Launch Builder', 'lifterlms' ),
					),
			),
			),
			'legacy' => array(
				'title' => __( 'Legacy certificate detected', 'lifterlms' ),
				'buttons' => array(
					array(
						'class' => array( 'llms-certificate-switch', 'button-secondary' ),
						'text' => __( 'Continue using WP Editor', 'lifterlms' ),
					),
					array(
						'class' => array( 'llms-certificate-migrate', 'button-primary' ),
						'text' => __( 'Migrate to Builder', 'lifterlms' ),
					),
				),
			),
		);

		/**
		 * Filters all available overlay configurations
		 *
		 * @since    [version]
		 * @version  [version]
		 */
		$this->overlay_configs = apply_filters( 'llms_certificate_editor_overlay_configurations', $overlay_configs );

		// hook build link to posts table.
		add_filter( 'post_row_actions', array( $this, 'build_action' ), 10, 2 );

		// hook editor overlay.
		add_action( 'current_screen', array( $this, 'maybe_overlay_editor' ) );

		// set default post content for new certificates.
		add_filter( 'default_content', array( $this, 'default_content' ), 10, 2 );

	}

	/**
	 * Generates builder url.
	 *
	 * @param WP_Post|bool $post Post Object
	 * @return   string
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function build_url( $post = false ) {

		// make sure we have the current post.
		if ( empty( $post ) ) {
			global $post;
		}

		// add build mode parameter to post permalink.
		$build_url = add_query_arg(
			array(
				LLMS_CERTIFICATE_BUILD_MODE_PARAMETER => true,
			),
			get_permalink( $post->ID )
		);

		return $build_url;
	}

	/**
	 * Adds builder link to post actions.
	 *
	 * @param array $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function build_action( $actions, $post ) {

		// Only load for certificates and for appropriate permissions.
		if ( 'llms_certificate' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) {

			// Get the build url.
			$build_url = $this->build_url( $post );

			// Build action.
			$build_action = array(
				'build' => sprintf( '<a href="%1$s">%2$s</a>', $build_url, __( 'Build', 'lifterlms' ) ),
			);

			// prepend build url to post actions.
			$actions = $build_action + $actions;
		}

		return $actions;
	}

	/**
	 * Conditionally overlays WP Editor.
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function maybe_overlay_editor() {

		// get the current admin screen.
		$screen = get_current_screen();

		// if no post type is set, no point doing anything.
		if ( ! isset( $screen->post_type ) ) {
			return;
		}

		// only load for certificates.
		if ( 'llms_certificate' !== $screen->post_type ) {
			return;
		}

		// set default overlay config to edit.
		$this->current_overlay_config = $this->overlay_configs[ 'edit' ];

		// override for add new screen.
		if ( 'add' === $screen->action ){
			$this->current_overlay_config = $this->overlay_configs[ 'add' ];
		}

		// add a build button alongside the Add Media button.
		add_action( 'media_buttons', array( $this, 'builder_button' ) );

		// add overlay markup after the form. this will be moved in DOM by js.
		add_action( 'edit_form_after_editor', array( $this, 'editor_overlay' ) );

		// enqueue js and css
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Generates a builder button.
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function builder_button() {
		?>
		<a class="button llms-certificate-builder-button" href="<?php echo $this->build_url(); ?>">
			<?php _e( 'Launch Builder', 'lifterlms' ); ?>
		</a>
		<?php
	}


	/**
	 * Generates editor overlay markup.
	 *
	 * @param WP_Post Post object
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function editor_overlay( $post ) {

		// override for legacy certificates
		if ( llms_certificate_is_legacy( $post->ID ) ) {
			$this->current_overlay_config = $this->overlay_configs[ 'legacy' ];
		}

		/**
		 * Filters current overlay configuration
		 *
		 * @since    [version]
		 * @version  [version]
		 */
		$current_overlay_config = apply_filters( 'llms_certificate_editor_current_overlay_configuration', $this->current_overlay_config );

		/**
		 * Triggers just before editor overlay markup
		 *
		 * @param WP_Post $post Post object of the certificate
		 *
		 * @since    [version]
		 * @version  [version]
		 */
		do_action( 'llms_certificate_overlay_before', $post );
		?>
		<div class="llms-editor-overlay">

			<?php
				/**
				 * Triggers just after editor overlay wrapper and before overlay content
				 *
				 * @param WP_Post $post Post object of the certificate
				 *
				 * @since    [version]
				 * @version  [version]
				 */
				do_action( 'llms_certificate_overlay_content_before', $post );
			?>

			<div class="llms-editor-overlay-content">
				<p><?php echo $current_overlay_config['title']; ?></p>
				<?php
					foreach ( $current_overlay_config['buttons'] as $button ) {
						$classes = array( 'button' ) + $button['class'];
						?>
						<a href="#" class="<?php echo implode( ' ', $button['class'] ); ?>">
							<?php echo $button['text']; ?>
						</a>
						<?php
					}
				?>
			</div>

			<?php
				/**
				 * Triggers just after overlay content and before editor overlay wrapper's closing div
				 *
				 * @param WP_Post $post Post object of the certificate
				 *
				 * @since    [version]
				 * @version  [version]
				 */
				do_action( 'llms_certificate_overlay_content_after', $post );
			?>

		</div>
		<?php
		/**
		 * Triggers just after editor overlay markup
		 *
		 * @param WP_Post $post Post object of the certificate
		 *
		 * @since    [version]
		 * @version  [version]
		 */
		do_action( 'llms_certificate_overlay_after', $post );
	}

	/**
	 * Enqueue editor assets
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function enqueue_assets() {

		// enqueue editor js.
		wp_enqueue_script( 'llms-certificate-editor', LLMS_PLUGIN_URL . 'assets/js/llms-certificate-editor' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'wp-editor' ), '', true );

		// enqueue editor css.
		wp_enqueue_style( 'llms-certificate-editor', LLMS_PLUGIN_URL . 'assets/css/certificate-editor' . LLMS_ASSETS_SUFFIX . '.css' );
	}

	/**
	 * Generates default content for new certificates
	 *
	 * @param string $content Default new post content
	 * @param WP_Post $post Post Object
	 *
	 * @return string
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function default_content( $content, $post ) {

		if ( 'llms_certificate' !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $content;
		}

		ob_start();
		?>
		<div class="llms-certificate-container">
			<img class="llms-certificate-background" src="<?php echo LLMS_PLUGIN_URL . 'assets/images/optional_certificate.png'; ?>"></p>
			<div class="llms-certificate-content-area">
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

}

return new LLMS_Certificate_Editor();
