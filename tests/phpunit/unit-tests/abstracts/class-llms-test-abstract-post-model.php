<?php
/**
 * Tests for the LLMS_Post_Model abstract
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group post_model_abstract
 * @group post_models
 *
 * @since [version]
 */
class LLMS_Test_Abstract_Post_Model extends LLMS_UnitTestCase {

	private $post_type = 'mock_post_type';

	/**
	 * Setup before class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setUpBeforeClass() {

		parent::setUpBeforeClass();
		register_post_type( 'mock_post_type' );

	}

	/**
	 * Teradown after class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function tearDownAfterClass() {

		parent::tearDownAfterClass();
		unregister_post_type( 'mock_post_type' );

	}

	/**
	 * Setup the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->stub = $this->get_stub();

	}

	/**
	 * Retrieve the abstract class mock stub
	 *
	 * @since [version]
	 *
	 * @return LLMS_Abstract_Generator_Posts
	 */
	private function get_stub() {

		$post = $this->factory->post->create_and_get( array( 'post_type' => $this->post_type ) );
		$stub = $this->getMockForAbstractClass( 'LLMS_Post_Model', array( $post ) );

		LLMS_Unit_Test_Util::set_private_property( $stub, 'db_post_type', $this->post_type );
		LLMS_Unit_Test_Util::set_private_property( $stub, 'model_post_type', $this->post_type );

		return $stub;

	}

	/**
	 * Test get() to ensure properties that should not be scrubbed are not scrubbed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_skipped_no_scrub_properties() {

		$tests = array(
			'content' => "<p>has html</p>\n",
			'name'    => 'اسم-آخر', // See https://github.com/gocodebox/lifterlms/pull/1408.
		);

		// Filters should
		foreach ( $tests as $key => $val ) {

			$this->stub->set( $key, $val );

			// The scrub filter should not run when getting the value.
			$actions = did_action( "llms_scrub_{$this->post_type}_field_{$key}" );

			// Characters should not be scrubbed.
			$this->assertEquals( 'name' === $key ? utf8_uri_encode( $val ) : $val, $this->stub->get( $key ) );

			$this->assertSame( $actions, did_action( "llms_scrub_{$this->post_type}_field_{$key}" ) );

		}

	}

}
