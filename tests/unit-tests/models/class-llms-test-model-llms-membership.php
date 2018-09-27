<?php
/**
 * Tests for LifterLMS Membership Model
 * @group    LLMS_Membership
 * @group    LLMS_Post_Model
 * @since    3.20.0
 * @version  3.20.0
 */
class LLMS_Test_LLMS_Membership extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Membership';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'llms_membership';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.20.0
	 * @version  3.20.0
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
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.20.0
	 * @version  3.20.0
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
	 * Test get_sales_page_url method
	 * @return   void
	 * @since    3.20.0
	 * @version  3.20.0
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
	 * Test the get sections function
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function test_get_sections() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 4, 0, 0, 0 )[0] );

		// get just ids
		$sections = $course->get_sections( 'ids' );
		$this->assertEquals( 4, count( $sections ) );
		array_map( function( $id ) {
			$this->assertTrue( is_numeric( $id ) );
		}, $sections );

		// wp post objects
		$sections = $course->get_sections( 'posts' );
		$this->assertEquals( 4, count( $sections ) );
		array_map( function( $post ) {
			$this->assertTrue( is_a( $post, 'WP_Post' ) );
		}, $sections );

		// section objects
		$sections = $course->get_sections( 'sections' );
		$this->assertEquals( 4, count( $sections ) );
		array_map( function( $section ) {
			$this->assertTrue( is_a( $section, 'LLMS_Section' ) );
		}, $sections );

	}

	/**
	 * Test the get students function
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
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
	 * Test the has_sales_page_redirect method
	 * @return   void
	 * @since    3.20.0
	 * @version  3.20.0
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
