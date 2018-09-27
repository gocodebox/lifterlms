<?php
/**
 * Tests for the LLMS_PlayNice Class
 * @since    3.19.6
 * @version  3.19.6
 */
class LLMS_Test_PlayNice extends LLMS_UnitTestCase {

	/**
	 * Tests for wp_optimizepress_live_editor()
	 * @return   void
	 * @since    3.19.6
	 * @version  3.19.6
	 */
	public function test_wp_optimizepress_live_editor() {

		$play = new LLMS_PlayNice();
		$this->assertNull( $play->wp_optimizepress_live_editor() );

	}

}
