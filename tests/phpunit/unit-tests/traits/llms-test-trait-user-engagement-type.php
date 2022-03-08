<?php
/**
 * Tests for {@see LLMS_Trait_User_Engagement_Type}.
 *
 * @group Traits
 *
 * @since 6.0.0
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
	 * @since 6.0.0
	 */
	public function set_up() {

		parent::set_up();

		$this->mock = new class() {

			use LLMS_Trait_User_Engagement_Type;

			public function __construct() {
				$this->engagement_type = 'mock_cert';
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
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public static function tear_down_after_class() {

		parent::tear_down_after_class();
		unregister_post_type( 'llms_mock_cert' );
		unregister_post_type( 'llms_my_mock_cert' );
	}

	/**
	 * Test get_user_engagement().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_user_engagement() {

		/** Create a mock class, by creating a mock object, that
		 * {@see LLMS_Trait_User_Engagement_Type::get_user_engagement()} can use to instantiate an object.
		 */
		$this->getMockBuilder( LLMS_Abstract_User_Engagement::class )
		     ->setMockClassName( 'LLMS_User_Mock_Cert' )
		     ->setConstructorArgs( array( 'new' ) )
		     ->onlyMethods( array() ) // Do not replace any methods with configurable test doubles.
		     ->getMock();

		// Test a non-existing awarded engagement.
		self::assertFalse(
			LLMS_Unit_Test_Util::call_method( $this->mock, 'get_user_engagement', array( - 1, true ) )
		);

		// Test a non-existing engagement template.
		self::assertFalse(
			LLMS_Unit_Test_Util::call_method( $this->mock, 'get_user_engagement', array( - 1, false ) )
		);

		// Test an engagement template.
		$template_id         = $this->factory->post->create( array( 'post_type' => 'llms_mock_cert' ) );
		$args                = array( $template_id, false );
		$engagement_template = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_user_engagement', $args );
		self::assertIsObject( $engagement_template );
		self::assertEquals( 'LLMS_User_Mock_Cert', get_class( $engagement_template ) );
		self::assertEquals( $template_id, $engagement_template->get( 'id' ) );

		// Test an awarded engagement.
		$awarded_id = $this->factory->post->create( array( 'post_type' => 'llms_my_mock_cert' ) );
		$args       = array( $awarded_id, true );
		/** @var LLMS_Abstract_User_Engagement $awarded_engagement */
		$awarded_engagement = LLMS_Unit_Test_Util::call_method( $this->mock, 'get_user_engagement', $args );
		self::assertIsObject( $awarded_engagement );
		self::assertEquals( 'LLMS_User_Mock_Cert', get_class( $awarded_engagement ) );
		self::assertEquals( $awarded_id, $awarded_engagement->get( 'id' ) );
	}
}
