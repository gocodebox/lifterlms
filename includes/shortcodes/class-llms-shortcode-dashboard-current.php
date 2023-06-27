<?php
/**
 * LLMS_Shortcode_Dashboard_Current class.
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Dashboard Current Shortcode.
 *
 * Shortcode: [lifterlms_dashboard_current]
 *
 * @since [version]
 */
class LLMS_Shortcode_Dashboard_Current extends LLMS_Shortcode {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_dashboard_current';

	/**
	 * Retrieve the actual content of the shortcode.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_output(): string {
		$slug = LLMS_Student_Dashboard::get_current_tab( 'slug' );

		if ( 'dashboard' === $slug ) {
			return '';
		}

		$map = [
			'view-courses'      => 'my-courses',
			'my-grades'         => 'my-grades',
			'view-certificates' => 'my-certificates',
			'view-memberships'  => 'my-memberships',
			'view-achievements' => 'my-achievements',
		];

		$page = isset( $map[ $slug ] ) ? get_page_by_path( 'dashboard/' . $map[ $slug ] ) : '';

		if ( $page ) {

			$content = $page->post_content;

		} else {

			$current = LLMS_Student_Dashboard::get_current_tab();

			if ( ! is_callable( $current['content'] ?? '' ) ) {
				return '';
			}

			ob_start();

			call_user_func( $current['content'] );

			$content = ob_get_clean();

		}

		return $content;

	}

}

return LLMS_Shortcode_Dashboard_Current::instance();
