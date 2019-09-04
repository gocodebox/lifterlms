<?php
/**
 * Access Plan metabox
 *
 * @since 1.0.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Meta_Box_Product class.
 *
 * @since 1.0.0
 * @since 3.30.0 Added checkout redirect settings
 * @since 3.30.3 Adjusted localization priority to 9.
 */
class LLMS_Meta_Box_Product extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @since 3.0.0
	 * @since 3.30.3 Adjusted localization priority to 9.
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-product';
		$this->title    = __( 'Access Plans', 'lifterlms' );
		$this->screens  = array(
			'course',
			'llms_membership',
		);
		$this->priority = 'high';

		// output PHP variables for JS access
		add_action( 'admin_print_footer_scripts', array( $this, 'localize_js' ), 9 );

	}

	/**
	 * Return an empty array because the metabox fields here are completely custom
	 *
	 * @return array
	 * @since  3.0.0
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Pass settings to JS
	 *
	 * @return  void
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	public function localize_js() {
		$p     = new LLMS_Product( $this->post );
		$limit = $p->get_access_plan_limit();
		echo '<script>window.llms = window.llms || {}; window.llms.product = { access_plan_limit: ' . $limit . ' };</script>';
	}

	/**
	 * Filter the available buttons in the Plan Description editors
	 *
	 * @param  array  $buttons array of default buttons
	 * @param  string $id      editor id
	 * @return array
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	public function mce_buttons( $buttons, $id ) {

		if ( strpos( $id, '_llms_plans_content' ) !== false ) {

			$buttons = array(
				'bold',
				'italic',
				'underline',
				'blockquote',
				'strikethrough',
				'bullist',
				'numlist',
				'alignleft',
				'aligncenter',
				'alignright',
				'undo',
				'redo',
			);

		}

		return $buttons;
	}

	/**
	 * Output metabox content
	 * Overwrites abstract because of the requirements of the UI
	 *
	 * @return void
	 * @since  3.0.0
	 * @version 3.29.0
	 */
	public function output() {
		echo $this->get_html();
	}

	/**
	 * Retrieve the HTML for the metabox
	 *
	 * @return  string
	 * @since   3.29.0
	 * @since   3.30.0 Added checkout redirect settings.
	 * @version 3.30.0
	 */
	public function get_html() {

		ob_start();
		$product = new LLMS_Product( $this->post );
		add_filter( 'teeny_mce_buttons', array( $this, 'mce_buttons' ), 10, 2 );
		$course = ( 'course' === $product->get( 'type' ) ) ? new LLMS_Course( $product->post ) : false;

		// get available checkout redirection types.
		$product_type               = ( ! $course ) ? __( 'Membership', 'lifterlms' ) : __( 'Course', 'lifterlms' );
		$checkout_redirection_types = llms_get_checkout_redirection_types( $product_type );

		include LLMS_PLUGIN_DIR . 'includes/admin/views/access-plans/metabox.php';
		remove_filter( 'teeny_mce_buttons', array( $this, 'mce_buttons' ), 10, 2 );
		return apply_filters( 'llms_metabox_product_output', ob_get_clean(), $this );

	}

}
