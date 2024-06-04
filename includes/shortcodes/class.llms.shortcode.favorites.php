<?php
/**
 * LifterLMS Favorites Shortcode.
 *
 * [lifterlms_favorites]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 7.5.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Favorites Shortcode class.
 *
 * @since version
 */
class LLMS_Shortcode_Favorites extends LLMS_Shortcode {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_favorites';

	/**
	 * Get shortcode attributes.
	 *
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output().
	 *
	 * @since 7.5.0
	 *
	 * @return array
	 */
	protected function get_default_attributes() {

		return array(
			'orderby' => 'updated_date',
			'order'   => 'ASC',
			'limit'   => '',
		);
	}

	/**
	 * Retrieve an array of Favorites from `lifterlms_user_postmeta`.
	 *
	 * @since 7.5.0
	 *
	 * @return WP_Query
	 */
	protected function get_favorites() {

		$student = llms_get_student();

		$order_by = $this->get_attribute( 'orderby' );
		$order    = $this->get_attribute( 'order' );
		$limit    = $this->get_attribute( 'limit' );

		return $student->get_favorites( $order_by, $order, $limit );
	}

	/**
	 * Retrieve the actual content of the shortcode.
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter.
	 *
	 * @since 7.5.0
	 *
	 * @return string
	 */
	protected function get_output() {

		ob_start();

		// If we're outputting a "My Favorites" list and we don't have a student output login info.
		if ( ! llms_get_student() ) {

			printf(
				// Translators: 1%$s = Opening anchor tag; %2$s = Closing anchor tag.
				esc_html__( 'You must be logged in to view this information. Click %1$shere%2$s to login.', 'lifterlms' ),
				'<a href="' . esc_url( llms_get_page_url( 'myaccount' ) ) . '">',
				'</a>'
			);
		} else {
			$favorites = $this->get_favorites();
			llms_template_my_favorites_loop( get_current_user_id(), $favorites );
		}

		return ob_get_clean();
	}
}

return LLMS_Shortcode_Favorites::instance();
