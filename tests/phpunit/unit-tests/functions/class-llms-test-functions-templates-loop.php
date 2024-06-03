<?php
/**
 * Test page functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_loop
 *
 * @since 3.38.0
 */
class LLMS_Test_Functions_Loop extends LLMS_UnitTestCase {

	/**
	 * Test lifterlms_get_archive_description() and lifterlms_archive_description() on course and course taxonomy catalogs.
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_lifterlms_get_archive_description_courses() {

		LLMS_Install::create_pages();

		// On courses page with no description.
		$this->go_to( get_post_type_archive_link( 'course' ) );
		$this->assertEquals( '', lifterlms_get_archive_description() );
		$this->assertEquals( '', $this->get_output( 'lifterlms_archive_description' ) );

		// On courses page with a description.
		wp_update_post( array(
			'ID'           => llms_get_page_id( 'courses' ),
			'post_content' => 'Archive Description',
		) );
		$this->assertEquals( llms_content( 'Archive Description' ), lifterlms_get_archive_description() );
		$this->assertEquals( llms_content( 'Archive Description' ), $this->get_output( 'lifterlms_archive_description' ) );

		// On a tax archive page with no tax description.
		$term = wp_insert_term( 'mock-cat', 'course_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertEquals( llms_content( 'Archive Description' ), lifterlms_get_archive_description() );
		$this->assertEquals( llms_content( 'Archive Description' ), $this->get_output( 'lifterlms_archive_description' ) );

		// On a tax archive page with a tax description.
		$term = wp_insert_term( 'mock-cat-with-desc', 'course_cat', array( 'description' => 'Term desc.' ) );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertEquals( llms_content( 'Term desc.' ), lifterlms_get_archive_description() );
		$this->assertEquals( llms_content( 'Term desc.' ), $this->get_output( 'lifterlms_archive_description' ) );

	}

	/**
	 * Test lifterlms_get_archive_description() and lifterlms_archive_description() on membership and membership taxonomy catalogs.
	 *
	 * @since 4.10.0
	 *
	 * @return void
	 */
	public function test_lifterlms_get_archive_description_memberships() {

		LLMS_Install::create_pages();

		// On courses page with no description.
		$this->go_to( get_post_type_archive_link( 'llms_membership' ) );
		$this->assertEquals( '', lifterlms_get_archive_description() );
		$this->assertEquals( '', $this->get_output( 'lifterlms_archive_description' ) );

		// On courses page with a description.
		wp_update_post( array(
			'ID'           => llms_get_page_id( 'memberships' ),
			'post_content' => 'Archive Description',
		) );
		$this->assertEquals( llms_content( 'Archive Description' ), lifterlms_get_archive_description() );
		$this->assertEquals( llms_content( 'Archive Description' ), $this->get_output( 'lifterlms_archive_description' ) );

		// On a tax archive page with no tax description.
		$term = wp_insert_term( 'mock-cat', 'membership_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertEquals( llms_content( 'Archive Description' ), lifterlms_get_archive_description() );
		$this->assertEquals( llms_content( 'Archive Description' ), $this->get_output( 'lifterlms_archive_description' ) );

		// On a tax archive page with a tax description.
		$term = wp_insert_term( 'mock-cat-with-desc', 'membership_cat', array( 'description' => 'Term desc.' ) );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertEquals( llms_content( 'Term desc.' ), lifterlms_get_archive_description() );
		$this->assertEquals( llms_content( 'Term desc.' ), $this->get_output( 'lifterlms_archive_description' ) );

	}

}
