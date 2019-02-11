<?php
/**
 * Tests for LifterLMS Currency functions
 * @group	functions
 * @group	currency
 * @since   3.24.1
 * @version [version]
 */
class LLMS_Test_Functions_Currency extends LLMS_UnitTestCase {

	/**
	 * test the llms_format_decimal() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_llms_format_decimal() {

		// test the most trivial case
		$this->assertEquals( 3.3333, llms_format_decimal( 3.3333 ) );

		// test the $dp argument
		$this->assertEquals( 3.33, llms_format_decimal( 3.3333 , true ) );

		// test the $trim_zeros argument
		$this->assertSame( '3.33', llms_format_decimal( '3.330' , false, true ) );
		$this->assertSame( '3', llms_format_decimal( '3.0' , false, true ) );

		// test localized decimal formatting
		update_option( 'lifterlms_price_decimal_sep', ',' );
		$this->assertSame( '3.0', llms_format_decimal( '3,0' ) );
	}

	/**
	 * test the get_lifterlms_countries() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_countries() {

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

	/**
	 * test the get_lifterlms_country() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
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
	 * test the get_lifterlms_currency() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_currency() {

		// test default
		$this->assertEquals( 'USD', get_lifterlms_currency() );

		// test lifterlms_country option
		update_option( 'lifterlms_currency', 'GBP' );
		$this->assertEquals( 'GBP', get_lifterlms_currency() );

		// test that the lifterlms_currency filter is applied
		add_filter( 'lifterlms_currency', function() {
			return 'EUR';
		} );
		$this->assertEquals( 'EUR', get_lifterlms_currency() );
	}

	/**
	 * test the get_lifterlms_currency() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_currency_name() {

		// test default
		$this->assertEquals( 'United States dollar', get_lifterlms_currency_name() );

		// test $currency argument
		$this->assertEquals( 'Pound sterling', get_lifterlms_currency_name( 'GBP' ) );

		// test that the lifterlms_currency_name filter is applied
		add_filter( 'lifterlms_currency_name', function( $name, $currency ) {
			return sprintf( '%s (%s)', $name, $currency );
		}, 10, 2 );
		$this->assertEquals( 'United States dollar (USD)', get_lifterlms_currency_name() );
	}

	/**
	 * test the get_lifterlms_currencies() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_currencies() {

		// test unique and lifterlms_currencies filters are applied
		add_filter( 'lifterlms_currencies', function() {
			return array(
				'AED' => 'United Arab Emirates dirham',
				'AFN' => 'Afghan afghani',
				'ALL' => 'Albanian lek',
				'AMD' => 'Armenian dram',
				'ANG' => 'Netherlands Antillean guilder',
				'ANH' => 'Netherlands Antillean guilder',
			);
		} );

		$test = array(
			'AED' => 'United Arab Emirates dirham',
			'AFN' => 'Afghan afghani',
			'ALL' => 'Albanian lek',
			'AMD' => 'Armenian dram',
			'ANG' => 'Netherlands Antillean guilder',
		);

		$this->assertEquals( $test, get_lifterlms_currencies() );
	}

	/**
	 * test the get_lifterlms_currency_symbol() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_currency_symbol() {

		// test default
		$this->assertEquals( '&#36;', get_lifterlms_currency_symbol() );

		// test $currency argument
		$this->assertEquals( '&pound;', get_lifterlms_currency_symbol( 'GBP' ) );

		// test that the lifterlms_currency_symbol filter is applied
		add_filter( 'lifterlms_currency_symbol', function( $currency_symbol, $currency ) {
			return sprintf( '%s (%s)', $currency_symbol, $currency );
		}, 10, 2 );
		$this->assertEquals( '&#36; (USD)', get_lifterlms_currency_symbol() );
	}

	/**
	 * test the get_lifterlms_currency_symbol() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_decimals() {

		// test default
		$this->assertEquals( 2, get_lifterlms_decimals() );

		// test lifterlms_decimals option
		update_option( 'lifterlms_decimals', 3 );
		$this->assertEquals( 3, get_lifterlms_decimals() );

		// test that the lifterlms_decimals filter is applied
		add_filter( 'lifterlms_decimals', function() {
			return 4;
		} );
		$this->assertEquals( 4, get_lifterlms_decimals() );
	}

	/**
	 * test the get_lifterlms_decimal_separator() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_decimal_separator() {

		// test default
		$this->assertEquals( '.', get_lifterlms_decimal_separator() );

		// test lifterlms_decimal_separator option
		update_option( 'lifterlms_decimal_separator', ',' );
		$this->assertEquals( ',', get_lifterlms_decimal_separator() );

		// test that the lifterlms_decimal_separator filter is applied
		add_filter( 'lifterlms_decimal_separator', function() {
			return ':';
		} );
		$this->assertEquals( ':', get_lifterlms_decimal_separator() );
	}

	/**
	 * test the get_lifterlms_trim_zero_decimals() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_trim_zero_decimals() {

		// test default
		$this->assertEquals( 'no', get_lifterlms_trim_zero_decimals() );

		// test lifterlms_trim_zero_decimals option
		update_option( 'lifterlms_trim_zero_decimals', 'yes' );
		$this->assertEquals( 'yes', get_lifterlms_trim_zero_decimals() );

		// test that the lifterlms_trim_zero_decimals filter is applied
		add_filter( 'lifterlms_trim_zero_decimals', function() {
			return 'no';
		} );
		$this->assertEquals( 'no', get_lifterlms_trim_zero_decimals() );
	}

	/**
	 * test the get_lifterlms_price_format() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_price_format() {

		// test default
		$this->assertEquals( '%1$s%2$s', get_lifterlms_price_format() );

		// test right option
		update_option( 'lifterlms_currency_position', 'right' );
		$this->assertEquals( '%2$s%1$s', get_lifterlms_price_format() );

		// test left_space option
		update_option( 'lifterlms_currency_position', 'left_space' );
		$this->assertEquals( '%1$s&nbsp;%2$s', get_lifterlms_price_format() );

		// test right_space option
		update_option( 'lifterlms_currency_position', 'right_space' );
		$this->assertEquals( '%2$s&nbsp;%1$s', get_lifterlms_price_format() );

		// test that the lifterlms_price_format filter is applied
		add_filter( 'lifterlms_price_format', function( $format, $pos ) {
			return sprintf( '%s (%s)', $format, $pos );
		}, 10, 2 );
		$this->assertEquals( '%2$s&nbsp;%1$s (right_space)', get_lifterlms_price_format() );
	}

	/**
	 * test the get_lifterlms_thousand_separator() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_get_lifterlms_thousand_separator() {

		// test default
		$this->assertEquals( ',', get_lifterlms_thousand_separator() );

		// test lifterlms_thousand_separator option
		update_option( 'lifterlms_thousand_separator', '.' );
		$this->assertEquals( '.', get_lifterlms_thousand_separator() );

		// test that the lifterlms_thousand_separator filter is applied
		add_filter( 'lifterlms_thousand_separator', function() {
			return ':';
		} );
		$this->assertEquals( ':', get_lifterlms_thousand_separator() );
	}

	/**
	 * test the llms_get_country_name() function
	 * @return   void
	 * @since    3.24.1
	 * @version  [version]
	 */
	public function test_llms_get_country_name() {

		// test existing country definition
		$this->assertEquals( 'United States (US)', llms_get_country_name( 'US' ) );

		// test non-existing country definition
		$this->assertEquals( 'XX', llms_get_country_name( 'XX' ) );
	}

	/**
	 * test the llms_price() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_llms_price() {

		// test default positive price
		$this->assertEquals( '<span class="lifterlms-price"><span class="llms-price-currency-symbol">&#36;</span>2.99</span>', llms_price( 2.99 ) );

		// test default negative price
		$this->assertEquals( '<span class="lifterlms-price">-<span class="llms-price-currency-symbol">&#36;</span>2.99</span>', llms_price( -2.99 ) );

		// test that raw_lifterlms_price filter is applied
		add_filter( 'raw_lifterlms_price', function( $price ) {
			return $price * 10;
		} );
		$this->assertEquals( '<span class="lifterlms-price"><span class="llms-price-currency-symbol">&#36;</span>29.90</span>', llms_price( 2.99 ) );
		remove_all_filters( 'raw_lifterlms_price' );

		// test that formatted_lifterlms_price filter is applied
		add_filter( 'formatted_lifterlms_price', function( $formatted_price, $price, $decimals, $decimal_separator, $thousand_separator ) {
			$price = number_format( $price, $decimals, $decimal_separator, $thousand_separator );
			return round( $formatted_price );
		}, 10, 5 );
		$this->assertEquals( '<span class="lifterlms-price"><span class="llms-price-currency-symbol">&#36;</span>3</span>', llms_price( 2.99 ) );
		remove_all_filters( 'formatted_lifterlms_price' );

		// test that llms_price filter is applied
		add_filter( 'llms_price', function( $r, $price, $args ) {
			return $price;
		}, 10, 3 );
		$this->assertEquals( '2.99', llms_price( 2.99 ) );
		remove_all_filters( 'llms_price' );

		// test with custom options
		update_option( 'lifterlms_decimal_separator', ',' );
		update_option( 'lifterlms_decimals', 3 );
		update_option( 'lifterlms_currency_position', 'left_space' );
		update_option( 'lifterlms_thousand_separator', '.' );
		update_option( 'lifterlms_trim_zero_decimals', 'yes' );
		$this->assertEquals( '<span class="lifterlms-price"><span class="llms-price-currency-symbol">&#36;</span>&nbsp;1.002</span>', llms_price( 1002.00 ) );

		// test with custom options via $args argument
		$args = array(
			'currency' => 'GBP',
			'decimal_separator' => '.',
			'decimals' => 2,
			'format' => '<div>%s</div>%s',
			'thousand_separator' => ',',
			'trim_zeros' => 'no',
		);
		$this->assertEquals( '<span class="lifterlms-price"><div><span class="llms-price-currency-symbol">&pound;</span></div>1,003.00</span>', llms_price( '1002.999', $args ) );

		// test with custom arguments via llms_price_args filter
		add_filter( 'llms_price_args', function() {
			return array(
				'currency' => 'EUR',
				'decimal_separator' => ':',
				'decimals' => 1,
				'format' => '%s - %s',
				'thousand_separator' => '.',
				'trim_zeros' => 'no',
			);
		} );
		$this->assertEquals( '<span class="lifterlms-price"><span class="llms-price-currency-symbol">&euro;</span> - 1.003:0</span>', llms_price( '1002.999', $args ) );
	}

	/**
	 * test the llms_price_raw() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_llms_price_raw() {

		// test default case
		$this->assertEquals( '$2.99', llms_price_raw( 2.99 ) );

		// test with $args
		$args = array(
			'currency' => 'GBP',
			'decimal_separator' => '.',
			'decimals' => 2,
			'format' => '<div>%s</div>%s',
			'thousand_separator' => ',',
			'trim_zeros' => 'no',
		);
		$this->assertEquals( '£1,003.00', llms_price_raw( 1002.999, $args ) );
	}

	/**
	 * test the llms_trim_zeros() function
	 * @return   void
	 * @since    3.24.1
	 * @version  3.24.1
	 */
	public function test_llms_trim_zeros() {

		$this->assertEquals( '2', llms_trim_zeros( '2.00' ) );
	}
}
