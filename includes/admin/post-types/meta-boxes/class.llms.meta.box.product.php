<?php
defined( 'ABSPATH' ) || exit;

/**
* Meta Box Product info
* @since    1.0.0
* @version  3.23.0
*/
class LLMS_Meta_Box_Product extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-product';
		$this->title = __( 'Product Options', 'lifterlms' );
		$this->screens = array(
			'course',
			'llms_membership',
		);
		$this->priority = 'high';

		// output PHP variables for JS access
		add_action( 'admin_print_footer_scripts', array( $this, 'localize_js' ) );

	}

	/**
	 * Return an empty array because the metabox fields here are completely custom
	 * @return array
	 * @since  3.0.0
	 */
	public function get_fields() {
		return array();
	}

	public function localize_js() {
		$p = new LLMS_Product( $this->post );
		$limit = $p->get_access_plan_limit();
		echo '<script>window.llms = window.llms || {}; window.llms.product = { access_plan_limit: ' . $limit . ' };</script>';
	}

	/**
	 * Filter the available buttons in the Plan Description editors
	 * @param  array  $buttons array of default butotns
	 * @param  [type] $id      [description]
	 * @return [type]          [description]
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
	 * Overwrites abstract because of the requirments of the UI
	 * @return void
	 * @since  3.0.0
	 */
	public function output() {

		ob_start();

		$gateways = LLMS()->payment_gateways();
		$product = new LLMS_Product( $this->post );

		add_filter( 'teeny_mce_buttons', array( $this, 'mce_buttons' ), 10, 2 );

		$course = ( 'course' === $product->get( 'type' ) ) ? new LLMS_Course( $product->post ) : false;

		llms_get_template( 'admin/post-types/product.php', array(
			'course' => $course,
			'gateways' => $gateways,
			'product' => $product,
		) );

		remove_filter( 'teeny_mce_buttons', array( $this, 'mce_buttons' ), 10, 2 );

		$html = ob_get_clean();

		echo apply_filters( 'llms_metabox_product_output', $html, $this );

	}

	/**
	 * Save product information to db
	 * @param    int     $post_id  ID of the post
	 * @return   void
	 * @since    1.0.0
	 * @version  3.23.0
	 */
	public function save( $post_id ) {

		if ( ! isset( $_POST[ $this->prefix . 'plans' ] ) ) {
			return;
		}

		$plans = $_POST[ $this->prefix . 'plans' ];

		if ( ! is_array( $plans ) ) {

			$this->add_error( __( 'Access Plan data was posted in an invalid format', 'lifterlms' ) );

		}

		foreach ( $plans as $data ) {

			$data = apply_filters( 'llms_access_before_save_plan', $data, $this );

			// required fields
			if ( empty( $data['title'] ) ) {
				$this->add_error( __( 'Access Plan title is required', 'lifterlms' ) );
			}

			if ( empty( $data['price'] ) && ! isset( $data['is_free'] ) ) {
				$this->add_error( __( 'Access Plan price is required', 'lifterlms' ) );
			}

			if ( isset( $data['on_sale'] ) && 'yes' === $data['on_sale'] && empty( $data['sale_price'] ) && '0' !== $data['sale_price'] ) {
				$this->add_error( __( 'Sale price is required if the plan is on sale', 'lifterlms' ) );
			}

			if ( ! empty( $data['trial_offer'] ) && 'yes' === $data['trial_offer'] && empty( $data['trial_price'] ) && '0' !== $data['trial_price'] ) {
				$this->add_error( __( 'Trial price is required if the plan has a trial', 'lifterlms' ) );
			}

			if ( $this->has_errors() ) {
				return;
			}

			if ( empty( $data['id'] ) ) {
				$id = 'new';
				$title = $data['title'];
			} else {
				$id = $data['id'];
				$title = '';
			}

			$plan = new LLMS_Access_Plan( $id, $title );

			$plan->set_visibility( $data['visibility'] );
			$plan->set( 'product_id', $post_id );

			// set some values based on the product being free
			if ( ! empty( $data['is_free'] ) && 'yes' === $data['is_free'] ) {
				$data['price'] = 0;
				$data['frequency'] = 0;
				$data['on_sale'] = 'no';
				$data['sale_price'] = 0;
				$data['trial_offer'] = 'no';
				$data['trial_price'] = 0;
			}

			$props = $plan->get_properties();

			foreach ( $props as $prop => $type ) {

				if ( array_key_exists( $prop, $data ) ) {
					// if the key exists, set it to the submitted value

					$plan->set( $prop, $data[ $prop ] );

				} elseif ( 'yesno' === $type ) {
					// missing yesno field should be set to no

					$plan->set( $prop, 'no' );

				}
			}

			do_action( 'llms_access_plan_saved', $plan, $data, $this );

		}// End foreach().

	}

}
