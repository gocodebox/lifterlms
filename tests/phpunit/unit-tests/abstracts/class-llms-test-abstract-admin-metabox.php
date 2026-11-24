<?php
/**
 * Tests for the LLMS_Admin_Metabox class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group metaboxes
 * @group metabox_abstract
 *
 * @since 3.37.12
 */
class LLMS_Test_Admin_Metabox extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Retrieve an mocked abstract.
	 *
	 * @since 3.37.12
	 *
	 * @return LLMS_Admin_Metabox
	 */
	private function get_stub() {

		$stub = $this->getMockForAbstractClass( 'LLMS_Admin_Metabox' );

		$stub->title = 'Mock Metabox';
		$stub->id    = 'mocker';

		return $stub;

	}

	/**
	 * Mock the get_fields() method for an LLMS_Admin_Metabox stub.
	 *
	 * @since 3.37.12
	 *
	 * @param LLMS_Admin_Metabox $stub Metabox stub.
	 * @return array Array of metabox field data.
	 */
	private function add_fields_to_stub( $stub ) {

		$fields = array(
			array(
				'title'  => 'Tab Title',
				'fields' => array(
					array(
						'label' => 'Field Title.',
						'desc'  => 'Field Description',
						'id'    => $stub->prefix . 'mock_field',
						'type'  => 'text',
					),
					array(
						'label' => 'Field Title.',
						'desc'  => 'Field Description',
						'id'    => $stub->prefix . 'mock_field_2',
						'type'  => 'text',
					),
					array(
						'label'    => 'Allow quotes Field Title.',
						'desc'     => 'Field Description',
						'id'       => $stub->prefix . 'mock_field_with_quotes',
						'type'     => 'text',
						'sanitize' => 'shortcode',
					),
					array(
						'label'    => 'Allow quotes Field Title.',
						'desc'     => 'Field Description',
						'id'       => $stub->prefix . 'mock_field_with_quotes_2',
						'type'     => 'text',
						'sanitize' => 'no_encode_quotes',
					),
					array(
						'label' => 'Multi Select Title.',
						'desc'  => 'Field Description',
						'id'    => $stub->prefix . 'mock_field_multi_select',
						'type'  => 'select',
						'multi' => true,
						'value' => array(
							'key_1' => 'Value 1',
							'key_2' => 'Value 2',
							'key_3' => 'Value 3',
						),
					),
				),
			),
		);

		$stub->method( 'get_fields' )->will( $this->returnValue( $fields ) );

		return $fields;

	}

	/**
	 * Test add_error(), get_errors(), has_errors(), and save_errors().
	 *
	 * @since 3.37.12
	 * @since 6.0.0 Add WP_Error test.
	 *
	 * @return void.
	 */
	public function test_errors_get_set_save() {

		$stub   = $this->get_stub();
		$errors = array(
			1 => 'Error message.',
			2 => 'Second message.',
			3 => new WP_Error( 'brown', 'Third Message' ),
		);

		// No messages.
		$this->assertEquals( array(), $stub->get_errors() );
		$this->assertEquals( false, $stub->has_errors() );

		// Has a specific number of messages.
		foreach ( $errors as $error_number => $error ) {
			$stub->add_error( $error );
			$this->assertEquals( true, $stub->has_errors() );
			$stub->save_errors();
			$this->assertEquals( array_slice( $errors, 0, $error_number ), $stub->get_errors() );
		}
	}

	/**
	 * Test get_screens() method.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$stub = $this->get_stub();

		// As string.
		$stub->screens = 'post';
		$this->assertEquals( array( 'post' ), LLMS_Unit_Test_Util::call_method( $stub, 'get_screens' ) );

		// Array.
		$stub->screens = array( 'post' );
		$this->assertEquals( array( 'post' ), LLMS_Unit_Test_Util::call_method( $stub, 'get_screens' ) );

		// Array with multiple post types.
		$stub->screens[] = 'page';
		$this->assertEquals( array( 'post', 'page' ), LLMS_Unit_Test_Util::call_method( $stub, 'get_screens' ) );

	}

	/**
	 * Test output_errors().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_output_errors() {

		$stub   = $this->get_stub();
		$errors = array(
			'string error'   => 'string error',
			'WP_Error error' => new WP_Error( 'blue', 'WP_Error error' ),
		);

		foreach ( $errors as $contains => $error ) {
			$stub->add_error( $error );
			$stub->save_errors();
			$this->assertOutputContains( $contains, array( $stub, 'output_errors' ) );
		}
	}

	/**
	 * Test save(): no nonce supplied.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_no_nonce() {

		$stub = $this->get_stub();
		$post = $this->factory->post->create();

		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

	}

	/**
	 * Test save(): invalid nonce supplied.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_invalid_nonce() {

		$stub = $this->get_stub();
		$post = $this->factory->post->create();

		$this->mockPostRequest( $this->add_nonce_to_array( array(), false ) );

		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

	}

	/**
	 * Test save(): missing required capabilites.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_no_cap() {

		$stub = $this->get_stub();
		$post = $this->factory->post->create();

		$this->mockPostRequest( $this->add_nonce_to_array() );

		// Logged out.
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

		// Invalid cap.
		wp_set_current_user( $this->factory->student->create() );
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

	}

	/**
	 * Test save(): during a quick edit (inline save).
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_inline_save() {

		$stub = $this->get_stub();
		$post = $this->factory->post->create();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( $this->add_nonce_to_array( array(
			'action' => 'inline-save',
		) ) );

		$this->assertEquals( 0, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

	}

	/**
	 * Test save(): for a metabox with no fields.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_no_fields() {

		$stub = $this->get_stub();
		$post = $this->factory->post->create();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( $this->add_nonce_to_array( array() ) );

		$this->assertEquals( 0, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

	}

	/**
	 * Test save(): when it all works.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_success() {

		$stub = $this->get_stub();
		$this->add_fields_to_stub( $stub );
		$post = $this->factory->post->create();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Save.
		$this->mockPostRequest( $this->add_nonce_to_array( array(
			$stub->prefix . 'mock_field'   => 'mock_val_1',
			$stub->prefix . 'mock_field_2' => 'mock_val_2',
		) ) );

		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

		$this->assertEquals( 'mock_val_1', get_post_meta( $post, $stub->prefix . 'mock_field', true ) );
		$this->assertEquals( 'mock_val_2', get_post_meta( $post, $stub->prefix . 'mock_field_2', true ) );

		// Unset values that aren't posted.
		$this->mockPostRequest( $this->add_nonce_to_array( array(
			$stub->prefix . 'mock_field'   => 'mock_val_1',
		) ) );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

		$this->assertEquals( 'mock_val_1', get_post_meta( $post, $stub->prefix . 'mock_field', true ) );
		$this->assertEquals( '', get_post_meta( $post, $stub->prefix . 'mock_field_2', true ) );

		// Unset a value, update another.
		$this->mockPostRequest( $this->add_nonce_to_array( array(
			$stub->prefix . 'mock_field'   => '',
			$stub->prefix . 'mock_field_2'   => 'new_Val',
		) ) );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $stub, 'save', array( $post ) ) );

		$this->assertEquals( '', get_post_meta( $post, $stub->prefix . 'mock_field', true ) );
		$this->assertEquals( 'new_Val', get_post_meta( $post, $stub->prefix . 'mock_field_2', true ) );

	}

	/**
	 * Test save_field() for a standard field (text)
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_field_standard() {

		$stub  = $this->get_stub();
		$field = $this->add_fields_to_stub( $stub )[0]['fields'][0];
		$post  = $this->factory->post->create();

		$this->mockPostRequest( array(
			$field['id'] => 'Saved "Field" Value.',
		) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $post, $field ) ) );
		$this->assertEquals( 'Saved &#34;Field&#34; Value.', get_post_meta( $post, $field['id'], true ) );

		// Unset the value.
		$this->mockPostRequest( array() );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $post, $field ) ) );
		$this->assertEquals( '', get_post_meta( $post, $field['id'], true ) );


	}

	/**
	 * Test save_field() for "shortcode" sanitization.
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_field_allow_quotes() {

		$stub   = $this->get_stub();
		$fields = $this->add_fields_to_stub( $stub );
		$post   = $this->factory->post->create();

		foreach ( array( 2, 3 ) as $index ) {

			$field = $fields[0]['fields'][ $index ];

			$this->mockPostRequest( array(
				$field['id'] => 'Saved "Field" Value.',
			) );

			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $post, $field ) ) );
			$this->assertEquals( 'Saved "Field" Value.', get_post_meta( $post, $field['id'], true ) );

		}

	}

	/**
	 * Test save_field() for a multi-select
	 *
	 * @since 3.37.12
	 *
	 * @return void
	 */
	public function test_save_field_multi_select() {

		$stub  = $this->get_stub();
		$field = $this->add_fields_to_stub( $stub )[0]['fields'][4];
		$post  = $this->factory->post->create();

		// Array not submitted.
		$this->mockPostRequest( array(
			$field['id'] => 'key_1',
		) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $post, $field ) ) );
		$this->assertEquals( '', get_post_meta( $post, $field['id'], true ) );

		// Single value.
		$this->mockPostRequest( array(
			$field['id'] => array( 'key_1' ),
		) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $post, $field ) ) );
		$this->assertEquals( array( 'key_1' ), get_post_meta( $post, $field['id'], true ) );

		// Multi values.
		$this->mockPostRequest( array(
			$field['id'] => array( 'key_1', 'key_2' ),
		) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $post, $field ) ) );
		$this->assertEquals( array( 'key_1', 'key_2' ), get_post_meta( $post, $field['id'], true ) );


		// Unset.
		$this->mockPostRequest( array(
			$field['id'] => array(),
		) );

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $post, $field ) ) );
		$this->assertEquals( array(), get_post_meta( $post, $field['id'], true ) );

	}

	/**
	 * Test saving the instructors field keeps an author changed in the same save and still lets the instructors set the author.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_field_instructors_keeps_changed_author() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		list( $old_author, $new_author, $other ) = $this->factory->user->create_many( 3, array( 'role' => 'instructor' ) );

		$course = llms_get_post( $this->factory->course->create( array( 'sections' => 0, 'post_author' => $old_author ) ) );
		$course->instructors()->set_instructors( array( array( 'id' => $old_author ), array( 'id' => $other ) ) );

		$stub  = $this->get_stub();
		$field = array(
			'id'      => '_llms_instructors_data',
			'handler' => 'instructors_mb_store',
		);

		// The author was changed to a user who isn't an instructor before the meta box, still listing the old author, saves.
		wp_update_post( array( 'ID' => $course->get( 'id' ), 'post_author' => $new_author ) );
		$this->mockPostRequest( array(
			'_llms_instructors_author' => $old_author,
			'_llms_id_1'               => $old_author,
			'_llms_label_1'            => 'Author',
			'_llms_visibility_1'       => 'visible',
			'_llms_id_2'               => $other,
			'_llms_label_2'            => 'Author',
			'_llms_visibility_2'       => 'visible',
		) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $course->get( 'id' ), $field ) ) );

		$this->assertEquals( $new_author, get_post_field( 'post_author', $course->get( 'id' ) ) );
		$this->assertEquals( array( $new_author, $other ), wp_list_pluck( $course->instructors()->get_instructors(), 'id' ) );

		// With the author unchanged, the first listed instructor becomes the author.
		$this->mockPostRequest( array(
			'_llms_instructors_author' => $new_author,
			'_llms_id_1'               => $other,
			'_llms_label_1'            => 'Author',
			'_llms_visibility_1'       => 'visible',
			'_llms_id_2'               => $new_author,
			'_llms_label_2'            => 'Author',
			'_llms_visibility_2'       => 'visible',
		) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $course->get( 'id' ), $field ) ) );

		$this->assertEquals( $other, get_post_field( 'post_author', $course->get( 'id' ) ) );
		$this->assertEquals( array( $other, $new_author ), wp_list_pluck( $course->instructors()->get_instructors(), 'id' ) );

		// Reordering the Instructors tab saves over AJAX first, so the author already is the new first instructor
		// while the form still lists the author it loaded. Every instructor is kept, in the submitted order.
		$course->instructors()->set_instructors( array( array( 'id' => $other ), array( 'id' => $old_author ), array( 'id' => $new_author ) ) );
		$this->mockPostRequest( array(
			'_llms_instructors_author' => $old_author,
			'_llms_id_1'               => $other,
			'_llms_label_1'            => 'Author',
			'_llms_visibility_1'       => 'visible',
			'_llms_id_2'               => $old_author,
			'_llms_label_2'            => 'Author',
			'_llms_visibility_2'       => 'visible',
			'_llms_id_3'               => $new_author,
			'_llms_label_3'            => 'Author',
			'_llms_visibility_3'       => 'visible',
		) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $stub, 'save_field', array( $course->get( 'id' ), $field ) ) );

		$this->assertEquals( $other, get_post_field( 'post_author', $course->get( 'id' ) ) );
		$this->assertEquals( array( $other, $old_author, $new_author ), wp_list_pluck( $course->instructors()->get_instructors(), 'id' ) );

	}

}
