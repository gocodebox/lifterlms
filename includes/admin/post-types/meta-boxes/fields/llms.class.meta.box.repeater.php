<?php
/**
 * Meta box Field: Repeater
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since 3.11.0
 * @version 3.17.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta box Repeater Field class
 *
 * @since 3.11.0
 * @version 3.17.3
 */
class LLMS_Metabox_Repeater_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor
	 *
	 * @param array $_field Array containing information about field
	 * @since    3.11.0
	 * @version  3.11.0
	 */
	public function __construct( $_field ) {

		$button_defaults = array(
			'classes' => '', // Array or space separated string.
			'icon'    => 'dashicons-plus', // dashicon classname or HTML/String.
			'id'      => $_field['id'] . '-add-new',
			'size'    => 'small',
			'style'   => 'primary',
			'text'    => __( 'Add New', 'lifterlms' ),
		);

		if ( empty( $_field['button'] ) ) {
			$_field['button'] = $button_defaults;
		} else {
			$_field['button'] = wp_parse_args( $_field['button'], $button_defaults );
		}

		$this->field = $_field;
	}

	/**
	 * Retrieve the HTML for the repeater add more button
	 *
	 * @return   string
	 * @since    3.11.0
	 * @version  3.11.0
	 */
	private function output_button() {

		$btn = $this->field['button'];

		// Setup class list.
		$classes   = explode( ' ', $btn['classes'] );
		$classes[] = sprintf( 'llms-button-%s', $btn['style'] );
		$classes[] = $btn['size'];
		$classes[] = 'llms-repeater-new-btn';
		$classes   = implode( ' ', $classes );

		// Setup icon.
		if ( $btn['icon'] && 0 === strpos( $btn['icon'], 'dashicons-' ) ) {
			$icon = '<span class="dashicons ' . $btn['icon'] . '"></span>&nbsp;';
		} else {
			$icon = $btn['icon'];
		}

		?>
		<button class="<?php echo esc_attr( $classes ); ?>" type="button"><?php echo wp_kses_post( $icon ) . esc_html( $btn['text'] ); ?></button>
		<?php
	}

	private function output_row( $index ) {

		?>

		<div class="llms-collapsible llms-repeater-row" data-row-order="<?php echo esc_attr( $index ); ?>">

			<header class="llms-collapsible-header">
				<div class="d-2of3">
					<h3 class="llms-repeater-title"><?php echo esc_html( $this->field['header']['default'] ); ?></h3>
				</div>
				<div class="d-1of3 d-right">
					<span class="dashicons dashicons-arrow-down"></span>
					<span class="dashicons dashicons-arrow-up"></span>
					<span class="dashicons dashicons-menu llms-drag-handle"></span>
					<span class="dashicons dashicons-no llms-repeater-remove"></span>
				</div>
			</header>

			<section class="llms-collapsible-body">

				<ul class="llms-mb-repeater-fields">

					<?php foreach ( $this->field['fields'] as $field ) : ?>

						<?php $this->output_sub_field( $field, $index ); ?>

					<?php endforeach; ?>

				</ul>

			</section>

		</div>

		<?php
	}

	/**
	 * Get repeater sub field html output
	 *
	 * @return   string
	 * @since    3.11.0
	 * @version  3.17.3
	 */
	private function output_sub_field( $field, $index ) {

		$field['id'] .= '_' . $index;

		if ( isset( $field['controller'] ) ) {
			$field['controller'] .= '_' . $index;
		}

		$name = ucfirst(
			strtr(
				preg_replace_callback(
					'/(\w+)/',
					function ( $m ) {
						return ucfirst( $m[1] );
					},
					$field['type']
				),
				'-',
				'_'
			)
		);

		$field_class_name = str_replace( '{TOKEN}', $name, 'LLMS_Metabox_{TOKEN}_Field' );
		$field_class      = new $field_class_name( $field );
		$field_class->output();
	}

	/**
	 * Outputs the Html for the given field
	 *
	 * @return   void
	 * @since    3.11.0
	 * @version  3.11.0
	 */
	public function output() {

		global $post;

		parent::output();

		?>
		<div class="llms-repeater-model" id="<?php echo esc_attr( $this->field['id'] ); ?>-model" style="display:none;">
		<?php $this->output_row( 'model' ); ?>
		</div>

		<div class="llms-collapsible-group llms-repeater-rows"></div>

		<footer class="llms-mb-repeater-footer">
		<?php $this->output_button(); ?>
		</footer>

		<input class="llms-repeater-field-handler" type="hidden" value="<?php echo esc_attr( $this->field['handler'] ); ?>">
		<?php
		parent::close_output();
	}
}

