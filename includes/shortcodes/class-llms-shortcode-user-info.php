<?php
/**
 * LifterLMS User Information Shortcode class.
 *
 * [user]
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_User_Info class.
 *
 * @since [version]
 */
class LLMS_Shortcode_User_Info extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'user';

	protected function get_fields() {

		$fields = array(
			'',
		);

		return $fields;

	}

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @since [version]
	 *
	 * @return   array
	 */
	protected function get_default_attributes() {
		return array(
			'id'      => get_current_user_id(),
			'field'   => '',

			'if'      => '',
			'compare' => '=', // !=, <, >, <=, >=,
			'value'   => '',

			'prefix'  => '',
		);
	}

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_output() {

		if ( ! $this->get_attribute( 'id' ) ) {
			return '';
		}

		$student = llms_get_student( $this->get_attribute( 'id' ) );
		if ( ! $student ) {
			return '';
		}

		$field = $this->get_attribute( 'field' );
		if ( $field ) {
			$val = $student->get( $field );
			if ( $val ) {
				return $val;
			}
		}

	}

	/**
	 * Merge user attributes with default attributes.
	 *
	 * @since [version]
	 *
	 * @param array $atts User-submitted shortcode attributes.
	 *
	 * @return array
	 */
	protected function set_attributes( $atts = array() ) {

		if ( isset( $atts[0] ) ) {
			$atts['field'] = $atts[0];
			unset( $atts[0] );
		}

		return parent::set_attributes( $atts );

	}

}

return LLMS_Shortcode_User_Info::instance();
