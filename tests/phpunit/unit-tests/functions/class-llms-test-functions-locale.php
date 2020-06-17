<?php
/**
 * Tests for LifterLMS Locale functions
 *
 * @package  LifterLMS/Tests/Functions
 *
 * @group functions
 * @group locale
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Functions_Locale extends LLMS_UnitTestCase {


	/**
	 * Test llms_get_country_locale_info() for valid countries
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function test_llms_get_country_locale_info() {

		foreach ( array( 'AU', 'GB', 'IT', 'MH', 'US', ) as $code ) {

			$res = llms_get_country_locale_info( $code );

			$this->assertTrue( is_array( $res ) );
			$this->assertArrayHasKey( 'currency', $res );
			$this->assertArrayHasKey( 'platforms', $res );

		}

	}

	/**
	 * Test llms_get_country_locale_info() for invalid countries
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function test_llms_get_country_locale_info_not_found() {
		$this->assertFalse( llms_get_country_locale_info( 'FAKE' ) );
	}

	function test_llms_get_locale_info() {

		$res = llms_get_locale_info();
		$this->assertTrue( is_array( $res ) );

		// Spot check random countries.
		foreach( array( 'AS', 'FI', 'CN', 'IM', 'BR' ) as $code ) {
			$this->assertArrayHasKey( $code, $res );
		}

	}

	/**
	 * Test the get_lifterlms_countries() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_lifterlms_countries() {

		$countries = get_lifterlms_countries();
		$this->assertTrue( is_array( get_lifterlms_countries() ) );

		// Spot check presence of countries.
		$this->assertEquals( 'United States (US)', $countries['US'] );
		$this->assertEquals( 'United Kingdom (UK)', $countries['GB'] );
		$this->assertEquals( 'Australia', $countries['AU'] );
		$this->assertEquals( 'China', $countries['CN'] );
		$this->assertEquals( 'Afghanistan', $countries['AF'] );
		$this->assertEquals( 'Haiti', $countries['HT'] );
		$this->assertEquals( 'Nigeria', $countries['NG'] );
		$this->assertEquals( 'Slovakia', $countries['SK'] );
		$this->assertEquals( 'Uzbekistan', $countries['UZ'] );
		$this->assertEquals( 'Zimbabwe', $countries['ZW'] );

	}

	/**
	 * test the get_lifterlms_country() function
	 *
	 * @since 3.24.1
	 * @since [version] Moved from currency tests file.
	 *
	 * @return void
	 */
	public function test_get_lifterlms_country() {

		// test default
		$this->assertEquals( 'US', get_lifterlms_country() );

		// test lifterlms_country option
		update_option( 'lifterlms_country', 'GB' );
		$this->assertEquals( 'GB', get_lifterlms_country() );

		// test that the lifterlms_country filter is applied
		add_filter( 'lifterlms_country', function() {
			return 'FR';
		} );
		$this->assertEquals( 'FR', get_lifterlms_country() );
	}

	/**
	 * test the llms_get_country_name() function
	 *
	 * @since 3.24.1
	 * @since 3.28.2 Unknown.
	 * @since [version] Moved from currency tests file.
	 *
	 * @return void
	 */
	public function test_llms_get_country_name() {

		// test existing country definition
		$this->assertEquals( 'United States (US)', llms_get_country_name( 'US' ) );

		// test non-existing country definition
		$this->assertEquals( 'XX', llms_get_country_name( 'XX' ) );
	}

	/**
	 * test the get_lifterlms_countries() function
	 *
	 * @since 3.24.1
	 * @since [version] Updated name when adding test for the base function
	 *
	 * @return void
	 */
	public function test_get_lifterlms_countries_filter_and_unique() {

		// test unique and lifterlms_countries filters are applied
		add_filter( 'lifterlms_countries', function() {
			return array(
				'AF' => 'Afghanistan',
				'AL' => 'Albania',
				'DZ' => 'Algeria',
				'AS' => 'American Samoa',
				'AD' => 'Andorra',
				'AN' => 'Andorra',
			);
		} );

		$test = array(
			'AF' => 'Afghanistan',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
		);

		$this->assertEquals( $test, get_lifterlms_countries() );
	}

}
