<?php
/**
 * Test Order Functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_conditional_tags
 *
 * @since [version]
 */
class LLMS_Test_Functions_Conditional_Tags extends LLMS_UnitTestCase {

	/**
	 * Test the is_course() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_course() {

		$this->assertFalse( is_course() );

		$this->go_to( home_url() );
		$this->assertFalse( is_course() );

		$this->go_to( get_permalink( $this->factory->post->create() ) );
		$this->assertFalse( is_course() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'course' ) ) ) );
		$this->assertTrue( is_course() );

	}

	/**
	 * Test is_course_category() function.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return [type]
	 */
	public function test_is_course_category() {

		$this->assertFalse( is_course_category() );

		$this->go_to( home_url() );
		$this->assertFalse( is_course_category() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'course' ) ) ) );
		$this->assertFalse( is_course_category() );

		$term = wp_create_tag( 'mock-tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertFalse( is_course_category() );

		// Cat not specified.
		$term = wp_create_term( 'mock-cat', 'course_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_course_category() );
		$this->assertTrue( is_course_category( $term['term_id'] ) );
		$this->assertTrue( is_course_category( array( $term['term_id'] ) ) );

		// Another term.
		$term_2 = wp_create_term( 'mock-cat-2', 'course_cat' );
		$this->go_to( get_term_link( $term_2['term_id'] ) );
		$this->assertTrue( is_course_category() );

		// We're on the other term's page.
		$this->assertFalse( is_course_category( $term['term_id'] ) );

		// One of passed terms.
		$this->assertTrue( is_course_category( array( $term['term_id'], $term_2['term_id'] ) ) );

	}

	/**
	 * Test is_course_tag() function.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return [type]
	 */
	public function test_is_course_tag() {

		$this->assertFalse( is_course_tag() );

		$this->go_to( home_url() );
		$this->assertFalse( is_course_tag() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'course' ) ) ) );
		$this->assertFalse( is_course_tag() );

		$term = wp_create_tag( 'mock-tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertFalse( is_course_tag() );

		// Cat not specified.
		$term = wp_create_term( 'mock-cat', 'course_tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_course_tag() );
		$this->assertTrue( is_course_tag( $term['term_id'] ) );
		$this->assertTrue( is_course_tag( array( $term['term_id'] ) ) );

		// Another term.
		$term_2 = wp_create_term( 'mock-cat-2', 'course_tag' );
		$this->go_to( get_term_link( $term_2['term_id'] ) );
		$this->assertTrue( is_course_tag() );

		// We're on the other term's page.
		$this->assertFalse( is_course_tag( $term['term_id'] ) );

		// One of passed terms.
		$this->assertTrue( is_course_tag( array( $term['term_id'], $term_2['term_id'] ) ) );

	}

	/**
	 * Test is_course_tag() function.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return [type]
	 */
	public function test_is_course_taxonomy() {

		$this->assertFalse( is_course_taxonomy() );

		$this->go_to( home_url() );
		$this->assertFalse( is_course_taxonomy() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'course' ) ) ) );
		$this->assertFalse( is_course_taxonomy() );

		$term = wp_create_tag( 'mock-tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertFalse( is_course_taxonomy() );

		// Cat.
		$term = wp_create_term( 'mock-cat', 'course_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_course_taxonomy() );

		// Tag.
		$term = wp_create_term( 'mock-tag', 'course_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_course_taxonomy() );

	}

	/**
	 * Test is_courses()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_courses() {

		LLMS_Install::create_pages();

		$this->assertFalse( is_courses() );

		$this->go_to( home_url() );
		$this->assertFalse( is_courses() );

		$this->go_to( get_post_type_archive_link( 'llms_membership' ) );
		$this->assertFalse( is_courses() );

		$this->go_to( get_post_type_archive_link( 'course' ) );
		$this->assertTrue( is_courses() );

		$this->go_to( get_permalink( llms_get_page_id( 'courses' ) ) );
		$this->assertTrue( is_courses() );

	}

	/**
	 * Test the is_lesson() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_lesson() {

		$this->assertFalse( is_lesson() );

		$this->go_to( home_url() );
		$this->assertFalse( is_lesson() );

		$this->go_to( get_permalink( $this->factory->post->create() ) );
		$this->assertFalse( is_lesson() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'lesson' ) ) ) );
		$this->assertTrue( is_lesson() );

	}

	/**
	 * Test is_lifterlms() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_lifterlms() {

		$this->assertFalse( is_lifterlms() );

		$this->go_to( home_url() );
		$this->assertFalse( is_lifterlms() );

		$post_types = array(
			'post' => false,
			'course' => true,
			'lesson' => true,
			'llms_quiz' => true,
			'llms_membership' => true,
		);
		foreach( $post_types as $post_type => $expect ) {

			// Single post type.
			$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => $post_type ) ) ) );
			$this->assertEquals( $expect, is_lifterlms() );

			if ( ! in_array( $post_type, array( 'lesson', 'llms_quiz' ), true ) ) {

				// Archive page.
				$this->go_to( get_post_type_archive_link( $post_type ) );
				$this->assertEquals( $expect, is_lifterlms(), $post_type );

			}

		}

		$term = wp_create_term( 'mock-cat', 'course_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_lifterlms() );

		$term = wp_create_term( 'mock-cat', 'membership_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_lifterlms() );

		$term = wp_create_tag( 'mock-tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertFalse( is_lifterlms() );

	}

	/**
	 * Test the is_llms_account_page() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_llms_account_page() {

		LLMS_Install::create_pages();

		$this->assertFalse( is_llms_account_page() );

		$this->go_to( home_url() );
		$this->assertFalse( is_llms_account_page() );

		add_filter( 'lifterlms_is_account_page', '__return_true' );
		$this->assertTrue( is_llms_account_page() );
		remove_filter( 'lifterlms_is_account_page', '__return_true' );

		$this->go_to( get_permalink( llms_get_page_id( 'myaccount' ) ) );
		$this->assertTrue( is_llms_account_page() );

	}

	/**
	 * Test the is_llms_checkout() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_llms_checkout() {

		LLMS_Install::create_pages();

		$this->assertFalse( is_llms_checkout() );

		$this->go_to( home_url() );
		$this->assertFalse( is_llms_checkout() );

		$this->go_to( get_permalink( llms_get_page_id( 'checkout' ) ) );
		$this->assertTrue( is_llms_checkout() );

	}

	/**
	 * Test the is_membership() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_membership() {

		$this->assertFalse( is_membership() );

		$this->go_to( home_url() );
		$this->assertFalse( is_membership() );

		$this->go_to( get_permalink( $this->factory->post->create() ) );
		$this->assertFalse( is_membership() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'llms_membership' ) ) ) );
		$this->assertTrue( is_membership() );

	}

	/**
	 * Test is_membership_category() function.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return [type]
	 */
	public function test_is_membership_category() {

		$this->assertFalse( is_membership_category() );

		$this->go_to( home_url() );
		$this->assertFalse( is_membership_category() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'membership' ) ) ) );
		$this->assertFalse( is_membership_category() );

		$term = wp_create_tag( 'mock-tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertFalse( is_membership_category() );

		// Cat not specified.
		$term = wp_create_term( 'mock-cat', 'membership_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_membership_category() );
		$this->assertTrue( is_membership_category( $term['term_id'] ) );
		$this->assertTrue( is_membership_category( array( $term['term_id'] ) ) );

		// Another term.
		$term_2 = wp_create_term( 'mock-cat-2', 'membership_cat' );
		$this->go_to( get_term_link( $term_2['term_id'] ) );
		$this->assertTrue( is_membership_category() );

		// We're on the other term's page.
		$this->assertFalse( is_membership_category( $term['term_id'] ) );

		// One of passed terms.
		$this->assertTrue( is_membership_category( array( $term['term_id'], $term_2['term_id'] ) ) );

	}

	/**
	 * Test is_membership_tag() function.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return [type]
	 */
	public function test_is_membership_tag() {

		$this->assertFalse( is_membership_tag() );

		$this->go_to( home_url() );
		$this->assertFalse( is_membership_tag() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'membership' ) ) ) );
		$this->assertFalse( is_membership_tag() );

		$term = wp_create_tag( 'mock-tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertFalse( is_membership_tag() );

		// Cat not specified.
		$term = wp_create_term( 'mock-cat', 'membership_tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_membership_tag() );
		$this->assertTrue( is_membership_tag( $term['term_id'] ) );
		$this->assertTrue( is_membership_tag( array( $term['term_id'] ) ) );

		// Another term.
		$term_2 = wp_create_term( 'mock-cat-2', 'membership_tag' );
		$this->go_to( get_term_link( $term_2['term_id'] ) );
		$this->assertTrue( is_membership_tag() );

		// We're on the other term's page.
		$this->assertFalse( is_membership_tag( $term['term_id'] ) );

		// One of passed terms.
		$this->assertTrue( is_membership_tag( array( $term['term_id'], $term_2['term_id'] ) ) );

	}

	/**
	 * Test is_membership_tag() function.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return [type]
	 */
	public function test_is_membership_taxonomy() {

		$this->assertFalse( is_membership_taxonomy() );

		$this->go_to( home_url() );
		$this->assertFalse( is_membership_taxonomy() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'membership' ) ) ) );
		$this->assertFalse( is_membership_taxonomy() );

		$term = wp_create_tag( 'mock-tag' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertFalse( is_membership_taxonomy() );

		// Cat.
		$term = wp_create_term( 'mock-cat', 'membership_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_membership_taxonomy() );

		// Tag.
		$term = wp_create_term( 'mock-tag', 'membership_cat' );
		$this->go_to( get_term_link( $term['term_id'] ) );
		$this->assertTrue( is_membership_taxonomy() );

	}

	/**
	 * Test is_memberships()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_memberships() {

		LLMS_Install::create_pages();

		$this->assertFalse( is_memberships() );

		$this->go_to( home_url() );
		$this->assertFalse( is_memberships() );

		$this->go_to( get_post_type_archive_link( 'course' ) );
		$this->assertFalse( is_memberships() );

		$this->go_to( get_post_type_archive_link( 'llms_membership' ) );
		$this->assertTrue( is_memberships() );

		$this->go_to( get_permalink( llms_get_page_id( 'memberships' ) ) );
		$this->assertTrue( is_memberships() );

	}

	/**
	 * Test the is_quiz() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_quiz() {

		$this->assertFalse( is_quiz() );

		$this->go_to( home_url() );
		$this->assertFalse( is_quiz() );

		$this->go_to( get_permalink( $this->factory->post->create() ) );
		$this->assertFalse( is_quiz() );

		$this->go_to( get_permalink( $this->factory->post->create( array( 'post_type' => 'llms_quiz' ) ) ) );
		$this->assertTrue( is_quiz() );

	}

}
