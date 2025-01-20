<?php
/**
 * LifterLMS Membership Instructors Module
 *
 * @package LifterLMS_Labs/Labs/BeaverBuilder/Modules/MembershipInstructors/Classes
 *
 * @since 8.0.0
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Lab_Membership_Instructors_Module extends FLBUilderModule {
	public function __construct() {
		parent::__construct(
			array(
				'name'          => esc_html__( 'Membership Instructors', 'lifterlms' ),
				'description'   => esc_html__( 'Displays instructors for the current membership.', 'lifterlms' ),
				'category'      => esc_html__( 'LifterLMS Modules', 'lifterlms' ),
				'dir'           => LLMS_BB_MODULES_DIR . 'membership-instructors/',
				'url'           => LLMS_BB_MODULES_URL . 'membership-instructors/',
				'editor_export' => false,
				'enabled'       => true,
			)
		);
	}
}

FLBuilder::register_module( 'LLMS_Lab_Membership_Instructors_Module', array() );
