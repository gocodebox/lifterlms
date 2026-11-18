<?php
/**
 * Tests for LLMS_Post_Model when the underlying WP_Post is null.
 *
 * Regression coverage for LifterLMS issue #2577 — the private ___get() method
 * previously dereferenced $this->post when the property was null, emitting
 * PHP warnings on every read of a post-derived property.
 *
 * @group LLMS_Post_Model
 *
 * @since 10.1.0
 * @version 10.1.0
 */
class LLMS_Test_Post_Model_Null_Post extends LLMS_PostModelUnitTestCase {

	protected $class_name = 'LLMS_Course';
	protected $post_type  = 'course';

	/**
	 * Null out the underlying WP_Post on a built course instance.
	 *
	 * @since 10.1.0
	 * @version 10.1.0
	 *
	 * @param LLMS_Course $course Course instance to mutate.
	 * @return void
	 */
	private function null_post_on( $course ) {

		$ref  = new ReflectionClass( LLMS_Course::class );
		$prop = $ref->getParentClass()->getProperty( 'post' );
		$prop->setAccessible( true );
		$prop->setValue( $course, null );
	}

	/**
	 * Post-derived properties must return an empty string, not warn.
	 *
	 * @since 10.1.0
	 * @version 10.1.0
	 *
	 * @return void
	 */
	public function test_get_post_property_returns_empty_string_when_post_is_null() {

		$this->create( 'null post coverage' );
		$this->null_post_on( $this->obj );

		$this->assertSame( '', $this->obj->get( 'title' ) );
		$this->assertSame( '', $this->obj->get( 'content' ) );
		$this->assertSame( '', $this->obj->get( 'excerpt' ) );
		$this->assertSame( '', $this->obj->get( 'menu_order' ) );
		$this->assertSame( '', $this->obj->get( 'type' ) );
	}

	/**
	 * The id lookup is independent of the WP_Post — it must still absint().
	 *
	 * @since 10.1.0
	 * @version 10.1.0
	 *
	 * @return void
	 */
	public function test_get_id_still_returns_absint_when_post_is_null() {

		$this->create( 'id stays live' );
		$expected_id = absint( $this->obj->id );
		$this->null_post_on( $this->obj );

		$this->assertSame( $expected_id, $this->obj->get( 'id' ) );
	}

	/**
	 * Serialization must not warn when the underlying WP_Post is null.
	 *
	 * Covers toArray() (which routes content/excerpt/title through $this->post
	 * outside of ___get()) and the get_post_type_data() result access.
	 *
	 * @since 10.1.0
	 * @version 10.1.0
	 *
	 * @return void
	 */
	public function test_to_array_returns_without_warnings_when_post_is_null() {

		$this->create( 'null post toArray' );
		$this->null_post_on( $this->obj );

		$arr = $this->obj->toArray();

		$this->assertSame( absint( $this->obj->id ), $arr['id'] );
		$this->assertArrayHasKey( 'title', $arr );
		$this->assertArrayHasKey( 'content', $arr );
		$this->assertArrayHasKey( 'excerpt', $arr );
		$this->assertSame( '', $arr['title'] );
		$this->assertSame( '', $arr['content'] );
		$this->assertSame( '', $arr['excerpt'] );
		$this->assertArrayNotHasKey( 'permalink', $arr );
	}
}
