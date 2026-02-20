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
	 * Get the promo HTML.
	 *
	 * @since 7.8.0
	 *
	 * @return string
	 */
	private function get_promo_html() {
		$html  = '<div style="padding:20px;text-align:center;">';
		$html .= '<span class="dashicons dashicons-calendar-alt" style="font-size:48px;color:#2271b1;margin-bottom:12px;"></span>';
		$html .= '<h3 style="margin:0 0 8px;">' . esc_html__( 'Schedule Events for Your Students', 'lifterlms' ) . '</h3>';
		$html .= '<p>' . esc_html__( 'Add live events, webinars, and in-person sessions to your courses and memberships. Students can subscribe to calendar feeds and never miss an event.', 'lifterlms' ) . '</p>';
		$html .= '<a href="https://lifterlms.com/product/events/?utm_source=LifterLMS%20Plugin&utm_medium=Course%20Editor&utm_campaign=Events%20Promo" target="_blank" class="button button-primary">';
		$html .= esc_html__( 'Get LifterLMS Events', 'lifterlms' );
		$html .= '</a>';
		$html .= '</div>';

		return $html;
	}
}

new LLMS_Admin_Events_Promo();
