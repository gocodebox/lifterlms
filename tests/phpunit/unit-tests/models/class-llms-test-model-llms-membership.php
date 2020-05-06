<?php
/**
 * Tests for LifterLMS Membership Model.
 *
 * @group LLMS_Membership
 * @group LLMS_Post_Model
 *
 * @since 3.20.0
 * @since 3.36.3 Remove redundant test method `test_get_sections()`,
 *                @see tests/unit-tests/models/class-llms-test-model-llms-course.php.
 * @version 3.36.3
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
	 * Test get_associated_posts() when none exist for the membership.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_associated_posts_none_found() {

		$membership = $this->factory->membership->create_and_get();

		$expected = array(
			'post'   => array(),
			'page'   => array(),
			'course' => array(),
		);
		$this->assertEquals( $expected, $membership->get_associated_posts() );

		$this->assertEquals( array(), $membership->get_associated_posts( 'course' ) );
		$this->assertEquals( array(), $membership->get_associated_posts( 'page' ) );
		$this->assertEquals( array(), $membership->get_associated_posts( 'post' ) );
		$this->assertEquals( array(), $membership->get_associated_posts( 'fake' ) );

	}

	/**
	 * Test get_associated_posts() when associations do exist.
	 *
	 * @since [version]
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

		$res = $membership->get_associated_posts();

		$this->assertEquals( array( $post ), $res['post'] );
		$this->assertEquals( array( $page1, $page2 ), $res['page'] );
		$this->assertEquals( array( $plan->get( 'product_id' ), $course ), $res['course'] );

	}

	/**
	 * Test LLMS_Membership->get_categories() method.
	 *
	 * @since 3.36.3
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
	 * Test the has_sales_page_redirect method.
	 *
	 * @since 3.20.0
	 *
	 * @return void
	 */
	public function test_has_sales_page_redirect() {

		$course = new LLMS_Membership( 'new', 'Membership Title' );

		$this->assertEquals( false, $course->has_sales_page_redirect() );

		$course->set( 'sales_page_content_type', 'none' );
		$this->assertEquals( false, $course->has_sales_page_redirect() );

		$course->set( 'sales_page_content_type', 'content' );
		$this->assertEquals( false, $course->has_sales_page_redirect() );

		$course->set( 'sales_page_content_type', 'url' );
		$this->assertEquals( true, $course->has_sales_page_redirect() );

		$course->set( 'sales_page_content_type', 'page' );
		$this->assertEquals( true, $course->has_sales_page_redirect() );

	}

}
