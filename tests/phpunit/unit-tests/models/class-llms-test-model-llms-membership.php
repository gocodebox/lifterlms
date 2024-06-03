<?php
/**
 * Tests for LifterLMS Membership Model.
 *
 * @group LLMS_Membership
 * @group LLMS_Post_Model
 *
 * @since 3.20.0
 * @since 3.36.3 Remove redundant test method `test_get_sections()`,
 *               @see tests/unit-tests/models/class-llms-test-model-llms-course.php.
 * @since 5.2.1 Add checks for empty URL and page ID in `test_has_sales_page_redirect()`.
 */
class LLMS_Test_LLMS_Membership extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class.
	 * @var string
	 */
	protected $class_name = 'LLMS_Membership';

	/**
	 * db post type of the model being tested.
	 * @var string
	 */
	protected $post_type = 'llms_membership';

	/**
	 * Get properties, used by test_getters_setters.
	 * This should match, exactly, the object's $properties array.
	 *
	 * @since 3.20.0
	 *
	 * @return array
	 */
	protected function get_properties() {
		return array(
			'auto_enroll' => 'array',
			'redirect_page_id' => 'absint',
			'restriction_add_notice' => 'yesno',
			'restriction_notice' => 'html',
			'restriction_redirect_type' => 'text',
			'redirect_custom_url' => 'text',
			'sales_page_content_page_id' => 'absint',
			'sales_page_content_type' => 'string',
			'sales_page_content_url' => 'string',
		);
	}

	/**
	 * Get data to fill a create post with.
	 * This is used by test_getters_setters.
	 *
	 * @since 3.20.0
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'auto_enroll' => array(),
			'redirect_page_id' => '1',
			'restriction_add_notice' => 'yes',
			'restriction_notice' => '<p>test</p>',
			'restriction_redirect_type' => 'none',
			'redirect_custom_url' => 'https://lifterlms.com',
			'sales_page_content_page_id' => 1,
			'sales_page_content_type' => 'none',
			'sales_page_content_url' => 'https://lifterlms.com',
		);
	}

	/**
	 * Test CRUD functions for auto enroll courses.
	 *
	 * Tests the following three methods:
	 *
	 *   + add_auto_enroll_courses()
	 *   + get_auto_enroll_courses()
	 *   + remoe_auto_enroll_course()
	 *
	 * @since 4.15.0
	 *
	 * @return void
	 */
	public function test_crud_auto_enroll() {

		$membership = $this->factory->membership->create_and_get();

		// No posts.
		$this->assertSame( array(), $membership->get_auto_enroll_courses() );

		// Add a single course (not an array).
		$course = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->assertTrue( $membership->add_auto_enroll_courses( $course ) );
		$this->assertSame( array( $course ), $membership->get_auto_enroll_courses() );

		// Add multiple courses (as an array).
		$courses = $this->factory->post->create_many( 2, array( 'post_type' => 'course' ) );
		$this->assertTrue( $membership->add_auto_enroll_courses( $courses ) );
		$this->assertEqualSets( array_merge( array( $course ), $courses ), $membership->get_auto_enroll_courses() );

		// Remove a course.
		$this->assertTrue( $membership->remove_auto_enroll_course( $course ) );
		$this->assertEqualSets( $courses, $membership->get_auto_enroll_courses() );

		// Add a course that already exists (should remove duplicates).
		$this->assertTrue( $membership->add_auto_enroll_courses( $courses[1] ) );
		$this->assertEqualSets( $courses, $membership->get_auto_enroll_courses() );

		// Add & replace.
		$this->assertTrue( $membership->add_auto_enroll_courses( $course, true ) );
		$this->assertEquals( array( $course ), $membership->get_auto_enroll_courses() );

	}

	/**
	 * Ensure only published courses
	 *
	 * @since 4.15.0
	 *
	 * @link https://github.com/gocodebox/lifterlms-groups/issues/135
	 *
	 * @return void
	 */
	public function test_get_auto_enroll_courses_published_only() {

		$membership = $this->factory->membership->create_and_get();
		$draft      = $this->factory->post->create( array( 'post_type' => 'course', 'post_status' => 'draft' ) );
		$private    = $this->factory->post->create( array( 'post_type' => 'course', 'post_status' => 'private' ) );
		$published  = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$this->assertTrue( $membership->add_auto_enroll_courses( array( $draft, $private, $published ) ) );
		$this->assertEqualSets( array( $published ), $membership->get_auto_enroll_courses() );

	}

	/**
	 * Test get_associated_posts() when none exist for the membership.
	 *
	 * @since 3.38.1
	 *
	 * @return void
	 */
	public function test_get_associated_posts_none_found() {

		$membership = $this->factory->membership->create_and_get();

		$this->assertEquals( array(), $membership->get_associated_posts() );

		$this->assertEquals( array(), $membership->get_associated_posts( 'course' ) );
		$this->assertEquals( array(), $membership->get_associated_posts( 'page' ) );
		$this->assertEquals( array(), $membership->get_associated_posts( 'post' ) );
		$this->assertEquals( array(), $membership->get_associated_posts( 'fake' ) );

	}

	/**
	 * Test get_associated_posts() when associations do exist.
	 *
	 * @since 3.38.1
	 * @since 4.15.0 Test equal sets instead of strict equals because we don't really care about the returned order.
	 *               Added tests to check when querying for a single post type.
	 *
	 * @return void
	 */
	public function test_get_associated_posts_has_associations() {

		$membership = $this->factory->membership->create_and_get();

		// Add a post.
		$post = $this->factory->post->create();
		update_post_meta( $post, '_llms_is_restricted', 'yes' );
		update_post_meta( $post, '_llms_restricted_levels', array( $membership->get( 'id' ), 1, 1008, '183' ) );

		// Add pages.
		$page1 = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_post_meta( $page1, '_llms_is_restricted', 'yes' );
		update_post_meta( $page1, '_llms_restricted_levels', array( (string) $membership->get( 'id' ) . '00', $membership->get( 'id' ), 1234, 2 ) );

		$page2 = $this->factory->post->create( array( 'post_type' => 'page' ) );
		update_post_meta( $page2, '_llms_is_restricted', 'yes' );
		update_post_meta( $page2, '_llms_restricted_levels', array( $membership->get( 'id' ) ) );

		// Add a course with a plan.
		$plan = $this->get_mock_plan();
		$plan->set( 'availability', 'members' );
		$plan->set( 'availability_restrictions', array( 1, $membership->get( 'id' ) ) );

		// Add an autoenrollment course.
		$course = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$membership->set( 'auto_enroll', array( $course, $plan->get( 'product_id' ) ) );

		// Get all associations.
		$res = $membership->get_associated_posts();

		$this->assertEquals( array( $post ), $res['post'] );
		$this->assertEqualSets( array( $page1, $page2 ), $res['page'] );
		$this->assertEqualSets( array( $plan->get( 'product_id' ), $course ), $res['course'] );

		// Get only course associations.
		$res = $membership->get_associated_posts( 'course' );
		$this->assertEqualSets( array( $plan->get( 'product_id' ), $course ), $res );

		// Get posts.
		$res = $membership->get_associated_posts( 'post' );
		$this->assertEquals( array( $post ), $res );

		// Get pages.
		$res = $membership->get_associated_posts( 'page' );
		$this->assertEqualSets( array( $page1, $page2 ), $res );

		// Fake post type.
		$res = $membership->get_associated_posts( 'fake' );
		$this->assertEqualSets( array(), $res );


	}

	/**
	 * Test LLMS_Membership->get_categories() method.
	 *
	 * @since 3.36.3
	 *
	 * @return void
	 */
	public function test_get_categories() {
		// create new membership
		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$membership    = new LLMS_Membership( $membership_id );

		// create new categories
		$taxonomy = 'membership_cat';
		$created_term_ids = array();
		for ( $i = 1; $i <= 3; $i ++ ) {
			$new_term_ids = wp_create_term( "mock-membership-category-$i", $taxonomy );
			$this->assertNotWPError( $new_term_ids );
			$created_term_ids[ $i ] = $new_term_ids['term_id'];
		}

		// set categories in membership
		$term_taxonomy_ids = wp_set_post_terms( $membership_id, $created_term_ids, $taxonomy );
		$this->assertNotWPError( $term_taxonomy_ids );
		$this->assertNotFalse( $term_taxonomy_ids );

		// get categories from membership
		$membership_terms = $membership->get_categories();
		$membership_term_ids = array();
		/** @var WP_Term $membership_term */
		foreach ( $membership_terms as $membership_term ) {
			$membership_term_ids[] = $membership_term->term_id;
		}

		// compare array values while ignoring keys and order
		$this->assertEqualSets( $created_term_ids, $membership_term_ids );
	}

	/**
	 * Test get_product()
	 *
	 * @since 4.15.0
	 *
	 * @return void
	 */
	public function test_get_product() {

		$membership = $this->factory->membership->create_and_get();

		$this->assertInstanceOf( 'LLMS_Product', $membership->get_product() );

	}

	/**
	 * Test LLMS_Membership->get_tags() method.
	 *
	 * @since 3.36.3
	 * @return void
	 */
	public function test_get_tags() {
		// create new membership
		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$membership    = new LLMS_Membership( $membership_id );

		// create new tags
		$taxonomy = 'membership_tag';
		$created_term_ids = array();
		for ( $i = 1; $i <= 3; $i ++ ) {
			$new_term_ids = wp_create_term( "mock-membership-tag-$i", $taxonomy );
			$this->assertNotWPError( $new_term_ids );
			$created_term_ids[ $i ] = $new_term_ids['term_id'];
		}

		// set tags in membership
		$term_taxonomy_ids = wp_set_post_terms( $membership_id, $created_term_ids, $taxonomy );
		$this->assertNotWPError( $term_taxonomy_ids );
		$this->assertNotFalse( $term_taxonomy_ids );

		// get tags from membership
		$membership_terms = $membership->get_tags();
		$membership_term_ids = array();
		/** @var WP_Term $membership_term */
		foreach ( $membership_terms as $membership_term ) {
			$membership_term_ids[] = $membership_term->term_id;
		}

		// compare array values while ignoring keys and order
		$this->assertEqualSets( $created_term_ids, $membership_term_ids );
	}

	/**
	 * Test get_sales_page_url method.
	 *
	 * @since 3.20.0
	 *
	 * @return void
	 */
	public function test_get_sales_page_url() {

		$course = new LLMS_Membership( 'new', 'Membership Title' );

		$this->assertEquals( get_permalink( $course->get( 'id' ) ), $course->get_sales_page_url() );

		$course->set( 'sales_page_content_type', 'none' );
		$this->assertEquals( get_permalink( $course->get( 'id' ) ), $course->get_sales_page_url() );

		$course->set( 'sales_page_content_type', 'content' );
		$this->assertEquals( get_permalink( $course->get( 'id' ) ), $course->get_sales_page_url() );

		$course->set( 'sales_page_content_type', 'url' );
		$course->set( 'sales_page_content_url', 'https://lifterlms.com' );
		$this->assertEquals( 'https://lifterlms.com', $course->get_sales_page_url() );

		$course->set( 'sales_page_content_type', 'page' );
		$page = $this->factory->post->create();
		$course->set( 'sales_page_content_page_id', $page );
		$this->assertEquals( get_permalink( $page ), $course->get_sales_page_url() );

	}

	/**
	 * Test the get students function.
	 *
	 * @since 3.12.0
	 *
	 * @return void
	 */
	public function test_get_students() {

		$this->create();

		$students = $this->factory->user->create_many( 10, array( 'role' => 'student' ) );
		foreach ( $students as $sid ) {
			llms_enroll_student( $sid, $this->obj->get( 'id' ), 'testing' );
		}

		$this->assertEquals( 5, count( $this->obj->get_students( array( 'enrolled' ), 5 ) ) );
		$this->assertEquals( 10, count( $this->obj->get_students() ) );

	}

	/**
	 * Test the `has_sales_page_redirect` method.
	 *
	 * @since 3.20.0
	 * @since 5.2.1 Add checks for empty URL and page ID.
	 */
	public function test_has_sales_page_redirect() {

		$membership = new LLMS_Membership( 'new', 'Membership Title' );

		$this->assertEquals( false, $membership->has_sales_page_redirect() );

		$membership->set( 'sales_page_content_type', 'none' );
		$this->assertEquals( false, $membership->has_sales_page_redirect() );

		$membership->set( 'sales_page_content_type', 'content' );
		$this->assertEquals( false, $membership->has_sales_page_redirect() );

		$membership->set( 'sales_page_content_type', 'url' );
		$this->assertEquals( false, $membership->has_sales_page_redirect() );

		$membership->set( 'sales_page_content_url', 'https://lifterlms.com' );
		$this->assertEquals( true, $membership->has_sales_page_redirect() );

		$membership->set( 'sales_page_content_type', 'page' );
		$this->assertEquals( false, $membership->has_sales_page_redirect() );

		$page_id = $this->factory()->post->create( array( 'post_type' => 'page' ) );
		$membership->set( 'sales_page_content_page_id', $page_id );
		$this->assertEquals( true, $membership->has_sales_page_redirect() );

	}

	/**
	 * Test query_associated_courses() to ensure only plan associations from published courses are returned.
	 *
	 * @since 4.15.0
	 *
	 * @link https://github.com/gocodebox/lifterlms-groups/issues/135
	 *
	 * @return void
	 */
	public function test_query_associated_courses_published_only() {

		$membership = $this->factory->membership->create_and_get();

		$plan = $this->get_mock_plan();
		$plan->set( 'availability', 'members' );
		$plan->set( 'availability_restrictions', array( 1, $membership->get( 'id' ) ) );

		$course = llms_get_post( $plan->get( 'product_id' ) );
		$course->set( 'status', 'draft' );

		$this->assertEquals( array(), LLMS_Unit_Test_Util::call_method( $membership, 'query_associated_courses' ) );

	}

}
