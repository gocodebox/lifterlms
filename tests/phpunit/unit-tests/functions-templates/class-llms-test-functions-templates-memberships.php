<?php
/**
 * Membership template function tests
 *
 * @group functions
 * @group template_functions_memberships
 * @group template_functions
 *
 * @since 4.11.0
 */
class LLMS_Test_Functions_Templates_Memberships extends LLMS_Unit_Test_Case {

	/**
	 * Test llms_template_membership_instructors()
	 *
	 * @since 4.11.0
	 *
	 * @return void
	 */
	public function test_llms_template_membership_auinstructors() {

		$user  = $this->factory->user->create_and_get( array(
			'first_name'  => 'Jimothy',
			'last_name'   => 'Halpert',
			'description' => 'Paper salesman at Dunder Mifflin Scranton.'
		) );
		$user2 = $this->factory->user->create_and_get( array(
			'first_name'  => 'Dwight',
			'last_name'   => 'Schrute',
			'description' => 'Assistant <em>to</em> the Regional Manager at Dunder Mifflin Scranton.'
		) );

		global $post;
		$post   = $this->factory->post->create_and_get( array(
			'post_type'   => 'llms_membership',
			'post_author' => $user->ID,
		) );
		$membership = llms_get_post( $post );

		// One user (default post author).
		$membership->instructors()->set_instructors( array() );

		$template = $this->get_output( 'llms_template_membership_instructors' );

		$this->assertStringContains( 'Membership Instructor', $template );
		$this->assertStringContains( '<div class="llms-col-1">', $template );
		$this->assertStringContains( '<span class="llms-author-info name">Jimothy Halpert</span>', $template );
		$this->assertStringContains( '<span class="llms-author-info label">Author</span>', $template );
		$this->assertStringContains( '<p class="llms-author-info bio">Paper salesman at Dunder Mifflin Scranton.</p>', $template );

		// Two Instructors.
		$membership->instructors()->set_instructors( array( array( 'id' => $user->ID ), array( 'id' => $user2->ID ) ) );

		$template = $this->get_output( 'llms_template_membership_instructors' );

		$this->assertStringContains( 'Membership Instructors', $template );
		$this->assertStringContains( '<div class="llms-col-2">', $template );

		$this->assertStringContains( '<span class="llms-author-info name">Jimothy Halpert</span>', $template );
		$this->assertStringContains( '<p class="llms-author-info bio">Paper salesman at Dunder Mifflin Scranton.</p>', $template );

		$this->assertStringContains( '<span class="llms-author-info name">Dwight Schrute</span>', $template );
		$this->assertStringContains( '<p class="llms-author-info bio">Assistant <em>to</em> the Regional Manager at Dunder Mifflin Scranton.</p>', $template );

	}

}
