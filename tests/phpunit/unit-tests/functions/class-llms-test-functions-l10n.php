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

	/**
	 * Test llms_load_textdomain() as it would be used by a 3rd party.
	 *
	 * @since 4.9.0
	 *
	 * @see LLMS_Test_Main_Class::test_localize() for coverage with default args against the LifterLMS core plugin.
	 *
	 * @return void
	 */
	public function test_llms_load_textdomain() {

		$dirs = array(
			WP_LANG_DIR . '/lifterlms', // "Safe" directory.
			WP_LANG_DIR . '/plugins', // Default language directory.
			WP_PLUGIN_DIR . '/lifterlms-test/i18n', // Plugin language directory.
		);

		foreach ( $dirs as $dir ) {

			// Make sure the initial strings work.
			$this->assertEquals( 'LifterLMS', __( 'LifterLMS', 'lifterlms-test' ), $dir );
			$this->assertEquals( 'Course', __( 'Course', 'lifterlms-test' ), $dir );

			// Load a language file.
			$file = LLMS_Unit_Test_Files::copy_asset( 'lifterlms-en_US.mo', $dir, 'lifterlms-test-en_US.mo' );
			llms_load_textdomain( 'lifterlms-test', WP_PLUGIN_DIR . '/lifterlms-test', 'i18n' );

			$this->assertEquals( 'BetterLMS', __( 'LifterLMS', 'lifterlms-test' ), $dir );
			$this->assertEquals( 'Module', __( 'Course', 'lifterlms-test' ), $dir );

			// Clean up.
			LLMS_Unit_Test_Files::remove( $file );
			unload_textdomain( 'lifterlms-test' );

		}

	}

}
