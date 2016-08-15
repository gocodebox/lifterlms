<?php
/**
 * LifterLMS Membership Model
 * @since  3.0.0
 * @version  3.0.0
 *
 * @property  $restriction_redirect_type  (string)  What type of redirect action to take when content is restricted by this membership [none|membership|page|custom]
 * @property  $redirect_page_id  (int)  WP Post ID of a page to redirect users to when $restriction_redirect_type is 'page'
 * @property  $redirect_custom_url  (string)  Arbitrary URL to redirect users to when $restriction_redirect_type is 'custom'
 * @property  $restriction_add_notice  (string)  Whether or not to add an on screen message when content is restricted by this membership [yes|no]
 * @property  $restriction_notice  (string)  Notice to display when $restriction_add_notice is 'yes'
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Membership extends LLMS_Post_Model {

	protected $db_post_type = 'llms_membership'; // maybe fix this
	protected $model_post_type = 'membership';

	/**
	 * Get a property's data type for scrubbing
	 * used by $this->scrub() to determine how to scrub the property
	 * @param  string $key  property key
	 * @return string
	 * @since  3.0.0
	 */
	protected function get_property_type( $key ) {

		switch( $key ) {

			case 'redirect_page_id':
				$type = 'absint';
			break;

			case 'restriction_add_notice':
				$type = 'yesno';
			break;

			case 'restriction_notice':
				$type = 'html';
			break;

			case 'redirect_custom_url':
			case 'restriction_redirect_type':
			default:
				$type = 'text';

		}

		return $type;

	}

}
