<?php
/**
 * LifterLMS Favorites Shortcode.
 *
 * [lifterlms_favorites]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Favorites Shortcode class.
 *
 * @since version
 */
class LLMS_Shortcode_Favorites extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_favorites';

	/**
	 * Get shortcode attributes
	 *
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output().
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_output() {

		$this->enqueue_script( 'llms-jquery-matchheight' );

		ob_start();

		// If we're outputting a "My Favorites" list and we don't have a student output login info.
		if ( ! llms_get_student() ) {

			printf(
				__( 'You must be logged in to view this information. Click %1$shere%2$s to login.', 'lifterlms' ),
				'<a href="' . llms_get_page_url( 'myaccount' ) . '">',
				'</a>'
			);

		} else {

			$favorites = $this->get_favorites();

			if ( $favorites ) {

				foreach ( $favorites as $favorite ) {

					$lesson = new LLMS_Lesson( $favorite->post_id );

					llms_get_template(
						'course/lesson-preview.php',
						array(
							'lesson' => $lesson,
						)
					);

				}
			} else {

				printf( '<p>%s</p>', __( 'No favorites found.', 'lifterlms' ) );

			}
		}

		return ob_get_clean();

	}

}

return LLMS_Shortcode_Favorites::instance();
