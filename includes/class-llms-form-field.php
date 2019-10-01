<?php
/**
 * Setup and render form fields.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Form_Field class..
 *
 * @since [version]
 */
class LLMS_Form_Field {

	protected $html = '';

	public function __construct( $settings = array() ) {

		$this->settings = wp_parse_args( $settings, $this->get_defaults() );
		$this->prepare();

	}

	public function get_html() {

		$html = sprintf( '<div class="%s">', implode( ' ', $this->settings['wrapper_classes'] ) );
		$html .= $this->get_field_html();
		$html .= '</div>';

		// var_dump( $html );

		return apply_filters( 'llms_form_field', $html, $this->settings );

	}

	protected function render() {

		if ( ! $this->html ) {
			$this->get_html();
		}

		echo $this->html;

	}

	protected function get_defaults() {

		return array(
			'columns'         => 12,
			'classes'         => '',
			'description'     => '',
			'default'         => '',
			'disabled'        => false,
			'id'              => '',
			'label'           => '',
			'last_column'     => true,
			'match'           => '',
			'max_length'      => '',
			'min_length'      => '',
			'name'            => '',
			'options'         => array(),
			'placeholder'     => '',
			'required'        => false,
			'selected'        => '',
			'style'           => '',
			'type'            => 'text',
			'value'           => '',
			'wrapper_classes' => array(), // or string of space-separated classes.
		);

	}

	protected function get_field_html() {

		switch ( $this->settings['type'] ) {

			case 'button':
			case 'reset':
			case 'submit':

				$tag = 'button';
				$self_closing = false;
				$classes = array( 'llms-field-button' );
				$inner_html = $this->settings['value'];

				// $r .= '<button class="llms-field-button' . $field['classes'] . '" id="' . $field['id'] . '" type="' . $field['type'] . '"' . $disabled_attr . $name_attr . $field['style'] . '>' . $field['value'] . '</button>';
				break;

			case 'checkbox':
			case 'radio':

				$tag = 'input';
				$self_closing = true;
				$classes = array( 'llms-field-input' );
				$inner_html = '';

				// $checked = ( true === $field['selected'] ) ? ' checked="checked"' : '';
				// $r      .= '<input class="llms-field-input' . $field['classes'] . '" id="' . $field['id'] . '" type="' . $field['type'] . '"' . $checked . $disabled_attr . $name_attr . $required_attr . $value_attr . $field['style'] . '>';
				// $r      .= $label;
				break;

			case 'html':

				$tag = 'div';
				$self_closing = false;
				$classes = array( 'llms-field-html' );
				$inner_html = $this->settings['value'];

				// $r .= '<div class="llms-field-html' . $field['classes'] . '" id="' . $field['id'] . '">' . $field['value'] . '</div>';
				break;

			case 'select':

				$tag = 'select';
				$self_closing = false;
				$classes = array( 'llms-field-select' );

				// $r .= '<select class="llms-field-select' . $field['classes'] . '" id="' . $field['id'] . '" ' . $disabled_attr . $name_attr . $required_attr . $field['style'] . '>';
				// foreach ( $field['options'] as $k => $v ) {
				// 	$r .= '<option value="' . $k . '"' . selected( $k, $field['value'], false ) . '>' . $v . '</option>';
				// }
				// $r .= '</select>';
				break;

			case 'textarea':

				$tag = 'textarea';
				$self_closing = false;
				$classes = array( 'llms-field-textarea' );
				$inner_html = $this->settings['value'];

				// $r .= '<textarea class="llms-field-textarea' . $field['classes'] . '" id="' . $field['id'] . '" placeholder="' . $field['placeholder'] . '"' . $disabled_attr . $name_attr . $required_attr . $field['style'] . '>' . $field['value'] . '</textarea>';
				break;

			default:

				$tag = 'input';
				$self_closing = true;
				$classes = array( 'llms-field-button' );
				$inner_html = '';

				// $r .= '<input class="llms-field-input' . $field['classes'] . '" id="' . $field['id'] . '" placeholder="' . $field['placeholder'] . '" type="' . $field['type'] . '"' . $disabled_attr . $name_attr . $min_attr . $max_attr . $required_attr . $value_attr . $field['style'] . '>';

		}

		$attributes = $this->get_html_attributes();
		$attrs['class'] = $this->prepare_classes( $this->settings['classes'], $classes );

		$attrs = '';
		foreach ( $attributes as $attr => $val ) {
			$attrs .= sprintf( ' %1$s="%2$s"', $attr, $val );
		}

		$open = sprintf( '<%1$s%2$s', $tag, $attrs );
		$close = $self_closing ? ' />' : sprintf( '</%s>', $tag );

		return sprintf( '%1$s%2$s', $open, $close );

	}


	protected function get_html_attributes() {

		$check = array(
			'id',
			'disabled',
			'name',
			'placeholder',
			'style',
			'value',
		);

		$attrs = array();

		foreach ( $check as $attr ) {
			if ( ! empty( $this->settings[ $attr ] ) ) {
				$attrs[ $attr ] = esc_attr( wp_strip_all_tags( $this->settings[ $attr ] ) );
			}
		}

		return $attrs;

	}


	protected function prepare_classes( $classes, $defaults = array() ) {

		if ( is_string( $classes ) ) {
			$classes = array_map( 'esc_attr', array_map( 'trim', explode( ' ', $classes ) ) );
		}

		$classes = array_merge( $defaults, $classes );

		return $classes;

	}

	protected function prepare() {

		$this->settings['wrapper_classes'] = $this->prepare_classes(
			$this->settings['wrapper_classes'],
			array(
				'llms-form-field',
				sprintf( 'type-%s', $this->settings['type'] ),
			)
		);

		// Add default value if there's no explicit value and a default value is set.
		if ( ! $this->settings['value'] && '' !== $this->settings['default'] ) {
			$this->settings['value'] = $this->settings['default'];
		}

		// Allow setting `disabled` to `true` to disable the field.
		if ( true === $this->settings['disabled'] ) {
			$this->settings['disabled'] = 'disabled';
		}

		// Use the field id as the name if name isn't specified.
		$this->settings['name'] = empty( $this->settings['name'] ) ? $this->settings['id'] : $this->settings['name'];











		// // add space to classes
		// $field['wrapper_classes'] = ( $field['wrapper_classes'] ) ? ' ' . $field['wrapper_classes'] : '';
		// $field['classes']         = ( $field['classes'] ) ? ' ' . $field['classes'] : '';

		// // add column information to the wrapper
		// $field['wrapper_classes'] .= ' llms-cols-' . $field['columns'];
		// $field['wrapper_classes'] .= ( $field['last_column'] ) ? ' llms-cols-last' : '';

		// $desc = $field['description'] ? '<span class="llms-description">' . $field['description'] . '</span>' : '';

		// // required attributes and content
		// $required_char = apply_filters( 'lifterlms_form_field_required_character', '*', $field );
		// $required_span = $field['required'] ? ' <span class="llms-required">' . $required_char . '</span>' : '';
		// $required_attr = $field['required'] ? ' required="required"' : '';

		// // setup the label
		// $label = $field['label'] ? '<label for="' . $field['id'] . '">' . $field['label'] . $required_span . '</label>' : '';

		// $r = '<div class="llms-form-field type-' . $field['type'] . $field['wrapper_classes'] . '">';

		// if ( 'hidden' !== $field['type'] && 'checkbox' !== $field['type'] && 'radio' !== $field['type'] ) {
		// 	$r .= $label;
		// }

		// $min_attr = ( $field['min_length'] ) ? ' minlength="' . $field['min_length'] . '"' : '';
		// $max_attr = ( $field['max_length'] ) ? ' maxlength="' . $field['max_length'] . '"' : '';

		// $r .= $this->get_field_html();

		// if ( 'hidden' !== $field['type'] ) {
		// 	$r .= $desc;
		// }

		// $r .= '</div>';

		// if ( $field['last_column'] ) {
		// 	$r .= '<div class="clear"></div>';
		// }

	}

}
