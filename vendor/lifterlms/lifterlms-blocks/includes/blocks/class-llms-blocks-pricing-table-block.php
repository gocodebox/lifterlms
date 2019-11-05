<?php
/**
 * Course pricing table block.
 *
 * @package  LifterLMS_Blocks/Blocks
 * @since    1.0.0
 * @version  1.3.7
 *
 * @render_hook llms_pricing-table-block_render
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course syllabus block class.
 */
class LLMS_Blocks_Pricing_Table_Block extends LLMS_Blocks_Abstract_Block {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'pricing-table';

	/**
	 * Is block dynamic (rendered in PHP).
	 *
	 * @var bool
	 */
	protected $is_dynamic = true;

	/**
	 * Add actions attached to the render function action.
	 *
	 * @param   array  $attributes Optional. Block attributes. Default empty array.
	 * @param   string $content    Optional. Block content. Default empty string.
	 * @return  void
	 * @since   1.0.0
	 * @version 1.1.0
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {

		add_action( $this->get_render_hook(), array( $this, 'output' ), 10 );

	}

	/**
	 * Retrieve custom block attributes.
	 * Necessary to override when creating ServerSideRender blocks.
	 *
	 * @return  array
	 * @since   1.0.0
	 * @version 1.3.6
	 */
	public function get_attributes() {
		return array_merge(
			parent::get_attributes(),
			array(
				'post_id' => array(
					'type'    => 'int',
					'default' => 0,
				),
			)
		);
	}

	/**
	 * Output the pricing table.
	 *
	 * @param   array $attributes Optional. Block attributes. Default empty array.
	 * @return  void
	 * @since   1.0.0
	 * @version 1.3.7
	 */
	public function output( $attributes = array() ) {

		if ( 'edit' === filter_input( INPUT_GET, 'context' ) ) {
			$id = filter_input( INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT );
			if ( $id ) {
				$product = new LLMS_Product( $id );
				if ( ! $product->get_access_plans() ) {
					echo '<p>' . __( 'No access plans found.', 'lifterlms' ) . '</p>';
				}
			}

			// force display of the table on the admin panel.
			add_filter( 'llms_product_pricing_table_enrollment_status', '__return_false' );
			add_filter( 'llms_product_is_purchasable', '__return_true' );

		}

		lifterlms_template_pricing_table( $attributes['post_id'] );

		remove_filter( 'llms_product_pricing_table_enrollment_status', '__return_false' );
		remove_filter( 'llms_product_is_purchasable', '__return_true' );

	}
}

return new LLMS_Blocks_Pricing_Table_Block();
