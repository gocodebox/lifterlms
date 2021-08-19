<?php
/**
 * Tests for {@see LLMS_Trait_Singleton}.
 *
 * @group Traits
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Singleton_Trait extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Trait_Singleton
	 */
	protected $mock;

	/**
	 * Setup before running each test in this class.
	 *
	 * @since [version]
	 */
	public function setUp() {

		parent::setUp();

		$this->mock = new class {

			use LLMS_Trait_Singleton;

			protected $color;

			public function get_color() {
				return $this->color;
			}

			public function set_color( $color ) {
				$this->color = $color;
			}
		};
	}

	/**
	 * Test the {@see LLMS_Trait_Singleton::instance()} method.
	 *
	 * @since [version]
	 */
	public function test_instance() {

		# Test that the static instance property does not yet have an object.
		$actual = LLMS_Unit_Test_Util::get_private_property_value( $this->mock, 'instance' );
		$this->assertIsNotObject( $actual );

		/** Test {@see LLMS_Trait_Singleton::instance()}. */
		$instance1 = $this->mock->instance();
		$object = LLMS_Unit_Test_Util::get_private_property_value( $this->mock, 'instance' );
		$this->assertEquals( $instance1, $object );

		# Test that 2 instances are the same.
		$instance2 = $this->mock->instance();
		$instance1->set_color( 'red' );
		$instance2->set_color( 'green' );
		$this->assertEquals( $instance1, $instance2 );
		$this->assertEquals( 'green', $instance1->get_color() );
	}
}
