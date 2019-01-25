<?php
/**
 * Unit Test Case with tests and utilities specific to testing classes
 * which extend the LLMS_Post_Model
 * @since    3.4.0
 * @version  [version]
 */

require_once 'class-llms-unit-test-case.php';

class LLMS_PostModelUnitTestCase extends LLMS_UnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = '';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = '';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	protected function get_properties() {
		return array();
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	protected function get_data() {
		return array();
	}

	/*
		             /$$     /$$ /$$
		            | $$    |__/| $$
		 /$$   /$$ /$$$$$$   /$$| $$  /$$$$$$$
		| $$  | $$|_  $$_/  | $$| $$ /$$_____/
		| $$  | $$  | $$    | $$| $$|  $$$$$$
		| $$  | $$  | $$ /$$| $$| $$ \____  $$
		|  $$$$$$/  |  $$$$/| $$| $$ /$$$$$$$/
		 \______/    \___/  |__/|__/|_______/
	*/

	/**
	 * Will hold an intance of the model being tested by the class
	 * @var  obj
	 */
	protected $obj = null;


	/**
	 * Create a post that can be tested
	 * @param    string|array  $args  string for post title or array of arguments to use when creating the post
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	protected function create( $args = 'test title' ) {

		$this->obj = new $this->class_name( 'new', $args );

	}

	/*
		   /$$                           /$$
		  | $$                          | $$
		 /$$$$$$    /$$$$$$   /$$$$$$$ /$$$$$$   /$$$$$$$
		|_  $$_/   /$$__  $$ /$$_____/|_  $$_/  /$$_____/
		  | $$    | $$$$$$$$|  $$$$$$   | $$   |  $$$$$$
		  | $$ /$$| $$_____/ \____  $$  | $$ /$$\____  $$
		  |  $$$$/|  $$$$$$$ /$$$$$$$/  |  $$$$//$$$$$$$/
		   \___/   \_______/|_______/    \___/ |_______/
	*/

	/**
	 * Test creation of the model
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function test_create_model() {

		$this->create( 'test title' );

		$id = $this->obj->get( 'id' );

		$test = llms_get_post( $id );

		$this->assertEquals( $id, $test->get( 'id' ) );
		$this->assertEquals( $this->post_type, $test->get( 'type' ) );
		$this->assertEquals( 'test title', $test->get( 'title' ) );

	}

	/**
	 * Test getters and setters
	 * @return   void
	 * @since    3.4.0
	 * @version  [version]
	 */
	public function test_getters_setters() {

		$this->create( 'test title' );
		$props = $this->get_properties();
		$data = $this->get_data();

		if ( ! $data ) {
			$this->markTestSkipped( 'No properties to test.' );
		}

		foreach ( $props as $prop => $type ) {

			// set should return true
			$this->assertTrue( $this->obj->set( $prop, $data[ $prop ] ) );

			// make sure gotten value equals set val
			$this->assertEquals( $data[ $prop ], $this->obj->get( $prop ) );

			// check type
			switch ( $type ) {

				case 'absint':
					// should be numeric
					$this->assertTrue( is_numeric( $this->obj->get( $prop ) ) );
					// strings should return 0
					$this->obj->set( $prop, 'string' );
					$this->assertEquals( 0, $this->obj->get( $prop ) );
					// floats should drop the decimal
					$this->obj->set( $prop, 12.3 );
					$this->assertEquals( 12, $this->obj->get( $prop ) );
					// negative should return positive
					$this->obj->set( $prop, -45 );
					$this->assertEquals( 45, $this->obj->get( $prop ) );
					// numeric strind should return int
					$this->obj->set( $prop, '6' );
					$this->assertEquals( '6', $this->obj->get( $prop ) );
				break;

				case 'array':
					// should be an array
					$this->assertTrue( is_array( $this->obj->get( $prop ) ) );
					// strings should return an array with the string as the first item in the array
					$this->obj->set( $prop, 'string' );
					$this->assertEquals( array( 'string'), $this->obj->get( $prop ) );
				break;

				case 'float':
					// should be a float
					$this->assertTrue( is_float( $this->obj->get( $prop ) ) );
					// string should return 0
					$this->obj->set( $prop, 'string' );
					$this->assertEquals( 0, $this->obj->get( $prop ) );
					// decimals shouldn't be lost
					$this->obj->set( $prop, 123.456 );
					$this->assertEquals( 123.456, $this->obj->get( $prop ) );
					// whole numbers should still be whole numbers
					$this->obj->set( $prop, 789 );
					$this->assertEquals( 789, $this->obj->get( $prop ) );
					// check super big numbers
					$this->obj->set( $prop, 1234567.89 );
					$this->assertEquals( 1234567.89, $this->obj->get( $prop ) );
				break;

				case 'text':
					$this->assertTrue( is_string( $this->obj->get( $prop ) ) );
				break;

				case 'yesno':
					// yes returns yes
					$this->obj->set( $prop, 'yes' );
					$this->assertEquals( 'yes', $this->obj->get( $prop ) );
					// no returns no
					$this->obj->set( $prop, 'no' );
					$this->assertEquals( 'no', $this->obj->get( $prop ) );
					// anything else returns no
					$this->obj->set( $prop, 'string' );
					$this->assertEquals( 'no', $this->obj->get( $prop ) );
					$this->obj->set( $prop, '' );
					$this->assertEquals( 'no', $this->obj->get( $prop ) );
					$this->obj->set( $prop, 123456 );
					$this->assertEquals( 'no', $this->obj->get( $prop ) );
				break;

			}

		}
	}

}
