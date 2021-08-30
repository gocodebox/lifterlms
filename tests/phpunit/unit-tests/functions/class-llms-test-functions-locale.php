<?php
/**
 * Tests for LifterLMS Locale functiosn
 *
 * @group functions
 * @group locale
 *
 * @since 5.0.0
 * @version 5.0.0
 */
class LLMS_Test_Functions_Locale extends LLMS_UnitTestCase {

	/**
	 * Test the get_lifterlms_countries() method.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_lifterlms_countries() {

		$countries = get_lifterlms_countries();
		$this->assertTrue( is_array( get_lifterlms_countries() ) );

		// Spot check presence of countries.
		$this->assertEquals( 'United States', $countries['US'] );
		$this->assertEquals( 'United Kingdom', $countries['GB'] );
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
	 * @since 5.0.0 Moved from currency tests file.
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
	 * Test the llms_get_country_locale() function
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_country_address_info() {

		$this->assertEquals( array(
			'city'     => 'City',
			'state'    => 'State',
			'postcode' => 'ZIP code',
		), llms_get_country_address_info( 'US' ) );

		$this->assertEquals( array(), llms_get_country_address_info( 'FAKE' ) );

	}

	/**
	 * test the llms_get_country_name() function
	 *
	 * @since 3.24.1
	 * @since 3.28.2 Unknown.
	 * @since 5.0.0 Moved from currency tests file.
	 *
	 * @return void
	 */
	public function test_llms_get_country_name() {

		// test existing country definition
		$this->assertEquals( 'United States', llms_get_country_name( 'US' ) );

		// test non-existing country definition
		$this->assertEquals( 'XX', llms_get_country_name( 'XX' ) );
	}

	/**
	 * Test llms_get_time_period_l10n()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_llms_get_time_period_l10n() {

		/**
		 * List of tests to run
		 *
		 * Each array contains two items:
		 * 1) An array of arguments to pass to the function
		 * 2) the expected string output.
		 */
		$tests = array(
			array(
				array( 'day' ),
				'day',
			),
			array(
				array( 'dAy' ),
				'day',
			),
			array(
				array( 'day', 1 ),
				'day',
			),
			array(
				array( 'day', 2 ),
				'days',
			),
			array(
				array( 'day', 100 ),
				'days',
			),
			array(
				array( 'week' ),
				'week',
			),
			array(
				array( 'WEEK' ),
				'week',
			),
			array(
				array( 'week', 1 ),
				'week',
			),
			array(
				array( 'week', 2 ),
				'weeks',
			),
			array(
				array( 'week', 25 ),
				'weeks',
			),
			array(
				array( 'month' ),
				'month',
			),
			array(
				array( 'Month' ),
				'month',
			),
			array(
				array( 'month', 1 ),
				'month',
			),
			array(
				array( 'month', 2 ),
				'months',
			),
			array(
				array( 'month', 17 ),
				'months',
			),
			array(
				array( 'year' ),
				'year',
			),
			array(
				array( 'yeAR' ),
				'year',
			),
			array(
				array( 'year', 1 ),
				'year',
			),
			array(
				array( 'year', 2 ),
				'years',
			),
			array(
				array( 'year', 999 ),
				'years',
			),
			array(
				array( 'UNSUPPORTED' ),
				'UNSUPPORTED',
			),
		);

		foreach ( $tests as $test ) {
			list( $args, $expect ) = $test;
			$this->assertEquals( $expect, llms_get_time_period_l10n( ...$args ) );
		}

	}

	/**
	 * test the get_lifterlms_countries() function
	 *
	 * @since 3.24.1
	 * @since 5.0.0 Updated name when adding test for the base function
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
