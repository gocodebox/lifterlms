<?php
/**
 * Product Visibity Settings
 * Adds radios to the publishing misc. actions box for courses and memberships
 * @since    3.6.0
 * @version  3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Visibility {

	/**
	 * Constructor
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function __construct() {

		add_action( 'post_submitbox_misc_actions', array( $this, 'output' ) );
		add_action( 'save_post_course', array( $this, 'save' ), 10, 1 );
		add_action( 'save_post_llms_membership', array( $this, 'save' ), 10, 1 );

	}

	/**
	 * Output HTML for the settings
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function output() {

		global $post;

		if ( ! in_array( $post->post_type, array( 'course', 'llms_membership' ) ) ) {
			return;
		}

		$product = new LLMS_Product( $post );
		$visibility = $product->get_catalog_visibility();
		$options = llms_get_product_visibility_options();
		$name = isset( $options[ $visibility ] ) ? $options[ $visibility ] : $visibility;
		?>
		<div class="misc-pub-section" id="llms-catalog-visibility">

		<span style="color:#82878c;" class="dashicons dashicons-welcome-view-site"></span>

			<?php _e( 'Catalog visibility:', 'lifterlms' ); ?> <strong id="llms-catalog-visibility-display"><?php echo $name; ?></strong>

			<a href="#llms-catalog-visibility" class="llms-edit-catalog-visibility hide-if-no-js"><?php _e( 'Edit', 'lifterlms' ); ?></a>

			<div id="llms-catalog-visibility-select" class="hide-if-js">

				<p><?php printf( __( 'Choose the visibility of the %s in your catalog. It will always be available directly.', 'lifterlms' ), $product->get_post_type_label() ); ?></p>
				<?php foreach ( $options as $name => $label ) : ?>
					<input data-label="<?php echo esc_attr( $label ); ?>" id="_llms_visibility_<?php echo esc_attr( $name ); ?>" name="_llms_visibility" type="radio" value="<?php echo esc_attr( $name ); ?>" <?php checked( $visibility, $name ); ?> />
					<label for="_llms_visibility_<?php echo esc_attr( $name ); ?>" class="selectit"><?php echo esc_attr( $label ); ?></label><br>
				<?php endforeach; ?>
				<p>
					<a href="#llms-catalog-visibility" class="llms-save-catalog-visibility hide-if-no-js button"><?php _e( 'OK', 'lifterlms' ); ?></a>
					<a href="#llms-catalog-visibility" class="llms-cancel-catalog-visibility hide-if-no-js"><?php _e( 'Cancel', 'lifterlms' ); ?></a>
				</p>
			</div>
		</div>
		<?php

	}

	/**
	 * Save the settings
	 * @param    int     $post_id  WP Post ID
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function save( $post_id ) {
		if ( ! isset( $_POST['_llms_visibility'] ) ) {
			return;
		}
		$product = new LLMS_Product( $post_id );
		$product->set_catalog_visibility( $_POST['_llms_visibility'] );
	}

}

return new LLMS_Meta_Box_Visibility();
