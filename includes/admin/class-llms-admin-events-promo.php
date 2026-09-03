<?php
/**
 * Lightweight Events add-on promotion in the LifterLMS core admin.
 *
 * Shows an "Events" tab in Course Options and Membership meta boxes
 * with a CTA to install the Events add-on when it is not active.
 *
 * Also adds an "Events" link in Course Builder lesson settings.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 7.8.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Events_Promo class.
 *
 * @since 7.8.0
 */
class LLMS_Admin_Events_Promo {

	/**
	 * Constructor.
	 *
	 * @since 7.8.0
	 */
	public function __construct() {
		// Only show promo if the events plugin is not active.
		if ( class_exists( 'LLMS_Events_Plugin' ) ) {
			return;
		}

		add_filter( 'llms_metabox_fields_lifterlms_course_options', array( $this, 'add_course_promo_tab' ) );
		add_filter( 'llms_metabox_fields_lifterlms_membership', array( $this, 'add_membership_promo_tab' ) );
		add_filter( 'llms_metabox_fields_lifterlms_lesson', array( $this, 'add_lesson_promo_tab' ) );
	}

	/**
	 * Add an Events promo tab to Course Options.
	 *
	 * @since 7.8.0
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_course_promo_tab( $tabs ) {
		$tabs[] = array(
			'title'  => __( 'Events', 'lifterlms' ),
			'fields' => array(
				array(
					'id'    => '_llms_events_promo',
					'type'  => 'custom-html',
					'label' => '',
					'value' => $this->get_promo_html(),
				),
			),
		);
		return $tabs;
	}

	/**
	 * Add an Events promo tab to Membership.
	 *
	 * @since 7.8.0
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_membership_promo_tab( $tabs ) {
		$tabs[] = array(
			'title'  => __( 'Events', 'lifterlms' ),
			'fields' => array(
				array(
					'id'    => '_llms_events_promo',
					'type'  => 'custom-html',
					'label' => '',
					'value' => $this->get_promo_html(),
				),
			),
		);
		return $tabs;
	}

	/**
	 * Add an Events promo tab to Lesson Settings.
	 *
	 * @since 7.8.0
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_lesson_promo_tab( $tabs ) {
		$tabs[] = array(
			'title'  => __( 'Events', 'lifterlms' ),
			'fields' => array(
				array(
					'id'    => '_llms_events_promo',
					'type'  => 'custom-html',
					'label' => '',
					'value' => $this->get_promo_html(),
				),
			),
		);
		return $tabs;
	}

	/**
	 * Get the promo HTML.
	 *
	 * @since 7.8.0
	 *
	 * @return string
	 */
	private function get_promo_html() {
		return LLMS_Admin_Addon_Promo::get_html(
			array(
				'icon'        => 'calendar-alt',
				'headline'    => __( 'Schedule Events for Your Students', 'lifterlms' ),
				'message'     => __( 'Add live events, webinars, and in-person sessions to your courses and memberships. Students can subscribe to calendar feeds and never miss an event.', 'lifterlms' ),
				'button_text' => __( 'Get LifterLMS Events', 'lifterlms' ),
				'button_url'  => LLMS_Admin_Addon_Promo::get_utm_url(
					'https://lifterlms.com/product/lifterlms-events/',
					'Course Editor',
					'Events Promo'
				),
			)
		);
	}
}

new LLMS_Admin_Events_Promo();
