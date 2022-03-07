<?php
/**
 * Tests for {@see LLMS_Trait_Singleton}.
 *
 * @group Traits
 *
 * @since 5.3.0
 * @since 6.0.0 Removed `LLMS_Test_Singleton_Trait::test_deprecated_instance()`.
 */
class LLMS_Test_Singleton_Trait extends LLMS_UnitTestCase {

	/**
	 * Dynamic class name of the mock class.
	 *
	 * Even though this property contains a string, it is documented as a class so that it can be used like this:
	 * `$this->mock_class::instance()`
	 *
	 * @since 5.3.0
	 *
	 * @var LLMS_Trait_Singleton|object
	 */
	protected $mock_class;

	/**
	 * Setup before running each test in this class.
	 *
	 * @since 5.3.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @noinspection PhpHierarchyChecksInspection
	 */
	public function set_up() {

		parent::set_up();

		# Instantiate an anonymous class that uses the trait to be tested.
		$mock = new class {

			use LLMS_Trait_Singleton;

			protected $color;

			protected static $_instance = null;

			public static function deprecated_instance() {
				if ( is_null( self::$_instance ) ) {
					self::$_instance = new self();
				}

				return self::$_instance;
			}

			public static function init() {
				self::$_instance = null;
				self::$instance  = null;
			}

			public function get_color() {
				return $this->color;
			}

			public function set_color( $color ) {
				$this->color = $color;
			}
		};

		$this->mock_class = get_class( $mock );
	}

	/**
	 * Test the {@see LLMS_Trait_Singleton::instance()} method.
	 *
	 * @since 5.3.0
	 */
	public function test_instance() {

		# Test that the static instance property does not yet have an object.
		$this->mock_class::init();
		$instance_property = LLMS_Unit_Test_Util::get_private_property_value( $this->mock_class, 'instance' );
		$this->assertIsNotObject( $instance_property );

		/**
		 * Test that {@see LLMS_Trait_Singleton::instance()} instantiates a new object,
		 * sets it in the static `$instance` property, and returns the new object.
		 */
		$object1           = $this->mock_class::instance();
		$instance_property = LLMS_Unit_Test_Util::get_private_property_value( $this->mock_class, 'instance' );
		$this->assertEquals( $object1, $instance_property );

		# Test that 2 instances are the same.
		$object1->set_color( 'red' );
		$object2 = $this->mock_class::instance();
		$object2->set_color( 'green' );
		$this->assertEquals( $object1, $object2 );
		$this->assertEquals( 'green', $object1->get_color() );
	}
}
