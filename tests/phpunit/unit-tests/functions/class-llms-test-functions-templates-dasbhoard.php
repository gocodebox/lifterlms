<?php
/**
 * Test dashboard template functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_template
 * @group functions_template_dashboard
 *
 * @since 6.0.0
 */
class LLMS_Test_Functions_Templates_Dashboard extends LLMS_UnitTestCase {

	/**
	 * Test lifterlms_template_student_dashboard_my_achievements() with no student.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_student_dashboard_my_achievements_no_student() {

		wp_set_current_user( null );

		$this->assertOutputEmpty( 'lifterlms_template_student_dashboard_my_achievements' );

	}

	/**
	 * Test lifterlms_template_student_dashboard_my_achievements() when the endpoint is disabled.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_student_dashboard_my_achievements_disabled() {

		wp_set_current_user( $this->factory->user->create() );

		update_option( 'lifterlms_myaccount_achievements_endpoint', '' );

		$this->assertOutputEmpty( 'lifterlms_template_student_dashboard_my_achievements' );

	}

	/**
	 * Test lifterlms_template_student_dashboard_my_achievements() when showing a preview.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_student_dashboard_my_achievements_preview() {

		wp_set_current_user( $this->factory->user->create() );

		$output = $this->get_output( 'lifterlms_template_student_dashboard_my_achievements', array( true ) );

		$this->assertStringContainsString( '<section class="llms-sd-section llms-my-achievements">', $output );
		$this->assertStringContainsString( '<h3 class="llms-sd-section-title">', $output );
		$this->assertStringContainsString( '<a class="llms-button-secondary" href="?my-achievements">View All My Achievements</a>', $output );

	}

	/**
	 * Test lifterlms_template_student_dashboard_my_achievements() when showing all.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_student_dashboard_my_achievements_all() {

		wp_set_current_user( $this->factory->user->create() );

		$output = $this->get_output( 'lifterlms_template_student_dashboard_my_achievements' );

		$this->assertStringContainsString( '<section class="llms-sd-section llms-my-achievements">', $output );

		$this->assertStringNotContainsString( '<h3 class="llms-sd-section-title">', $output );
		$this->assertStringNotContainsString( '<a class="llms-button-secondary" href="?my-achievements">View All My Achievements</a>', $output );

	}

	/**
	 * Test lifterlms_template_student_dashboard_my_certificates() with no student.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_student_dashboard_my_certificates_no_student() {

		wp_set_current_user( null );

		$this->assertOutputEmpty( 'lifterlms_template_student_dashboard_my_certificates' );

	}

	/**
	 * Test lifterlms_template_student_dashboard_my_certificates() when the endpoint is disabled.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_student_dashboard_my_certificates_disabled() {

		wp_set_current_user( $this->factory->user->create() );

		update_option( 'lifterlms_myaccount_certificates_endpoint', '' );

		$this->assertOutputEmpty( 'lifterlms_template_student_dashboard_my_certificates' );

	}

	/**
	 * Test lifterlms_template_student_dashboard_my_certificates() when showing a preview.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_student_dashboard_my_certificates_preview() {

		wp_set_current_user( $this->factory->user->create() );

		$output = $this->get_output( 'lifterlms_template_student_dashboard_my_certificates', array( true ) );

		$this->assertStringContainsString( '<section class="llms-sd-section llms-my-certificates">', $output );
		$this->assertStringContainsString( '<h3 class="llms-sd-section-title">', $output );
		$this->assertStringContainsString( '<a class="llms-button-secondary" href="?my-certificates">View All My Certificates</a>', $output );

	}

	/**
	 * Test lifterlms_template_student_dashboard_my_certificates() when showing all.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_lifterlms_template_student_dashboard_my_certificates_all() {

		wp_set_current_user( $this->factory->user->create() );

		$output = $this->get_output( 'lifterlms_template_student_dashboard_my_certificates' );

		$this->assertStringContainsString( '<section class="llms-sd-section llms-my-certificates">', $output );

		$this->assertStringNotContainsString( '<h3 class="llms-sd-section-title">', $output );
		$this->assertStringNotContainsString( '<a class="llms-button-secondary" href="?my-certificates">View All My Certificates</a>', $output );

	}

}
