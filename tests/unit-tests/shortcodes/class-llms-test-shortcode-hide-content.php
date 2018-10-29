<?php
/**
 * Test the [lifterlms_hide_content] Shortcode
 * @group    shortcodes
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Shortcode_Hide_Content extends LLMS_ShortcodeTestCase {

	/**
	 * Class name of the Shortcode Class
	 * @var string
	 */
	public $class_name = 'LLMS_Shortcode_Hide_Content';

	public function test_get_output() {

		// Test against logged out user.
		$this->assertOutputEquals( '', '[lifterlms_hide_content id="1"]Secrets.[/lifterlms_hide_content]' );

		// Logged out with multiples & different relationships.
		$this->assertOutputEquals( '', '[lifterlms_hide_content id="1,2,3,4" relation="any"]Secrets.[/lifterlms_hide_content]' );
		$this->assertOutputEquals( '', '[lifterlms_hide_content id="1,2,3,4" relation="all"]Secrets.[/lifterlms_hide_content]' );

		// Show a message
		$this->assertOutputEquals( 'Nope.', '[lifterlms_hide_content id="1" message="Nope."]Secrets.[/lifterlms_hide_content]' );
		$this->assertOutputEquals( 'Nope.', '[lifterlms_hide_content id="1,2,3,4" message="Nope." relation="any"]Secrets.[/lifterlms_hide_content]' );


		// get a student and try again
		$student = $this->get_mock_student( true );

		// check against both courses and memberships
		foreach ( array( 'course', 'llms_membership' ) as $post_type ) {

			$ids = $this->factory->post->create_many( 3, array(
				'post_type' => $post_type,
			) );

			// enroll only in the first.
			$student->enroll( $ids[0] );

			// Can see secrets b/c enrollment.
			$this->assertOutputEquals( 'Secrets.', sprintf( '[lifterlms_hide_content id="%d"]Secrets.[/lifterlms_hide_content]', $ids[0] ) );

			// Cannot see b/c no enrollment.
			$this->assertOutputEquals( '', sprintf( '[lifterlms_hide_content id="%d"]Secrets.[/lifterlms_hide_content]', $ids[1] ) );
			$this->assertOutputEquals( '', sprintf( '[lifterlms_hide_content id="%d"]Secrets.[/lifterlms_hide_content]', $ids[2] ) );

			// Must belong to all and does not.
			$this->assertOutputEquals( '', sprintf( '[lifterlms_hide_content id="%s" relation="all"]Secrets.[/lifterlms_hide_content]', $ids[0] . ', ' . $ids[1] ) );

			// Must belong to any and only belongs to one.
			$this->assertOutputEquals( 'Secrets.', sprintf( '[lifterlms_hide_content id="%s" relation="any"]Secrets.[/lifterlms_hide_content]', $ids[0] . ', ' . $ids[1] ) );

			// Enroll in another
			$student->enroll( $ids[2] );

			// Check two, belongs to both.
			$this->assertOutputEquals( 'Secrets.', sprintf( '[lifterlms_hide_content id="%s" relation="all"]Secrets.[/lifterlms_hide_content]', $ids[0] . ', ' . $ids[2] ) );

			// Check three.
			$this->assertOutputEquals( '', sprintf( '[lifterlms_hide_content id="%s" relation="all"]Secrets.[/lifterlms_hide_content]', implode( ',', $ids ) ) );

			// Check any of the two (belongs to both).
			$this->assertOutputEquals( 'Secrets.', sprintf( '[lifterlms_hide_content id="%s" relation="all"]Secrets.[/lifterlms_hide_content]', $ids[0] . ', ' . $ids[2] ) );

		}

	}

}
