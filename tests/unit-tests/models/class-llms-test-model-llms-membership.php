<?php
/**
 * Tests for LifterLMS Membership Model.
 *
 * @group LLMS_Membership
 * @group LLMS_Post_Model
 *
 * @since 3.20.0
 * @since [version] Remove redundant test method `test_get_sections()`,
 *                @see tests/unit-tests/models/class-llms-test-model-llms-course.php.
 * @version [version]
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
