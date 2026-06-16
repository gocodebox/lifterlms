<?php
/**
 * Abstract base class for LifterLMS Elementor widgets.
 *
 * @package LifterLMS/Classes/Elementor
 *
 * @since 7.7.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Elementor_Widget_Base abstract class.
 *
 * @since 7.7.0
 * @since [version] Added enrollment visibility controls and server-side render gate.
 */
abstract class LLMS_Elementor_Widget_Base extends \Elementor\Widget_Base {

	/**
	 * Constructor.
	 *
	 * @since 7.7.0
	 *
	 * @param array      $data Widget data.
	 * @param array|null $args Widget arguments.
	 * @return void
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 7.7.0
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dashicons-before dashicons-welcome-learn-more';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 7.7.0
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'lifterlms' );
	}

	/**
	 * Register the enrollment visibility controls section.
	 *
	 * Adds an "Enrollment Visibility" section under the Advanced tab of every
	 * LifterLMS Elementor widget, mirroring the visibility option available on
	 * standard Gutenberg blocks.
	 *
	 * Subclasses call this near the end of their `_register_controls()` method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function add_visibility_controls() {

		$this->start_controls_section(
			'llms_visibility_section',
			array(
				'label' => esc_html__( 'Enrollment Visibility', 'lifterlms' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			)
		);

		$this->add_control(
			'llms_visibility',
			array(
				'label'   => esc_html__( 'Display to', 'lifterlms' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'          => esc_html__( 'everyone', 'lifterlms' ),
					'enrolled'     => esc_html__( 'enrolled users', 'lifterlms' ),
					'not_enrolled' => esc_html__( 'non-enrolled users or visitors', 'lifterlms' ),
					'logged_in'    => esc_html__( 'logged in users', 'lifterlms' ),
					'logged_out'   => esc_html__( 'logged out users', 'lifterlms' ),
				),
			)
		);

		$this->add_control(
			'llms_visibility_in',
			array(
				'label'     => esc_html__( 'Enrolled In', 'lifterlms' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'any',
				'options'   => array(
					'any'            => esc_html__( 'in any course or membership', 'lifterlms' ),
					'any_course'     => esc_html__( 'in any course', 'lifterlms' ),
					'any_membership' => esc_html__( 'in any membership', 'lifterlms' ),
					'this'           => esc_html__( 'in this course or membership', 'lifterlms' ),
				),
				'condition' => array(
					'llms_visibility' => array( 'enrolled', 'not_enrolled' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Add a footer promo control linking to Elementor documentation.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	protected function add_footer_promo_control() {

		$this->add_control(
			'llms_footer_promo',
			array(
				'label'           => '',
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<hr><p style="margin-top: 20px;">' .
									/* translators: %1$s: Opening learn more link tag, %2$s: Closing link tag. */
									sprintf( esc_html__( 'Learn more about %1$sediting LifterLMS courses with Elementor%2$s', 'lifterlms' ), '<a target="_blank" href="https://lifterlms.com/docs/how-to-edit-courses-with-elementor/?utm_source=LifterLMS%20Plugin&utm_medium=Elementor%20Edit%20Panel%20&utm_campaign=Plugin%20to%20Sale">', '</a>' ) .
									'</p>',
				'content_classes' => 'lifterlms-notice',
			)
		);
	}

	/**
	 * Render the widget output.
	 *
	 * Checks enrollment visibility before delegating to `render_widget()`.
	 * When the current visitor does not meet the configured condition the
	 * widget outputs nothing. Inside the Elementor editor the widget always
	 * renders so authors can see and configure it regardless of their
	 * own enrollment status.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function render() {

		// Always render inside the Elementor editor.
		if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$settings = $this->get_settings_for_display();
			$attrs    = array(
				'llms_visibility'    => isset( $settings['llms_visibility'] ) ? $settings['llms_visibility'] : 'all',
				'llms_visibility_in' => isset( $settings['llms_visibility_in'] ) ? $settings['llms_visibility_in'] : '',
			);

			if ( ! $this->is_visible( $attrs ) ) {
				return;
			}
		}

		$this->render_widget();
	}

	/**
	 * Determine whether the widget should be visible to the current visitor.
	 *
	 * Evaluates the enrollment visibility attributes saved on the widget
	 * against the current user's login state and enrollment records. Returns
	 * `true` when the widget should be shown, `false` when it should be hidden.
	 *
	 * Supported `llms_visibility` values:
	 *   - `all`          — always visible (default).
	 *   - `logged_in`    — visible to logged-in users only.
	 *   - `logged_out`   — visible to logged-out visitors only.
	 *   - `enrolled`     — visible when the user is enrolled per `llms_visibility_in`.
	 *   - `not_enrolled` — visible when the user is NOT enrolled per `llms_visibility_in`.
	 *
	 * Supported `llms_visibility_in` values (for enrolled / not_enrolled):
	 *   - `any`            — enrolled in any course or membership.
	 *   - `any_course`     — enrolled in any course.
	 *   - `any_membership` — enrolled in any membership.
	 *   - `this`           — enrolled in the current post (resolved via `get_the_ID()`).
	 *
	 * @since [version]
	 *
	 * @param array $attrs {
	 *     Visibility attribute map.
	 *
	 *     @type string $llms_visibility    Primary visibility mode. Default 'all'.
	 *     @type string $llms_visibility_in Sub-mode for enrolled / not_enrolled. Default 'any'.
	 * }
	 * @return bool `true` if the widget should be shown to the current visitor.
	 */
	protected function is_visible( $attrs ) {

		$visibility = ! empty( $attrs['llms_visibility'] ) ? $attrs['llms_visibility'] : 'all';

		// Always show when set to everyone.
		if ( 'all' === $visibility ) {
			return true;
		}

		$uid = get_current_user_id();

		// Show only to logged-in users.
		if ( 'logged_in' === $visibility ) {
			return (bool) $uid;
		}

		// Show only to logged-out visitors.
		if ( 'logged_out' === $visibility ) {
			return ! $uid;
		}

		$visibility_in = ! empty( $attrs['llms_visibility_in'] ) ? $attrs['llms_visibility_in'] : 'any';

		if ( 'enrolled' === $visibility ) {

			// Must be logged in to be enrolled.
			if ( ! $uid ) {
				return false;
			}

			return $this->check_enrollment( $uid, $visibility_in );
		}

		if ( 'not_enrolled' === $visibility ) {

			// Logged-out visitors are never enrolled.
			if ( ! $uid ) {
				return true;
			}

			return ! $this->check_enrollment( $uid, $visibility_in );
		}

		// Unknown mode — show by default.
		return true;
	}

	/**
	 * Check whether a user meets the enrollment condition.
	 *
	 * @since [version]
	 *
	 * @param int    $uid           WP_User ID.
	 * @param string $visibility_in Enrollment sub-option: 'any', 'any_course', 'any_membership', or 'this'.
	 * @return bool
	 */
	private function check_enrollment( $uid, $visibility_in ) {

		if ( 'this' === $visibility_in ) {
			return (bool) llms_is_user_enrolled( $uid, get_the_ID() );
		}

		$student = llms_get_student( $uid );
		if ( ! $student ) {
			return false;
		}

		if ( in_array( $visibility_in, array( 'any', 'any_course' ), true ) ) {
			$result = $student->get_enrollments( 'course', array( 'limit' => 1 ) );
			if ( ! empty( $result['found'] ) ) {
				return true;
			}
		}

		if ( in_array( $visibility_in, array( 'any', 'any_membership' ), true ) ) {
			$result = $student->get_enrollments( 'membership', array( 'limit' => 1 ) );
			if ( ! empty( $result['found'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Render the widget-specific front-end output.
	 *
	 * Subclasses implement this method to output their content. It is called
	 * by `render()` only after the enrollment visibility check passes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	abstract protected function render_widget();

	/**
	 * Elementor live preview template.
	 *
	 * @since 7.7.0
	 *
	 * @return void
	 */
	protected function _content_template() {
		// Intentionally left empty — widgets use server-side rendering only.
	}
}
