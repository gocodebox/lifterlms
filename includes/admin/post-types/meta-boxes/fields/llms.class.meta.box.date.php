<?php
/**
 * Date Picker Field
 * Pass in 'llms-datepicker' for the class for the field to automatically use jQuery datepicker!
 * @since    ??
 * @version  3.11.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Metabox_Date_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor
	 * @param    array $_field Array containing information about field
	 * @since    ??
	 * @version  3.11.0
	 */
	function __construct( $_field ) {

		$_field = wp_parse_args( $_field, array(
			'date_format' => 'mm/dd/yy', // jQuery datepicker formats (http://api.jqueryui.com/datepicker/#utility-formatDate)
			'date_max' => '',
			'date_min' => '',
		) );

		$this->field = $_field;

	}

	/**
	 * Construct data attributes for the field
	 * sets up jQuery datepicker
	 * @return   [type]     [description]
	 * @since    3.11.0
	 * @version  3.11.0
	 */
	public function get_data_attrs() {

		$attrs = array(
			'date_format' => 'data-format',
			'date_max' => 'data-max-date',
			'date_min' => 'data-min-date',
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
	 * @return HTML
	 * @since    ??
	 * @version  3.11.0
	 */
	public function output() {

		global $post;

		parent::output(); ?>

		<input type="text"
			name="<?php echo $this->field['id']; ?>"
			id="<?php echo $this->field['id']; ?>"
			class="<?php echo esc_attr( $this->field['class'] ); ?>"
			value="<?php echo ! empty( $this->meta ) ? $this->meta : ''; ?>" size="30"
			<?php if ( isset( $this->field['required'] ) && $this->field['required'] ) : ?>
				required="required"
			<?php endif; ?>
			<?php if ( isset( $this->field['placeholder'] ) ) : ?>
				placeholder="<?php echo $this->field['placeholder']; ?>"
			<?php endif; ?>
			<?php echo $this->get_data_attrs(); ?>
		/>
		<?php
		parent::close_output();
	}
}

