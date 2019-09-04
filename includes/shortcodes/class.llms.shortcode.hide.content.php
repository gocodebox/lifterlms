<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Hide Content Shortcode
 *
 * [lifterlms_hide_content]
 *
 * @example
 *      [hide_content id="1"] allows user with access to 1 to access content
 *      [hide_content id="1,2,3,4" relation="any"] allows user with access to 1,2,3, OR 4 to access content
 *      [hide_content id="1,2,3,4" relation="all"] allows only users with access 1,2,3 AND 4 to access
 *
 * @since    3.5.1
 * @version  3.24.1
 */
class LLMS_Shortcode_Hide_Content extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_hide_content';

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @return   array
	 * @since    3.5.1
	 * @version  3.24.1
	 */
	protected function get_default_attributes() {
		return array(
			'membership' => '', // backwards compat, use ID moving forward
			'message'    => '',
			'id'         => get_the_ID(),
			'relation'   => 'all',
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
	 * @version  3.24.1
	 */
	protected function get_output() {

		// backwards compatibility, get membership if set and fallback to the id.
		$ids = $this->get_attribute( 'membership' ) ? $this->get_attribute( 'membership' ) : $this->get_attribute( 'id' );

		// Explode, trim whitespace and remove empty values.
		$ids = (array) array_map( 'trim', array_filter( explode( ',', $ids ) ) );

		// Assume content is hidden.
		$hidden = true;

		if ( 'any' === $this->get_attribute( 'relation' ) && ! empty( $ids ) ) {
			foreach ( $ids as $id ) {
				if ( llms_is_user_enrolled( get_current_user_id(), $id ) ) {
					$hidden = false;
					break;
				}
			}
		} elseif ( 'all' === $this->get_attribute( 'relation' ) && ! empty( $ids ) ) {
			$inc = 0;
			foreach ( $ids as $id ) {
				if ( llms_is_user_enrolled( get_current_user_id(), $id ) ) {
					$inc++;
				}
			}

			if ( count( $ids ) === $inc ) {
				$hidden = false;
			}
		}

		return ! $hidden ? do_shortcode( $this->get_content() ) : $this->get_attribute( 'message' );

	}

}

return LLMS_Shortcode_Hide_Content::instance();
