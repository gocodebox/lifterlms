<?php
/**
 * Test Localization functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_l10n
 *
 * @since 4.9.0
 */
class LLMS_Test_Functions_L10n extends LLMS_UnitTestCase {

	/**
	 * Test llms_get_locale()
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function test_llms_get_locale() {
		$this->assertEquals( 'en_US', llms_get_locale() );
	}
}
