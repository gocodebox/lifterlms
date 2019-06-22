<?php
defined( 'ABSPATH' ) || exit;

// only load when module is active
if ( LLMS_CERTIFICATE_BUILDER !== true ) {
	exit;
}

/**
 * @since    [version]
 * @version  [version]
 */
class LLMS_Certificate_Editor {

	/**
	 * Constructor
	 *
	 * Hooks editor related modifications to Certificate post type.
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct() {

		// hook build link to posts table.
		add_filter( 'post_row_actions', array( $this, 'build_action' ), 10, 2 );

		// hook editor overlay.
		add_action( 'current_screen', array( $this, 'maybe_overlay_editor' ) );

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
		$build_url = add_query_arg( array( 'llms_certificate_build_mode' => true ), get_permalink( $post->ID ) );

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
		if ( $post->post_type === 'llms_certificate' && current_user_can( 'edit_post', $post->ID ) ) {

			// Get the build url.
			$build_url = $this->build_url( $post );

			// prepend build url to post actions.
			$actions = array( 'build' => sprintf( '<a href="%1$s">%2$s</a>', $build_url, __( 'Build', 'lifterlms' ) ) ) + $actions;
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
		if ( !isset( $screen->post_type) ) {
			return;
		}

		// only load for certificates.
		if ( $screen->post_type != 'llms_certificate' ) {
			return;
		}

		// add a build button alongside the Add Media button.
		add_action( 'media_buttons', array( $this, 'builder_button') );

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

		?>
		<div class="llms-editor-overlay">
			<div class="llms-editor-overlay-content">
				<p><?php _e( "LifterLMS's Builder is active on this certificate." ); ?></p>
				<a href="#" class="button button-secondary llms-certificate-switch">
					<?php _e("Switch to WP Editor"); ?>
				</a>
				<a href="<?php echo $this->build_url( $post ); ?>" class="button button-primary llms-certificate-build">
					<?php _e( 'Launch Builder', 'lifterlms' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue editor assets
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function enqueue_assets() {

		// enqueue editor js.
		//wp_enqueue_script( 'llms-certificate-editor', LLMS_PLUGIN_URL . 'assets/js/llms-certificate-editor' . LLMS_ASSETS_SUFFIX . '.js', array( 'jquery', 'wp-editor' ), '', true );
		wp_enqueue_script( 'llms-certificate-editor', LLMS_PLUGIN_URL . 'assets/js/llms-certificate-editor.js', array( 'jquery', 'wp-editor' ), '', true );

		// enqueue editor css.
		// wp_enqueue_style( 'llms-certificate-editor', LLMS_PLUGIN_URL . 'assets/css/certificate-editor' . LLMS_ASSETS_SUFFIX . '.css' );
		wp_enqueue_style( 'llms-certificate-editor', LLMS_PLUGIN_URL . 'assets/css/certificate-editor.css' );
	}

	public function default_content( $content, $post ) {

		if ( $post->post_type != 'llms_certificate' || ! current_user_can( 'edit_post', $post->ID ) ) {
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
