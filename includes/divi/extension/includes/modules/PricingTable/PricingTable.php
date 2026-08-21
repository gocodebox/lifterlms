<?php

class LLMS_Divi_Pricing_Table extends ET_Builder_Module {

	public $slug       = 'lifterlms_divi_pricing_table';
	public $vb_support = 'on';
	public $icon_path;

	protected $module_credits = array(
		'module_uri' => 'https://lifterlms.com/',
		'author'     => 'LifterLMS',
		'author_uri' => 'https://lifterlms.com',
	);

	public function init() {
		$this->name      = esc_html__( 'LifterLMS Pricing Table', 'lifterlms' );
		$this->icon_path = LLMS_PLUGIN_DIR . 'assets/images/lifterlms-icon-grey.svg';
	}

	public function get_fields() {
		return array(
			'dummy'          => array(
				'type'             => 'hidden',
				'default'          => '1',
				'option_category'  => 'basic_option',
				'computed_affects' => array( '__preview_html' ),
			),
			'product_id'     => array(
				'label'            => esc_html__( 'Course or Membership', 'lifterlms' ),
				'type'             => 'select',
				'option_category'  => 'basic_option',
				'options'          => $this->get_product_options(),
				'default'          => 'current',
				'computed_affects' => array( '__preview_html' ),
				'description'      => esc_html__( 'Select a course or leave blank to use the current page.', 'lifterlms' ),
			),
			'__preview_html' => array(
				'type'                => 'computed',
				'computed_callback'   => array( 'LLMS_Divi_Pricing_Table', 'get_preview_html' ),
				'computed_depends_on' => array( 'product_id', 'dummy' ),
			),
		);
	}

	private function get_product_options() {
		$options = array(
			'current' => esc_html__( 'Select a course or membership', 'lifterlms' ),
		);

		$products = get_posts(
			array(
				'post_type'      => array( 'course', 'llms_membership' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			)
		);

		foreach ( $products as $product ) {
			$options[ $product->ID ] = $product->post_title;
		}

		return $options;
	}

	static function get_preview_html( $args = array(), $conditional_tags = array(), $current_page = array() ) {
		global $post;
		$product_id = ( ! empty( $args['product_id'] ) && is_numeric( $args['product_id'] ) ) ? $args['product_id'] : $current_page['id'];

		if ( ! $product_id ) {
			return __( 'Cannot show preview. No course found.', 'lifterlms' );
		}

		$current_post = $post;

		$post   = get_post( $product_id );
		$output = do_shortcode( '[lifterlms_pricing_table product="' . absint( $product_id ) . '"]' );
		$post   = $current_post;

		return $output;
	}

	public function render( $attrs, $content, $render_slug ) {
		global $post;

		$product_id = ! empty( $this->props['product_id'] ) ? $this->props['product_id'] : ( $post ? $post->ID : 0 );
		return do_shortcode( '[lifterlms_pricing_table product="' . absint( $product_id ) . '"]' );
	}
}

new LLMS_Divi_Pricing_Table();
