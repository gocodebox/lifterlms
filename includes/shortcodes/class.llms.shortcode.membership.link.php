<?php
/**
 * LifterLMS Membership Link Shortcode
 *
 * Output an anchor link for a membership.
 *
 * [lifterlms_membership_link]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.0.0
 * @version 3.4.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Membership_Link
 *
 * @since 3.0.0
 * @since 3.4.3 Unknown.
 */
class LLMS_Shortcode_Membership_Link extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_membership_link';

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @return   string
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	protected function get_output() {
		if ( 'publish' !== get_post_status( $this->get_attribute( 'id' ) ) ) {
			return '';
		}

		return '<a href="' . get_permalink( $this->get_attribute( 'id' ) ) . '">' . $this->get_content() . '</a>';
	}

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @return   array
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	protected function get_default_attributes() {
		return array(
			'id' => get_the_ID(),
		);
	}

	/**
	 * Retrieves a string used for default content which is used if no content is supplied
	 *
	 * @return   string
	 * @since    3.4.3
	 * @version  3.4.3
	 */
	protected function get_default_content( $atts = array() ) {
		$default_content = 'publish' === get_post_status( $this->get_attribute( 'id' ) ) ? get_the_title( $this->get_attribute( 'id' ) ) : '';
		return apply_filters( 'lifterlms_membership_link_text', $default_content, $this->get_attribute( 'id' ) );
	}
}

return LLMS_Shortcode_Membership_Link::instance();
