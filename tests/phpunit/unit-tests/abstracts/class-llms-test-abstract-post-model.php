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
 * @since 4.10.0
 * @since [version] Added various tests on set_bulk() method.
 */
class LLMS_Test_Abstract_Post_Model extends LLMS_UnitTestCase {

	private $post_type = 'mock_post_type';

	/**
	 * @since 4.10.0
	 * @var LLMS_Post_Model
	 */
	protected $stub;

	/**
	 * Setup before class.
	 *
	 * @since 4.10.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		register_post_type( 'mock_post_type' );

	}

	/**
	 * Tear down after class.
	 *
	 * @since 4.10.0
	 * @since 5.3.3 Renamed from `tearDownAfterClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function tear_down_after_class() {

		parent::tear_down_after_class();
		unregister_post_type( 'mock_post_type' );

	}

	/**
	 * Setup the test case
	 *
	 * @since 4.10.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->stub = $this->get_stub();

	}

	/**
	 * Retrieve the abstract class mock stub
	 *
	 * @since 4.10.0
	 *
	 * @return LLMS_Post_Model
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
	 * @since 4.10.0
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

	/**
	 * Test scrub_field().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_scrub_field() {

		$types = array(
			'absint' => array(
				array( 1, 1 ),
				array( 0, 0 ),
				array( -1, 1 ),
				array( 1.5, 1 ),
				array( 2910, 2910 ),
				array( '932', 932 ),
				array( '34920.23', 34920 ),
				array( 'string', 0 ),
				array( '', 0 ),
				array( null, 0 ),
			),
			'array' => array(
				array( '', array() ),
				array( 1, array( 1 ) ),
				array( array( 1, 2, 3 ), array( 1, 2, 3 ) ),
				array( array( 'test' ), array( 'test' ) ),
			),
			'boolean' => array(
				array( true, true ),
				array( false, false ),
				array( 1, true ),
				array( 0, false ),
				array( null, false ),
			),
			'float' => array(
				array( 1.0, 1.0 ),
				array( 1, 1.0 ),
				array( 0.234, 0.234 ),
				array( 0, 0.0 ),
				array( '2.230', 2.23 ),
				array( null, 0.0 ),
			),
			'int' => array(
				array( 1, 1 ),
				array( 0, 0 ),
				array( -1, -1 ),
				array( 1.5, 1 ),
				array( 2910, 2910 ),
				array( '-932', -932 ),
				array( '34920.23', 34920 ),
				array( 'string', 0 ),
				array( '', 0 ),
				array( null, 0 ),
			),
			'yesno' => array(
				array( 'yes', 'yes' ),
				array( 'no', 'no' ),
				array( 0, 'no' ),
				array( 999, 'no' ),
				array( false, 'no' ),
				array( true, 'no' ),
				array( null, 'no' ),
			),
			'text' => array(
				array( 'yes', 'yes' ),
				array( 'a text string.', 'a text string.' ),
				array( 'no <b>tags</b>', 'no tags' ),
				array( '', '' ),
				array( null, '' ),
			),
			'html' => array(
				array( 'yes', 'yes' ),
				array( 'a text string.', 'a text string.' ),
				array( 'Tags <b>are (mostly) okay</b>.', 'Tags <b>are (mostly) okay</b>.' ),
				array( '', '' ),
				array( null, '' ),
			),
		);

		$types['bool'] = $types['boolean'];
		$types['string'] = $types['text'];

		foreach ( $types as $type => $tests ) {

			foreach ( $tests as $test ) {

				list( $input, $expected ) = $test;
				$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $this->stub, 'scrub_field', array( $input, $type ) ) );

			}
		}

	}

	/**
	 * Test setting meta with the same values as the stored ones, default behavior: not allowed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_meta_same_value_unallowed() {

		$meta = $this->_stage_meta_test();

		// Set all the meta except the last one.
		$result = $this->stub->set_bulk(
			array_column(
				array_slice( $meta, 0, count( $meta ) - 1, true ),
				'value',
				'meta'
			),
			true
		);

		$this->assertTrue( $result );

		// Update with the same values, plus a new one (the latest).
		$result = $this->stub->set_bulk(
			array_column(
				$meta,
				'value',
				'meta'
			),
			true
		);

		$this->assertWPError( $result );
		$this->assertWPErrorCodeEquals( 'invalid_meta', $result );

		foreach ( $result->get_error_messages( 'invalid_meta' ) as $i => $message ) {
			$this->assertEquals(
				sprintf( 'Cannot insert/update the meta_%1$s meta', $i + 1 ),
				$message,
				$message
			);
		}

		// Last meta updated.
		$this->assertEquals( end( $meta )['value'], $this->stub->get( end( $meta )['meta'] ) );

		$this->_unstage_meta_test();

	}

	/**
	 * Test setting meta with the same values as the stored ones, allowed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_meta_same_value_allowed() {

		$meta = $this->_stage_meta_test();

		// Set all the meta.
		$result = $this->stub->set_bulk(
			array_column(
				$meta,
				'value',
				'meta'
			),
			true
		);

		$this->assertTrue( $result );

		// Update with the same value.
		$result = $this->stub->set_bulk(
			array_column(
				$meta,
				'value',
				'meta'
			),
			true,
			true
		);

		$this->assertTrue( $result );

		// Meta updated.
		foreach ( $meta as $m ) {
			$this->assertEquals(
				$this->stub->get( $m['meta'] ),
				$m['value'],
				$m['meta']
			);
		}

		// Update meta with different values.
		$values = array_combine(
			array_column(
				$meta,
				'meta'
			),
			array_values(
				$this->get_all_types_fields( true )
			)
		);

		$result = $this->stub->set_bulk(
			$values,
			true,
			true
		);

		$this->assertTrue( $result );

		// Meta updated.
		foreach ( $values as $key => $value ) {
			$this->assertEquals(
				$this->stub->get( $key ),
				$value,
				$key
			);
		}

		$this->_unstage_meta_test();

	}

	/**
	 * Test set_bulk() method passing empty data array.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_bulk_empty_data() {

		// Return WP_Error, don't allow same meta value.
		$result = $this->stub->set_bulk(
			array(),
			true,
			false
		);

		$this->assertWPError( $result );
		$this->assertWPErrorCodeEquals( 'empty_data', $result );

		// Return WP_Error, allow same meta value.
		$result = $this->stub->set_bulk(
			array(),
			true,
			true
		);

		$this->assertWPError( $result );
		$this->assertWPErrorCodeEquals( 'empty_data', $result );

		// Don't return WP_Error, don't allow same meta value.
		$this->assertFalse(
			$this->stub->set_bulk(
				array(),
				false,
				true
			)
		);

		// Don't return WP_Error, allow same meta value.
		$this->assertFalse(
			$this->stub->set_bulk(
				array(),
				false,
				false
			)
		);

	}

	/**
	 * Test set_bulk() method passing invalid data.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_bulk_invalid_data() {

		// Setting only unsettable properties produces invalid data error.
		$unsettable_properties = LLMS_Unit_Test_Util::call_method( $this->stub, 'get_unsettable_properties' );

		// Return WP_Error, don't allow same meta value.
		$result = $this->stub->set_bulk(
			array_flip( $unsettable_properties ),
			true,
			false
		);

		$this->assertWPError( $result );
		$this->assertWPErrorCodeEquals( 'invalid_data', $result );

		// Return WP_Error, allow same meta value.
		$result = $this->stub->set_bulk(
			array_flip( $unsettable_properties ),
			true,
			false
		);

		$this->assertWPError( $result );
		$this->assertWPErrorCodeEquals( 'invalid_data', $result );

		// Don't return WP_Error, don't allow same meta value.
		$this->assertFalse(
			$this->stub->set_bulk(
				array_flip( $unsettable_properties ),
				false,
				false
			)
		);

		// Don't return WP_Error, allow same meta value.
		$this->assertFalse(
			$this->stub->set_bulk(
				array_flip( $unsettable_properties ),
				false,
				true
			)
		);

	}

	/**
	 * Test `set_bulk()` to ensure single quotes and double quotes are correctly slashed.
	 *
	 * @since 5.3.1
	 *
	 * @return void
	 */
	public function test_set_bulk_quotes() {

		$content = 'Content with "Double" Quotes and \'Single\' Quotes';
		$excerpt = 'Excerpt with "Double" Quotes and \'Single\' Quotes';
		$title   = 'Title with "Double" Quotes and \'Single\' Quotes';

		# Test with KSES filters
		$this->stub->set_bulk( array(
			'content' => $content,
			'excerpt' => $excerpt,
			'title'   => $title,
		) );
		$saved_post = get_post( $this->stub->get( 'id' ) );
		$this->assertEquals( $content, $saved_post->post_content );
		$this->assertEquals( $excerpt, $saved_post->post_excerpt );
		$this->assertEquals( $title, $saved_post->post_title );

		# Test without KSES filters
		kses_remove_filters();
		$this->stub->set_bulk( array(
			'content' => $content,
			'excerpt' => $excerpt,
			'title'   => $title,
		) );
		$saved_post = get_post( $this->stub->get( 'id' ) );
		$this->assertEquals( $content, $saved_post->post_content );
		$this->assertEquals( $excerpt, $saved_post->post_excerpt );
		$this->assertEquals( $title, $saved_post->post_title );
	}

	/**
	 * Test toArray() method.
	 *
	 * @since 5.4.1
	 *
	 * @return void
	 */
	public function test_toArray() {

		// Add custom meta data.
		update_post_meta( $this->stub->get( 'id' ), '_custom_meta', 'meta_value' );

		// Generate the array.
		$array = $this->stub->toArray();

		// Make sure all expected properties are returned.
		$this->assertEqualSets( array_merge( array_keys( $this->stub->get_properties() ), array( 'custom', 'id' ) ), array_keys( $array ) );

		// Values in the array should match the values retrieved by the object getters.
		foreach ( $array as $key => $val ) {

			if ( 'custom' === $key ) {
				$expect = array(
					'_custom_meta' => array(
						'meta_value',
					),
				);
			} elseif ( in_array( $key, array( 'content', 'excerpt', 'title' ), true ) ) {
				$key = "post_{$key}";
				$expect = $this->stub->post->$key;
			} else {
				$expect = $this->stub->get( $key );
			}

			$this->assertEquals( $expect, $val, $key );
		}

	}

	/**
	 * Test toArray() method when the author is expanded.
	 *
	 * @since 5.4.1
	 *
	 * @return void
	 */
	public function test_toArray_expanded_author() {

		$data = array(
			'role'       => 'editor',
			'first_name' => 'Jeffrey',
			'last_name'  => 'Lebowski',
			'description' => "Let me explain something to you. Um, I am not \"Mr. Lebowski\". You're Mr. Lebowski. I'm the Dude. So that's what you call me.",
		);
		$user = $this->factory->user->create_and_get( $data );
		$this->stub->set( 'author', $user->ID );

		unset( $data['role'] );
		$data['id'] = $user->ID;
		$data['email'] = $user->user_email;

		// Generate the array.
		$array = $this->stub->toArray();
		$this->assertEquals( $data, $array['author'] );

	}

	/**
	 * Stages tests on meta fields and returns an array of meta to tests.
	 *
	 * @return void
	 */
	private function _stage_meta_test() {

		$types = $this->get_all_types_fields();

		$meta = array();
		$i = 1;

		// Build meta.
		foreach ( $types as $type => $value ) {
			$meta[] = array(
				'meta'  => 'meta_' . $i++,
				'type'  => $type,
				'value' => $value,
			);
		}

		$declare_property_types = function( $props ) use ( $meta ) {
			return array_merge(
				$props,
				array_column( $meta, 'type', 'meta' )
			);
		};

		$model_post_type = LLMS_Unit_Test_Util::get_private_property_value( $this->stub, 'model_post_type' );
		add_filter( "llms_get_{$model_post_type}_properties", $declare_property_types );

		$on_unstage = function() use ( $declare_property_types, $model_post_type ) {
			remove_filter( "llms_get_{$model_post_type}_properties", $declare_property_types );
		};

		if ( ! has_action( 'llms_test_meta_test_unstage', $on_unstage ) ) {
			add_action( 'llms_test_meta_test_unstage', $on_unstage );
		}

		return $meta;

	}

	/**
	 * Unstage meta test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function _unstage_meta_test() {
		do_action( 'llms_test_meta_test_unstage' );
	}

	/**
	 * All types fields with values.
	 *
	 * @since [version]
	 *
	 * @param bool $alt Alternative values.
	 * @return array
	 */
	private function get_all_types_fields( $alt = false ) {

		$types = ! $alt
			?
			array(
				'absint'  => 1,
				'array'   => array( 1 ),
				'boolean' => true,
				'float'   => 1.0,
				'int'     => -1,
				'yesno'   => 'yes',
				'text'    => 'a text string.',
				'html'    => 'Tags <b>are (mostly) okay</b>.',
			)
			:
			array(
				'absint'  => 2,
				'array'   => array( 2 ),
				'boolean' => false,
				'float'   => 2.0,
				'int'     => -2,
				'yesno'   => 'no',
				'text'    => 'a different text string.',
				'html'    => 'Different Tags <b>are (mostly) okay</b>.',
			);

		$types['bool'] = $types['boolean'];
		$types['string'] = $types['text'];

		return $types;

	}
}
