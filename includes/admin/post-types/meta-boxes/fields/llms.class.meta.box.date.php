<?php
/**
 * Meta box Field: Date Picker Field
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Fields/Classes
 *
 * @since  Unknown
 * @version  3.11.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Metabox_Date_Field class
 *
 * Pass in 'llms-datepicker' for the class for the field to automatically use jQuery datepicker.
 *
 * @since Unknown
 */
class LLMS_Metabox_Date_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor
	 *
	 * @param    array $_field Array containing information about field
	 * @since    ??
	 * @version  3.11.0
	 */
	public function __construct( $_field ) {

		$_field = wp_parse_args(
			$_field,
			array(
				'date_format'        => 'mm/dd/yy', // jQuery datepicker formats (http://api.jqueryui.com/datepicker/#utility-formatDate).
				'date_max'           => '',
				'date_min'           => '',
				'date_displayformat' => $this->php_to_jquery_date_format( get_option( 'date_format' ) ),
			)
		);

		$this->field = $_field;
	}

	function php_to_jquery_date_format( $php_format ) {
		$replacements = array(
			// Day
			'd' => 'dd',
			'D' => 'D',
			'j' => 'd',
			'l' => 'DD',
			// Month
			'm' => 'mm',
			'n' => 'm',
			'M' => 'M',
			'F' => 'MM',
			// Year
			'Y' => 'yy',
			'y' => 'y',
		);

		return strtr( $php_format, $replacements );
	}

	function jquery_date_to_php_format( $js_format ) {
		// Mapping of jQuery UI Datepicker tokens to PHP date format tokens
		$replacements = array(
			// Year tokens
			'yy' => 'Y',  // 4-digit year
			'y'  => 'y',  // 2-digit year

			// Month tokens
			'mm' => 'm',  // Month with leading zero
			'm'  => 'n',  // Month without leading zero
			'MM' => 'F',  // Full month name
			'M'  => 'M',  // Short month name

			// Day tokens
			'dd' => 'd',  // Day with leading zero
			'd'  => 'j',  // Day without leading zero
			'DD' => 'l',  // Full day name
			'D'  => 'D',   // Short day name
		);

		// Sort keys descending by length to avoid partial replacements.
		uksort(
			$replacements,
			function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);

		return strtr( $js_format, $replacements );
	}

	/**
	 * Construct data attributes for the field
	 * sets up jQuery datepicker
	 *
	 * @return   [type]     [description]
	 * @since    3.11.0
	 * @version  3.11.0
	 */
	public function get_data_attrs() {

		$attrs = array(
			'date_format' => 'data-format',
			'date_max'    => 'data-max-date',
			'date_min'    => 'data-min-date',
		);

		$data_attrs = '';
		foreach ( $attrs as $key => $attr ) {
			$val = ! empty( $this->field[ $key ] ) ? $this->field[ $key ] : null;
			if ( $val ) {
				$data_attrs .= sprintf( '%1$s="%2$s"', $attr, $val );
			}
		}
		return $data_attrs;
	}

	/**
	 * outputs the Html for the given field
	 *
	 * @since    ??
	 * @version  3.11.0
	 *
	 * @return void
	 */
	public function output() {

		global $post;

		parent::output(); ?>
		<?php
		// Convert the meta value into display format.
		$js_display_date = $this->meta;
		if ( ! empty( $this->meta ) ) {
			$meta_date = DateTime::createFromFormat( $this->jquery_date_to_php_format( $this->field['date_format'] ), $this->meta );

			if ( ! $meta_date ) {
				error_log( sprintf( 'Meta value %s for field %s is not in the expected format %s', $this->meta, $this->field['id'], $this->field['date_format'] ) );
			}

			if ( $meta_date ) {
				$js_display_date = $meta_date->format( $this->jquery_date_to_php_format( $this->field['date_displayformat'] ) );
			}
		}
		?>
		<input type="hidden"
				id="<?php echo esc_attr( $this->field['id'] ); ?>_alt_datefield"
				name="<?php echo esc_attr( $this->field['id'] ); ?>"
				value="<?php echo ! empty( $this->meta ) ? esc_attr( $this->meta ) : ''; ?>"
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?>
				required="required"
			<?php endif; ?>
		/>
		<input type="text"
			name="<?php echo esc_attr( $this->field['id'] ); ?>_datepicker"
			id="<?php echo esc_attr( $this->field['id'] ); ?>"
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			value="<?php echo ! empty( $this->meta ) ? esc_attr( $js_display_date ) : ''; ?>"
			size="30"
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?>
			required="required"
			<?php endif; ?>
			<?php if ( isset( $this->field['placeholder'] ) ) : ?>
			placeholder="<?php echo esc_attr( $this->field['placeholder'] ); ?>"
			<?php endif; ?>
			data-format="<?php echo esc_attr( $this->field['date_displayformat'] ); ?>"
			data-alt-format="<?php echo esc_attr( $this->field['date_format'] ); ?>"
			<?php if ( ! empty( $this->field['date_max'] ) ) : ?>
				data-max-date="<?php echo esc_attr( $this->field['date_max'] ); ?>"
			<?php endif; ?>
			<?php if ( ! empty( $this->field['date_min'] ) ) : ?>
				data-min-date="<?php echo esc_attr( $this->field['date_min'] ); ?>"
			<?php endif; ?>
			data-alt-field="#<?php echo esc_attr( $this->field['id'] ); ?>_alt_datefield"

		/>
		<?php
		parent::close_output();
	}
}

