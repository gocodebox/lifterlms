<?php
/**
 * Setup and render form fields.
 *
 * @package LifterLMS/Classes
 *
 * @since 5.0.0
 * @version 6.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Form_Field class
 *
 * @since 5.0.0
 */
class LLMS_Form_Field {

	/**
	 * Form Field Settings
	 *
	 * @var array {
	 *     Array of field settings.
	 *
	 *     @type array           $attributes       Associative array of HTML attributes to add to the field element.
	 *     @type bool            $checked          Determines if radio and checkbox fields are checked.
	 *     @type int             $columns          Number of columns the field wrapper should occupy when rendered. Accepts integers >= 1 and <= 12.
	 *     @type string[]|string $classes          Additional CSS classes to add to the field element. Accepts a string or an array of strings.
	 *     @type string          $data_store       Determines where to store field values. Accepts "users" or "usermeta" to store on the respective WP core tables.
	 *     @type string|false    $data_store_key   Determines the key name to use when storing the field value. Pass `false` to disable automatic storage. Defaults to the value of the `$name` property.
	 *     @type string          $description      A string to use as the field's description or helper text.
	 *     @type string          $default          The default value to use for the field.
	 *     @type bool            $disabled         Whether or not the field is enabled.
	 *     @type string          $id               The field's HTML "id" attribute. Must be unique. If not supplied, an ID is automatically generated.
	 *     @type string          $label            Text to use in the label element associated with the field.
	 *     @type bool            $label_show_empty When true and no `$label` is supplied, will show an empty label element.
	 *     @type bool            $last_column      When true, outputs a clearfix element following the element's wrapper. Allows ending a "row" of fields.
	 *     @type bool            $match            Match this field to another field for validation purposes. Must be the `$id` of another field in the form.
	 *     @type string          $name             The field's HTML "name" attribute. Default's to the value of `$id` when not supplied.
	 *     @type array           $options          An associative array of options used for select, checkbox groups, and radio fields.
	 *     @type string          $options_preset   A string representing a pre-defined set of `$options`. Accepts "countries" or "states". Custom presets can be defined using the filter "llms_form_field_options_preset_{$preset_id}".
	 *     @type string          $placeholder      The field's HTML placeholder attribute.
	 *     @type bool            $required         Determines if the field is marked as required.
	 *     @type string          $selected         Alias of `$default`.
	 *     @type string          $type             Field type. Accepts any HTML5 input type (text, email, tel, etc...), radio, checkbox, select, textarea, button, reset, submit, and html.
	 *     @type string          $value            Value of the field.
	 *     @type string[]|string $wrapper_classes  Additional CSS classes to add to the field's wrapper element. Accepts a string or an array of strings.
	 * }
	 */
	protected $settings = array();

	/**
	 * Cached field HTML.
	 *
	 * @var string
	 */
	protected $html = '';

	/**
	 * Data source where to get field value from.
	 *
	 * @var null|WP_Post|WP_User
	 */
	private $data_source;

	/**
	 * Data source type where to get field value from.
	 *
	 * @var null|string
	 */
	private $data_source_type;

	/**
	 * Constructor
	 *
	 * @since 5.0.0
	 *
	 * @param array      $settings    Field settings.
	 * @param int|object $data_source Optional. Data source where to get field value from. Default is `null`.
	 *                                Can be a WP_User or a WP_Post, or their id.
	 *                                The actual object will be retrieved basing on the data_store.
	 * @return void
	 */
	public function __construct( $settings = array(), $data_source = null ) {

		/**
		 * Filters the settings of a LifterLMS Form Field
		 *
		 * @since 5.0.0
		 *
		 * @param array           $settings Field settings.
		 * @param LLMS_Form_Field $field    Form field class instance.
		 */
		$this->settings = apply_filters( 'llms_field_settings', wp_parse_args( $settings, $this->get_defaults() ), $this );

		$this->define_data_source( $data_source );

		$this->prepare();
	}

	/**
	 * Define the source of the data
	 *
	 * @since 5.0.0
	 *
	 * @param int|object $data_source Data source where to get field value from.
	 * @return void
	 */
	private function define_data_source( $data_source ) {

		if ( empty( $this->settings['data_store'] ) || ! in_array( $this->settings['data_store'], array( 'users', 'usermeta' ), true ) ) {
			return;
		}

		if ( ! is_null( $data_source ) ) {
			$data_source = $data_source instanceof WP_User ? $data_source : get_user_by( 'ID', $data_source );
		} elseif ( is_user_logged_in() ) {
			$data_source = wp_get_current_user();
		}

		if ( $data_source instanceof WP_User ) {
			$this->data_source      = $data_source;
			$this->data_source_type = 'wp_user';
		}
	}

	/**
	 * Merge an array of classes into a string or array of classes
	 *
	 * @since 5.0.0
	 *
	 * @param string[]|string $classes  Classes.
	 * @param string[]        $defaults Default classes.
	 * @return string[]
	 */
	protected function classes_ensure_array( $classes, $defaults = array() ) {

		if ( is_string( $classes ) ) {
			$classes = array_map( 'esc_attr', array_map( 'trim', explode( ' ', $classes ) ) );
		}

		$classes = array_merge( $defaults, $classes );

		return array_filter( $classes );
	}

	/**
	 * Returns an array of form field objects from this checkbox or radio field's options array.
	 *
	 * @since 6.2.0 Moved from `LLMS_Form_Field::get_field_html()` and added the hidden logic.
	 *
	 * @param string $is_hidden If true, returns only the checked fields and sets their type to 'hidden',
	 *                          else returns all options as `$this->settings['type']` form fields.
	 * @return LLMS_Form_Field[]
	 */
	public function explode_options_to_fields( $is_hidden = false ) {

		$fields = array();
		$value  = ! empty( $this->settings['value'] ) || is_array( $this->settings['value'] )
			? $this->settings['value']
			: $this->settings['default'];

		foreach ( $this->settings['options'] as $key => $val ) {

			$name    = $this->settings['name'];
			$checked = $value === $key;

			if ( 'checkbox' === $this->settings['type'] ) {
				$name   .= '[]';
				$value   = is_array( $value ) ? $value : array( $value );
				$checked = in_array( $key, $value, true );
			}

			if ( $is_hidden && ! $checked ) {
				continue;
			}

			$fields[] = new self(
				array(
					'data_store' => false,
					'id'         => sprintf( '%1$s--%2$s', $this->settings['id'], $key ),
					'name'       => $name,
					'value'      => $key,
					'label'      => $val,
					'checked'    => $checked,
					'type'       => $is_hidden ? 'hidden' : $this->settings['type'],
				)
			);
		}

		return $fields;
	}

	/**
	 * Get default field settings.
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	protected function get_defaults() {

		return array(
			'attributes'       => array(),
			'checked'          => false,
			'columns'          => 12,
			'classes'          => array(), // Or string of space-separated classes.
			'data_store'       => 'usermeta', // Users or usermeta.
			'data_store_key'   => '', // Defaults to value passed for "name".
			'description'      => '',
			'default'          => '',
			'disabled'         => false,
			'id'               => '',
			'label'            => '',
			'label_show_empty' => false,
			'last_column'      => true,
			'match'            => '', // Test.
			'name'             => '', // Defaults to value passed for "id".
			'options'          => array(),
			'options_preset'   => '',
			'placeholder'      => '',
			'required'         => false,
			'selected'         => '', // Alias of "default".
			'type'             => 'text',
			'value'            => '',
			'wrapper_classes'  => array(), // Or string of space-separated classes.
		);
	}

	/**
	 * Ensure deprecated settings still function.
	 *
	 * The legacy "min_length", "max_length", and "style" settings should now
	 * be passed via the "attributes" setting.
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	protected function get_deprecated_html_attributes() {

		$attrs = array();
		foreach ( array( 'min_length', 'max_length', 'style' ) as $attr ) {
			if ( isset( $this->settings[ $attr ] ) ) {
				$attrs[ str_replace( '_', '', $attr ) ] = esc_attr( $this->settings[ $attr ] );
			}
		}

		return $attrs;
	}

	/**
	 * Retrieve HTML for the fields description
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_description_html() {

		return $this->settings['description'] ? sprintf( '<span class="llms-description">%s</span>', $this->settings['description'] ) : '';
	}

	/**
	 * Retrieve the full HTML for the field.
	 *
	 * @since 5.0.0
	 * @since 6.2.0 Moved exploding of checkbox and radio options to `explode_options_to_fields()`.
	 *
	 * @return string
	 */
	protected function get_field_html() {

		/**
		 * Allow 3rd parties to create custom field types or their own field HTML methods.
		 *
		 * Returning a non-empty string will override default HTML generation and use the returned HTML instead.
		 *
		 * @since 5.0.0
		 *
		 * @param string          $html     Override html.
		 * @param array           $settings Array of field settings initially passed to the class constructor.
		 * @param LLMS_Form_Field $field    Form field object.
		 */
		$override = apply_filters( 'llms_form_field_get_' . $this->settings['type'] . '_html', '', $this->settings, $this );
		if ( ! empty( $override ) ) {
			return $override;
		}

		$extra_attrs  = array();
		$inner_html   = '';
		$self_closing = false;

		switch ( $this->settings['type'] ) {

			case 'button':
			case 'reset':
			case 'submit':
				$tag                 = 'button';
				$classes             = array( 'llms-field-button' );
				$inner_html          = $this->settings['value'];
				$extra_attrs['type'] = $this->settings['type'];
				break;

			case 'checkbox':
			case 'radio':
				$is_group     = ! empty( $this->settings['options'] );
				$tag          = $is_group ? 'div' : 'input';
				$self_closing = ! $is_group;
				$classes      = array( sprintf( 'llms-field-%s', $this->settings['type'] ) );

				if ( ! $is_group ) {

					$extra_attrs['type'] = $this->settings['type'];
					if ( true === $this->settings['checked'] ) {
						$extra_attrs['checked'] = 'checked';
					}
				} else {

					$classes[] = 'llms-input-group';
					$fields    = $this->explode_options_to_fields( false );
					foreach ( $fields as $field ) {
						$inner_html .= $field->get_html();
					}
				}

				break;

			case 'html':
				$tag        = 'div';
				$classes    = array( 'llms-field-html' );
				$inner_html = $this->settings['value'];
				break;

			case 'select':
				$tag        = 'select';
				$classes    = array( 'llms-field-select' );
				$inner_html = $this->get_options_html();
				break;

			case 'textarea':
				$tag        = 'textarea';
				$classes    = array( 'llms-field-textarea' );
				$inner_html = $this->settings['value'];
				break;

			default:
				$tag                 = 'input';
				$self_closing        = true;
				$classes             = array( 'llms-field-input' );
				$extra_attrs['type'] = $this->settings['type'];

		}

		$extra_attrs['class'] = implode( ' ', $this->classes_ensure_array( $this->settings['classes'], $classes ) );

		$attributes = array_merge( $this->get_html_attributes( $this->settings ), $extra_attrs );
		ksort( $attributes );

		$attrs = '';
		foreach ( $attributes as $attr => $val ) {
			$attrs .= sprintf( ' %1$s="%2$s"', $attr, $val );
		}

		$open  = $self_closing ? sprintf( '<%1$s%2$s', $tag, $attrs ) : sprintf( '<%1$s%2$s>', $tag, $attrs );
		$close = $self_closing ? ' />' : sprintf( '</%s>', $tag );

		return sprintf( '%1$s%2$s%3$s', $open, $inner_html, $close );
	}

	/**
	 * Retrieve an array of HTML attributes which should be added to the main field element.
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	protected function get_html_attributes() {

		$check = array(
			'id',
			'disabled',
			'name',
			'placeholder',
			'required',
			'value',
		);

		// Input groups and html only have an id.
		if ( $this->is_input_group() || 'html' === $this->settings['type'] ) {
			$check = array( 'id' );
		}

		$attrs = array();

		// Settings attributes.
		foreach ( $check as $attr ) {
			if ( ! empty( $this->settings[ $attr ] ) ) {
				$attrs[ $attr ] = esc_attr( wp_strip_all_tags( $this->settings[ $attr ] ) );
			}
		}

		// Any custom attributes.
		foreach ( $this->settings['attributes'] as $attr => $val ) {
			$attrs[ $attr ] = esc_attr( wp_strip_all_tags( $val ) );
		}

		if ( $this->settings['match'] ) {
			$attrs['data-match'] = $this->settings['match'];
		}

		return array_merge( $attrs, $this->get_deprecated_html_attributes() );
	}

	/**
	 * Retrieve the field's HTML.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	public function get_html() {

		/**
		 * Short-circuit field HTML generation.
		 *
		 * Allows a 3rd party to replace the HTML generation method with entirely custom HTML
		 * by returning a non-null value.
		 *
		 * @since 5.0.0
		 *
		 * @param string          $pre       The pre-rendered HTML content. Default `null`.
		 * @param array           $settings  The prepared field settings array.
		 * @param LLMS_Form_Field $field_obj Form field instance.
		 */
		$pre = apply_filters( 'llms_form_field_pre_render', null, $this->settings, $this );
		if ( ! is_null( $pre ) ) {
			return $pre;
		}

		$before = '';
		$after  = '';

		if ( 'hidden' !== $this->settings['type'] ) {

			$before .= sprintf( '<div class="%s">', implode( ' ', $this->settings['wrapper_classes'] ) );

			$label_pos   = $this->get_label_position();
			$$label_pos .= $this->get_label_html();

			$desc = $this->get_description_html();

			if ( $this->is_input_group() ) {
				$before .= $desc;
			} else {
				$after .= $this->get_description_html();
			}

			$after .= '</div>';

			if ( $this->settings['last_column'] ) {
				$after .= '<div class="clear"></div>';
			}
		}

		$this->html = $before . $this->get_field_html() . $after;

		return apply_filters( 'llms_form_field', $this->html, $this->settings );
	}

	/**
	 * Retrieve the HTML for the fields label.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_label_html() {

		if ( empty( $this->settings['label'] ) && ! $this->settings['label_show_empty'] ) {
			return '';
		}

		$required = '';
		if ( $this->settings['required'] ) {

			/**
			 * Customize the character used to denote a required field
			 *
			 * @since Unknown.
			 *
			 * @param string $character The character used to denote a required field. Defaults to "*" (an asterisk).
			 * @param array  $settings  Associative array of field settings.
			 */
			$char     = apply_filters( 'lifterlms_form_field_required_character', '*', $this->settings );
			$required = sprintf( '<span class="llms-required">%s</span>', $char );

		}

		return sprintf( '<label for="%1$s">%2$s%3$s</label>', esc_attr( $this->settings['id'] ), $this->settings['label'], $required );
	}

	/**
	 * Determines if the label element should be rendered before the field or after it.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_label_position() {

		$pos = 'before';

		if ( in_array( $this->settings['type'], array( 'checkbox', 'radio' ), true ) && empty( $this->settings['options'] ) ) {
			$pos = 'after';
		}

		return $pos;
	}

	/**
	 * Retrieve the HTML for an options list in a select field.
	 *
	 * This function works recursively to build optgroups.
	 *
	 * @since 5.0.0
	 *
	 * @param array $options      Prepared options array.
	 * @param mixed $selected_val The value of the option that should be marked as "selected".
	 * @return string
	 */
	protected function get_option_list_html( $options, $selected_val ) {

		$html = '';
		foreach ( $options as $key => $val ) {

			if ( is_array( $val ) ) {

				$label         = isset( $val['label'] ) ? $val['label'] : $key;
				$group_options = isset( $val['options'] ) ? $val['options'] : $val;
				$html         .= sprintf( '<optgroup label="%1$s" data-key="%2$s">%3$s</optgroup>', esc_attr( $label ), esc_attr( $key ), $this->get_option_list_html( $group_options, $selected_val ) );

			} else {

				$selected = ( (string) $key === (string) $selected_val ) ? ' selected="selected"' : '';
				$disabled = ( $this->settings['placeholder'] && '' === $key ) ? ' disabled="disabled"' : '';
				$html    .= sprintf( '<option value="%1$s"%3$s%4$s>%2$s</option>', esc_attr( $key ), esc_attr( $val ), $selected, $disabled );

			}
		}

		return $html;
	}

	/**
	 * Retrieve the html for all options in a select field.
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_options_html() {

		$html = '';

		if ( ! $this->settings['options'] ) {
			return $html;
		}

		$selected_val = ! empty( $this->settings['value'] ) ? $this->settings['value'] : $this->settings['default'];
		$html        .= $this->get_option_list_html( $this->settings['options'], $selected_val );

		return $html;
	}

	/**
	 * Retrieve the field settings array.
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Determines if the field is a group of checkboxes or radios.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	protected function is_input_group() {

		return in_array( $this->settings['type'], array( 'checkbox', 'radio' ), true ) && ! empty( $this->settings['options'] );
	}

	/**
	 * Prepares the field for rendering by configuring all of it's settings.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	protected function prepare() {

		if ( empty( $this->settings['id'] ) ) {
			$this->settings['id'] = uniqid( 'llms-field-' );
		}

		$this->prepare_wrapper_classes();

		$this->settings['classes'] = $this->classes_ensure_array( $this->settings['classes'] );

		// Allow setting `disabled` to `true` to disable the field.
		if ( true === $this->settings['disabled'] ) {
			$this->settings['disabled'] = 'disabled';
		}

		// Allow setting `required` to `true` to make the field required the field.
		if ( true === $this->settings['required'] ) {
			$this->settings['required'] = 'required';
		}

		// When name is `false` we don't want to output a name on the field.
		if ( false !== $this->settings['name'] ) {
			// Use the field id as the name if name isn't specified.
			$this->settings['name'] = empty( $this->settings['name'] ) ? $this->settings['id'] : $this->settings['name'];
		}

		// When `data_store_key` is false we won't automatically store or populate the field.
		if ( false !== $this->settings['data_store_key'] && empty( $this->settings['data_store_key'] ) ) {
			$this->prepare_storage();
		}

		// Add preset options.
		if ( $this->settings['options_preset'] ) {
			$this->prepare_options_from_preset();
		} elseif ( ! empty( $this->settings['options'] ) ) {
			$this->settings['options'] = $this->prepare_options( $this->settings['options'] );
		}

		$this->prepare_value();

		if ( 'llms-password-strength-meter' === $this->settings['id'] ) {
			$this->prepare_password_strength_meter();
		} elseif ( 'llms_voucher' === $this->settings['id'] ) {
			$this->prepare_voucher();
		}
	}

	/**
	 * Prepare the fields options.
	 *
	 * Allows options to be setup as an associative array of key/value pairs or
	 * an array of associative arrays each with a "label" and "key" property.
	 * The "key" property may be omitted, in which case the "label" will be
	 * duplicated as the option's "value".
	 *
	 * @since 5.0.0
	 *
	 * @param array $raw Raw field data.
	 * @return array
	 */
	protected function prepare_options( $raw ) {

		$prepared = array();

		foreach ( $raw as $key => $val ) {

			if ( is_array( $val ) ) {

				// Option group.
				if ( isset( $val['options'] ) ) {

					$prepared[ $key ] = array(
						'label'   => isset( $val['label'] ) ? $val['label'] : $key,
						'options' => $this->prepare_options( $val['options'] ),
					);

					// From block editor options array.
				} elseif ( isset( $val['text'] ) ) {

					$item_key              = isset( $val['key'] ) ? $val['key'] : $val['text'];
					$prepared[ $item_key ] = $val['text'];

					if ( isset( $val['default'] ) && llms_parse_bool( $val['default'] ) ) {
						if ( 'checkbox' === $this->settings['type'] ) { // Account for multiple defaults.
							$this->settings['default']   = is_array( $this->settings['default'] ) ? $this->settings['default'] : array();
							$this->settings['default'][] = $item_key;
						} else {
							$this->settings['default'] = $item_key;
						}
					}
				}

				// Flat array of $key=>$val.
			} else {

				$prepared[ $key ] = $val;

			}
		}

		// Add a placeholder.
		if ( $this->settings['placeholder'] ) {
			$this->settings['default'] = '';
			$prepared                  = array_merge( array( '' => $this->settings['placeholder'] ), $prepared );
		}

		return $prepared;
	}

	/**
	 * Retrieve options list data based on the options_preset settings.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	protected function prepare_options_from_preset() {

		$preset_id = $this->settings['options_preset'];
		switch ( $preset_id ) {
			case 'countries':
				$options                             = get_lifterlms_countries();
				$default                             = get_lifterlms_country();
				$this->settings['wrapper_classes'][] = 'llms-l10n-country-select';
				break;

			case 'states':
				$options                             = llms_get_states();
				$this->settings['wrapper_classes'][] = 'llms-l10n-state-select';
				break;

			default:
				/**
				 * Define custom / 3rd party presets
				 *
				 * @since 5.0.0
				 *
				 * @param array $options              Array of options.
				 * @param array $settings             Prepared field settings.
				 * @param LLMS_Form_Field $fomr_field Form field object instance.
				 */
				$options = apply_filters( "llms_form_field_options_preset_{$preset_id}", array(), $this->settings, $this );
		}

		if ( isset( $options ) ) {
			$this->settings['options'] = $options;
		}

		if ( isset( $default ) && ! $this->settings['default'] ) {
			$this->settings['default'] = $default;
		}
	}

	/**
	 * Additional preparation for the password strength meter.
	 *
	 * @since 5.0.0
	 * @since 5.10.0 Make sure to enqueue the strength meter js, whether or not `wp_enqueue_scripts` hook has been fired yet.
	 *
	 * @return void
	 */
	protected function prepare_password_strength_meter() {

		$meter_settings = array(
			'blocklist'    => array(),
			'min_strength' => ! empty( $this->settings['min_strength'] ) ? $this->settings['min_strength'] : 'strong',
			'min_length'   => ! empty( $this->settings['min_length'] ) ? max( 6, $this->settings['min_length'] ) : 6,
		);

		// Backwards compat functionality ends up outputting a minlength attribute on the <div> and we don't want that.
		unset( $this->settings['min_length'] );

		/**
		 * Modify password strength meter settings.
		 *
		 * @since 5.0.0
		 *
		 * @param array $meter_settings {
		 *     Hash of meter configuration options.
		 *
		 *     @type string[] $blocklist    A list of strings that are penalized when used in the password. See "user_inputs" at https://github.com/dropbox/zxcvbn#usage.
		 *     @type string   $min_strength The minimum acceptable password strength. Accepts "strong", "medium", or "weak". Default: "strong".
		 *     @type int      $min_length   The minimum acceptable password length. Must be >= 6. Default: 6.
		 * }
		 */
		$meter_settings = apply_filters( 'llms_password_strength_meter_settings', $meter_settings, $this->settings, $this );

		// If scripts have been enqueued, add password strength meter script.
		if ( did_action( 'wp_enqueue_scripts' ) ) {
			return $this->enqueue_strength_meter( $meter_settings );
		}
		// Otherwise add it whe `wp_enqueue_scripts` is fired.
		add_action(
			'wp_enqueue_scripts',
			function () use ( $meter_settings ) {
				$this->enqueue_strength_meter( $meter_settings );
			}
		);
	}

	/**
	 * Enqueue password strength meter script.
	 *
	 * @since 5.10.0
	 *
	 * @param array $meter_settings {
	 *     Hash of meter configuration options.
	 *
	 *     @type string[] $blocklist    A list of strings that are penalized when used in the password. See "user_inputs" at https://github.com/dropbox/zxcvbn#usage.
	 *     @type string   $min_strength The minimum acceptable password strength. Accepts "strong", "medium", or "weak". Default: "strong".
	 *     @type int      $min_length   The minimum acceptable password length. Must be >= 6. Default: 6.
	 * }
	 * @return void
	 */
	private function enqueue_strength_meter( $meter_settings ) {

		wp_enqueue_script( 'password-strength-meter' );
		// Localize the script with meter data.
		llms()->assets->enqueue_inline(
			'llms-pw-strength-settings',
			'window.LLMS.PasswordStrength = window.LLMS.PasswordStrength || {};window.LLMS.PasswordStrength.get_settings = function() { return JSON.parse( \'' . wp_json_encode( $meter_settings ) . '\' ); };',
			'footer',
			15
		);
	}

	/**
	 * Setup default storage information.
	 *
	 * Ensures fields stored on the wp_users table have the proper default `data_store`.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	protected function prepare_storage() {

		$name = $this->settings['name'];

		// Field Name => Storage Key.
		$users_fields = array(

			// We prefer these aliases for legacy reasons.
			'email_address' => 'user_email',
			'password'      => 'user_pass',

			// Default wp_users column names.
			'user_login'    => 'user_login',
			'user_pass'     => 'user_pass',
			'user_nicename' => 'user_nicename',
			'user_email'    => 'user_email',
			'user_url'      => 'user_url',
			'display_name'  => 'display_name',

		);

		// Set data storage for items on the wp_users table.
		if ( in_array( $name, array_keys( $users_fields ), true ) ) {
			$this->settings['data_store'] = 'users';
			$name                         = $users_fields[ $name ];

			// Don't save default core confirmation fields.
		} elseif ( in_array( $name, LLMS_CONFIRMATION_FIELDS, true ) ) {
			$this->settings['data_store'] = false;
		}

		$this->settings['data_store_key'] = $name;
	}

	/**
	 * Prepare the field's value.
	 *
	 * @since 5.0.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	protected function prepare_value() {

		// Never autoload passwords and or fields with an explicit value (except radio and checkbox).
		if ( 'password' === $this->settings['type'] || ! empty( $this->settings['value'] && ! in_array( $this->settings['type'], array( 'checkbox', 'radio' ), true ) ) ) {
			return;
		}

		$user_val = null;

		// Attempt to populate field data from the most recent $_POST action.
		if ( 'POST' === strtoupper( getenv( 'REQUEST_METHOD' ) ) ) {
			$posted = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified prior to reaching this method.
			if ( isset( $posted[ $this->settings['name'] ] ) ) {
				$filter_options = is_array( $posted[ $this->settings['name'] ] ) ? array( FILTER_REQUIRE_ARRAY ) : array();
				$user_val       = llms_filter_input_sanitize_string( INPUT_POST, $this->settings['name'], $filter_options );
			}
		}

		// Auto-populate field from the datastore if we have a user and datastore information.
		if ( is_null( $user_val ) && ( isset( $this->data_source ) && 'wp_user' === $this->data_source_type ) && $this->settings['data_store_key'] ) {
			$user_val = $this->data_source->get( $this->settings['data_store_key'] );
		}

		// Set the value to the user's submitted or stored value.
		if ( ! is_null( $user_val ) ) {
			if ( in_array( $this->settings['type'], array( 'checkbox', 'radio' ), true ) && ! $this->is_input_group() ) {
				$this->settings['checked'] = ( $this->settings['value'] === $user_val );
			} else {
				$this->settings['value'] = $user_val;
			}
		}

		// Handle "default" alias "selected".
		if ( isset( $this->settings['selected'] ) && '' !== $this->settings['selected'] ) {
			$this->settings['default'] = $this->settings['selected'];
		}

		// Add default value if there's no explicit value and a default value is set.
		if ( ! $this->settings['value'] && ! is_array( $this->settings['value'] ) && '' !== $this->settings['default'] ) {
			$this->settings['value'] = $this->settings['default'];
		}
	}

	/**
	 * Additional preparation for the special voucher field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	protected function prepare_voucher() {

		if ( ! $this->settings['required'] && $this->settings['toggleable'] ) {

			$this->settings['label'] = sprintf( '<a class="llms-voucher-toggle" id="llms-voucher-toggle" href="#">%s</a>', $this->settings['label'] );

			$this->settings['attributes']['style'] = 'display:none;';

			$this->settings['data_store_key'] = false;

		}
	}

	/**
	 * Prepare CSS wrapper classes for the field.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	protected function prepare_wrapper_classes() {

		$defaults = array();

		// Base field class.
		$defaults[] = 'llms-form-field';

		// Add class for the field type.
		$defaults[] = sprintf( 'type-%s', $this->settings['type'] );

		if ( $this->is_input_group() ) {
			$defaults[] = 'is-group';
		}

		// Add columns classes.
		$defaults[] = sprintf( 'llms-cols-%d', $this->settings['columns'] );
		if ( $this->settings['last_column'] ) {
			$defaults[] = 'llms-cols-last';
		}

		// If required, add a class.
		if ( $this->settings['required'] ) {
			$defaults[] = 'llms-is-required';
		}

		$this->settings['wrapper_classes'] = $this->classes_ensure_array(
			$this->settings['wrapper_classes'],
			$defaults
		);
	}

	/**
	 * Render/output the field's html.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function render() {

		if ( ! $this->html ) {
			$this->get_html();
		}

		echo wp_kses( $this->html, LLMS_ALLOWED_HTML_FORM_FIELDS );
	}
}
