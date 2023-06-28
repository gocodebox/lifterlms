<?php
/**
 * LLMS_Shortcode_Dashboard class.
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since   [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Dashboard Section Shortcode.
 *
 * Shortcode: [lifterlms_dashboard]
 *
 * @since [version]
 */
class LLMS_Shortcode_Dashboard extends LLMS_Shortcode {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_dashboard';

	/**
	 * Retrieves an array of default attributes.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_default_attributes() {
		return [
			'section'    => null,
			'show_title' => 'true',
		];
	}

	/**
	 * Output the shortcode.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_output(): string {

		$atts = $this->get_attributes();

		if ( $atts['section'] ?? '' ) {

			$output = self::get_section( $atts['section'], $atts['show_title'] );

		} else {

			ob_start();

			lifterlms_student_dashboard( $atts, $this->get_content() );

			$output = ob_get_clean();

		}

		return str_replace(
			[ '<p></p>', '<p> </p>' ],
			'',
			$output
		);

	}

	/**
	 * Retrieve the actual content of the shortcode.
	 *
	 * @since [version]
	 *
	 * @param string $slug       Section name.
	 * @param string $show_title Whether to show the section title.
	 * @return string
	 */
	protected static function get_section( string $slug = '', string $show_title = 'true' ): string {

		if ( ! $slug ) {
			return '';
		}

		$defaults = [
			'content'      => fn() => self::render_content(),
			'navigation'   => fn() => llms_get_template( 'myaccount/navigation.php' ),
			'courses'      => fn() => lifterlms_template_student_dashboard_my_courses( true ),
			'achievements' => fn() => lifterlms_template_student_dashboard_my_achievements( true ),
			'certificates' => fn() => lifterlms_template_student_dashboard_my_certificates( true ),
			'memberships'  => fn() => lifterlms_template_student_dashboard_my_memberships( true ),
		];

		/**
		 * Dashboard section default content.
		 *
		 * @since [version]
		 *
		 * @param array $defaults Array of callbacks.
		 */
		$defaults = apply_filters( 'lifterlms_dashboard_defaults', $defaults );

		$page = get_page_by_path( $slug, OBJECT, 'llms_dashboard' );

		if ( $page && $page->post_content ) {
			return do_blocks( $page->post_content );
		}

		$content = $defaults[ $slug ] ?? '';

		if ( is_callable( $content ) ) {

			ob_start();

			if ( in_array( $show_title, [ 'true', 'yes', '1' ], true ) && 'content' === $slug ) {

				/**
				 * Display the dashboard header (without navigation).
				 *
				 * Also fires in the `myaccount/header` template.
				 *
				 * @since  [version]
				 *
				 * @hooked lifterlms_template_student_dashboard_title - 20
				 *
				 * @param bool $show_nav Whether to show the navigation.
				 */
				do_action( 'lifterlms_student_dashboard_header', false );

			}

			$content();

			$clean = ob_get_clean();

			return do_blocks( $clean );

		}

		return '';

	}

	/**
	 * Retrieve the actual content of the shortcode.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected static function render_content(): void {
		$slug = LLMS_Student_Dashboard::get_current_tab( 'slug' );

		$slugs = [
			'dashboard'         => 'dashboard',
			'view-courses'      => 'my-courses',
			'my-grades'         => 'my-grades',
			'view-certificates' => 'my-certificates',
			'view-memberships'  => 'my-memberships',
			'view-achievements' => 'my-achievements',
		];

		$page = isset( $slugs[ $slug ] ) ? get_page_by_path( $slugs[ $slug ], OBJECT, 'llms_dashboard' ) : '';

		if ( $page ) {

			$content = $page->post_content;

		} else {

			if ( 'dashboard' === $slug ) {

				ob_start();
				lifterlms_template_student_dashboard_my_courses( true );
				lifterlms_template_student_dashboard_my_achievements( true );
				lifterlms_template_student_dashboard_my_certificates( true );
				lifterlms_template_student_dashboard_my_memberships( true );

				$content = ob_get_clean();

			} else {

				$current = LLMS_Student_Dashboard::get_current_tab();

				if ( ! is_callable( $current['content'] ?? '' ) ) {
					return;
				}

				ob_start();

				call_user_func( $current['content'] );

				$content = ob_get_clean();

			}
		}

		echo do_shortcode( $content );

	}


}

return LLMS_Shortcode_Dashboard::instance();
