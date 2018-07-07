<?php
/**
 * Tests for the LLMS_PlayNice Class
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_PlayNice extends LLMS_UnitTestCase {

	/**
	 * Tests for wp_optimizepress_live_editor()
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_wp_optimizepress_live_editor() {

		$play = new LLMS_PlayNice();
		$this->assertNull( $play->wp_optimizepress_live_editor() );

	}

}
