<?php
/**
 * Tests for {@see LLMS_Trait_User_Engagement_Type}.
 *
 * @group Traits
 *
 * @since [version]
 */
class LLMS_Test_Trait_User_Engagement_Type extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Trait_User_Engagement_Type
	 */
	protected $mock;

	protected $mock_class;

	/**
	 * Setup before running each test in this class.
	 *
	 * @since [version]
	 */
	public function set_up() {

		parent::set_up();

		$this->mock = new class() implements LLMS_Interface_User_Engagement_Type {

			use LLMS_Trait_User_Engagement_Type;

			public function __construct() {
				$this->engagement_type    = 'mock_cert';
				$this->post_type_awarded  = 'llms_my_mock_cert';
				$this->post_type_template = 'llms_mock_cert';
			}
		};
	}

	public static function set_up_before_class() {

		parent::set_up_before_class();

		register_post_type( 'llms_mock_cert', array(
			'labels' => array(
				'name'          => 'Mock Certificate Templates',
				'singular_name' => 'Mock Certificate Template'
			)
		) );
		register_post_type( 'llms_my_mock_cert', array(
			'labels' => array(
				'name'          => 'Awarded Mock Certificates',
				'singular_name' => 'Awarded Mock Certificate'
			)
		) );
	}

	/**
	 * Tear down after class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function tear_down_after_class() {

		parent::tear_down_after_class();
		unregister_post_type( 'llms_mock_cert' );
		unregister_post_type( 'llms_my_mock_cert' );
	}

	/**
	 * Test get_awarded_engagement().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_awarded_engagement() {

		// Create a mock class, by creating a mock object, that LLMS_Trait_User_Engagement_Type::get_awarded_engagement()
		// can use to instantiate an object.
		$this->getMockBuilder( LLMS_Abstract_User_Engagement::class )
		     ->setMockClassName( 'LLMS_User_Mock_Cert' )
		     ->setConstructorArgs( array( 'new' ) )
		     ->onlyMethods( array() ) // Do not replace any methods with configurable test doubles.
		     ->getMock();

		// Test a non-existing engagement.
		self::assertFalse(
			LLMS_Unit_Test_Util::call_method( $this->mock, 'get_awarded_engagement', array( - 1 ) )
		);

		// Test an engagement template.
		$template_id = $this->factory->post->create( array( 'post_type' => 'llms_mock_cert' ) );
		self::assertFalse(
			LLMS_Unit_Test_Util::call_method( $this->mock, 'get_awarded_engagement', array( $template_id ) )
		);

		// Test an awarded engagement.
		$awarded_id = $this->factory->post->create( array( 'post_type' => 'llms_my_mock_cert' ) );
		/** @var LLMS_Abstract_User_Engagement $awarded_engagement */
		$awarded_engagement = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_awarded_engagement', array( $awarded_id ) );
		self::assertIsObject( $awarded_engagement );
		self::assertEquals( 'LLMS_User_Mock_Cert', get_class( $awarded_engagement ) );
		self::assertEquals( $awarded_id, $awarded_engagement->get( 'id' ) );
	}

	/**
	 * Test get_engagement_label_name().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_label_name() {

		$template_post_type = get_post_type_object( 'llms_mock_cert' );
		$awarded_post_type  = get_post_type_object( 'llms_my_mock_cert' );
		$tests              = array(
			array( null, LLMS_Interface_User_Engagement_Type::PLURAL, 'Unknown Engagement Type' ),
			array( $template_post_type, LLMS_Interface_User_Engagement_Type::PLURAL, $template_post_type->labels->name ),
			array( $template_post_type, LLMS_Interface_User_Engagement_Type::SINGULAR, $template_post_type->labels->singular_name ),
			array( $awarded_post_type, LLMS_Interface_User_Engagement_Type::PLURAL, $awarded_post_type->labels->name ),
			array( $awarded_post_type, LLMS_Interface_User_Engagement_Type::SINGULAR, $awarded_post_type->labels->singular_name ),
		);

		foreach ( $tests as $test ) {
			list( $post_type_object, $plural_or_singular, $expected_name ) = $test;
			$args = array( $post_type_object, $plural_or_singular );
			$actual_name = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_engagement_label_name', $args );
			self::assertEquals( $expected_name, $actual_name );
		}
	}

	/**
	 * Test get_engagement_type_object().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_engagement_type_object() {

		$tests = array(
			array( LLMS_Interface_User_Engagement_Type::AWARDED, get_post_type_object( 'llms_my_mock_cert' ) ),
			array( LLMS_Interface_User_Engagement_Type::TEMPLATE, get_post_type_object( 'llms_mock_cert' ) ),
			array( - 1, get_post_type_object( 'llms_mock_cert' ) ),
		);

		foreach ( $tests as $test ) {
			list( $type, $expected_post_type_object ) = $test;
			$actual_post_type_object = LLMS_Unit_Test_Util::call_method(
				$this->mock,
				'get_engagement_type_object',
				array( $type )
			);
			self::assertIsObject( $actual_post_type_object );
			self::assertEquals( $expected_post_type_object, $actual_post_type_object );
		}
	}
}
