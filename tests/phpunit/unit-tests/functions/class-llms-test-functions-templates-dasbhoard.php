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

	/**
	 * Enroll a student into a set number of memberships and return the membership post IDs.
	 *
	 * @param int $user_id Student user ID.
	 * @param int $count   Number of memberships to create and enroll into.
	 * @return int[]
	 */
	private function enroll_in_memberships( $user_id, $count ) {

		$membership_ids = $this->factory->post->create_many( $count, array( 'post_type' => 'llms_membership' ) );

		foreach ( $membership_ids as $membership_id ) {
			llms_enroll_student( $user_id, $membership_id );
		}

		return $membership_ids;

	}

	/**
	 * Test lifterlms_template_my_memberships_loop() when the student's membership count is under the default limit.
	 *
	 * @return void
	 */
	public function test_lifterlms_template_my_memberships_loop_under_limit() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$membership_ids = $this->enroll_in_memberships( $user_id, 3 );

		$output = $this->get_output( 'lifterlms_template_my_memberships_loop' );

		foreach ( $membership_ids as $membership_id ) {
			$this->assertStringContainsString( get_the_title( $membership_id ), $output );
		}

	}

	/**
	 * Test lifterlms_template_my_memberships_loop() treats a falsy `llms_my_memberships_loop_limit` filter value as "no limit".
	 *
	 * @return void
	 */
	public function test_lifterlms_template_my_memberships_loop_filter_zero_disables_limit() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$membership_ids = $this->enroll_in_memberships( $user_id, 3 );

		add_filter( 'llms_my_memberships_loop_limit', '__return_zero' );
		$output = $this->get_output( 'lifterlms_template_my_memberships_loop' );
		remove_filter( 'llms_my_memberships_loop_limit', '__return_zero' );

		foreach ( $membership_ids as $membership_id ) {
			$this->assertStringContainsString( get_the_title( $membership_id ), $output );
		}

	}

	/**
	 * Test lifterlms_template_my_memberships_loop() respects the `llms_my_memberships_loop_limit` filter.
	 *
	 * @return void
	 */
	public function test_lifterlms_template_my_memberships_loop_filtered_limit() {

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$membership_ids = $this->enroll_in_memberships( $user_id, 3 );

		$limit = function() {
			return 1;
		};

		add_filter( 'llms_my_memberships_loop_limit', $limit );
		$output = $this->get_output( 'lifterlms_template_my_memberships_loop' );
		remove_filter( 'llms_my_memberships_loop_limit', $limit );

		$rendered = array_filter(
			$membership_ids,
			function( $membership_id ) use ( $output ) {
				return false !== strpos( $output, get_the_title( $membership_id ) );
			}
		);

		$this->assertCount( 1, $rendered );

	}

}
