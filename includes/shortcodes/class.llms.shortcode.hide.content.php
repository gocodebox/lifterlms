<?php
/**
 * LifterLMS Hide Content Shortcode
 *
 * [lifterlms_hide_content]
 *
 * @since    3.5.1
 * @version  3.5.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Shortcode_Hide_Content extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 * @var  string
	 */
	public $tag = 'lifterlms_hide_content';

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 * @return   array
	 * @since    3.5.1
	 * @version  3.5.1
	 */
	protected function get_default_attributes() {
		return array(
			'membership' => '', // backwards compat, use ID moving forwad
			'message' => '',
			'id' => get_the_ID(),
		);
	}

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @return   string
	 * @since    3.5.1
	 * @version  3.5.1
	 */
	protected function get_output() {

		// backwards compatibility, get membership if set and fallback to the id
		$id = $this->get_attribute( 'membership' ) ?  $this->get_attribute( 'membership' ) : $this->get_attribute( 'id' );

		if ( llms_is_user_enrolled( get_current_user_id(), $id ) ) {
			return do_shortcode( $this->get_content() );
		}

		return $this->get_attribute( 'message' );

	}

}

return LLMS_Shortcode_Hide_Content::instance();
