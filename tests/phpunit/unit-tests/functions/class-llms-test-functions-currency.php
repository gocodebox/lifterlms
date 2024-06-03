<?php
/**
 * Tests for LifterLMS Currency functions
 *
 * @group functions
 * @group currency
 *
 * @since 3.24.1
 * @since 5.0.0 Moved country-related function tests to locale functions test file.
 * @since 6.0.0 Removed testing of the removed `llms_format_decimal()` function.
 */
class LLMS_Test_Functions_Currency extends LLMS_UnitTestCase {

	/**
	 * test the get_lifterlms_currency() function
	 *
	 * @since 3.24.1
	 *
	 * @return void
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
	 *
	 * @since 3.24.1
	 * @since 5.0.0 Update language.
	 *
	 * @return void
	 */
	public function test_get_lifterlms_currency_name() {

		// test default
		$this->assertEquals( 'United States Dollar', get_lifterlms_currency_name() );

		// test $currency argument
		$this->assertEquals( 'British Pound', get_lifterlms_currency_name( 'GBP' ) );

		// test that the lifterlms_currency_name filter is applied
		add_filter( 'lifterlms_currency_name', function( $name, $currency ) {
			return sprintf( '%s (%s)', $name, $currency );
		}, 10, 2 );
		$this->assertEquals( 'United States Dollar (USD)', get_lifterlms_currency_name() );
	}

	/**
	 * test the get_lifterlms_currencies() function
	 *
	 * @since 3.24.1
	 * @since 5.0.0 Update test to ensure result matches source data array.
	 *
	 * @return void
	 */
	public function test_get_lifterlms_currencies() {

		$expected = include LLMS_PLUGIN_DIR . 'languages/currencies.php';
		$this->assertEquals( $expected, get_lifterlms_currencies() );

	}

	/**
	 * test the get_lifterlms_currency_symbol() function
	 *
	 * @since 3.24.1
	 * @since 5.0.0 Update character entity used for the pound.
	 *
	 * @return void
	 */
	public function test_get_lifterlms_currency_symbol() {

		// test default
		$this->assertEquals( '&#36;', get_lifterlms_currency_symbol() );

		// test $currency argument
		$this->assertEquals( '&#163;', get_lifterlms_currency_symbol( 'GBP' ) );

		// test that the lifterlms_currency_symbol filter is applied
		add_filter( 'lifterlms_currency_symbol', function( $currency_symbol, $currency ) {
			return sprintf( '%s (%s)', $currency_symbol, $currency );
		}, 10, 2 );
		$this->assertEquals( '&#36; (USD)', get_lifterlms_currency_symbol() );
	}

	/**
	 * test the get_lifterlms_currency_symbol() function
	 *
	 * @since 3.24.1
	 *
	 * @return void
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
	 *
	 * @since 3.24.1
	 *
	 * @return void
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
	 *
	 * @since 3.24.1
	 *
	 * @return void
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
	 *
	 * @since 3.24.1
	 *
	 * @return void
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
	 *
	 * @since 3.24.1
	 *
	 * @return void
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

	public function test_llms_get_currency_symbols() {

		$expected = include LLMS_PLUGIN_DIR . 'languages/currency-symbols.php';
		$res      = llms_get_currency_symbols();
		$this->assertEquals( $expected, $res );

		// Make sure entities decode to what's expected.
		$this->assertEquals( '$', html_entity_decode( $res['USD'] ) );
		$this->assertEquals( '£', html_entity_decode( $res['GBP'] ) );
		$this->assertEquals( '€', html_entity_decode( $res['EUR'] ) );

		// Text symbols.
		$this->assertEquals( 'P', $res['BWP'] );
		$this->assertEquals( 'CHF', $res['CHF'] );

	}

	/**
	 * test the llms_price() function
	 *
	 * @since 3.24.1
	 * @since 5.0.0 Update currency symbol entities.
	 *
	 * @return void
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
		$this->assertEquals( '<span class="lifterlms-price"><div><span class="llms-price-currency-symbol">&#163;</span></div>1,003.00</span>', llms_price( '1002.999', $args ) );

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
		$this->assertEquals( '<span class="lifterlms-price"><span class="llms-price-currency-symbol">&#8364;</span> - 1.003:0</span>', llms_price( '1002.999', $args ) );
	}

	/**
	 * test the llms_price_raw() function
	 *
	 * @since 3.24.1
	 *
	 * @return void
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
	 *
	 * @since 3.24.1
	 *
	 * @return void
	 */
	public function test_llms_trim_zeros() {

		$this->assertEquals( '2', llms_trim_zeros( '2.00' ) );
	}
}
