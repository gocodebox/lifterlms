<?php
/**
 * Currency and Price related functions for LifterLMS Products
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the currency selected
 *
 * @since 1.0.0
 * @since 3.0.0 Added USD as default when no option is set.
 *
 * @return string Currency code.
 */
function get_lifterlms_currency() {

	/**
	 * Hook Summary
	 *
	 * Hook description.
	 *
	 * @since Unknown
	 *
	 * @param string $currency Currency code.
	 */
	return apply_filters( 'lifterlms_currency', get_option( 'lifterlms_currency', 'USD' ) );

}

/**
 * Get the name of a currency
 *
 * @since  3.0.0
 *
 * @param string $currency A currency code.
 * @return string
 */
function get_lifterlms_currency_name( $currency = '' ) {

	if ( ! $currency ) {
		$currency = get_lifterlms_currency();
	}
	$name = '';

	$currencies = get_lifterlms_currencies();
	if ( isset( $currencies[ $currency ] ) ) {
		$name = $currencies[ $currency ];
	}

	/**
	 * Filters the name of the given currency.
	 *
	 * @since 3.0.0
	 *
	 * @param string $name     Currency name.
	 * @param string $currency Currency code.
	 */
	return apply_filters( 'lifterlms_currency_name', $name, $currency );
}

/**
 * Get array of supported currencies
 *
 * @since Unknown
 * @since 3.0.0 Unknown.
 * @since 5.0.0 Use currency list provided in `languages/currencies.php`.
 *
 * @return array
 */
function get_lifterlms_currencies() {

	$currencies = require LLMS_PLUGIN_DIR . 'languages/currencies.php';

	/**
	 * Filters the list of available currencies
	 *
	 * @since Unknown
	 *
	 * @param array $currencies A list of currency codes to currency names. See "languages/currencies.php" for details.
	 */
	return apply_filters( 'lifterlms_currencies', $currencies );

}

/**
 * Get Currency Symbol text code
 *
 * @since Unknown
 * @since 3.30.3 Removed duplicate key "MAD".
 * @since 5.0.0 Retrieve symbols list from `llms_get_currency_symbols()`.
 *              If a symbol cannot be found for the supplied currency code, return the code instead of an empty string.
 *
 * @param  string $currency Currency Code.
 * @return string
 */
function get_lifterlms_currency_symbol( $currency = '' ) {

	if ( ! $currency ) {
		$currency = get_lifterlms_currency();
	}

	$symbols         = llms_get_currency_symbols();
	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency;

	/**
	 * Filters the symbol for the specified currency
	 *
	 * @since Unknown
	 *
	 * @param string $currency_symbol Currency symbol. If the symbol contains non-Latin characters, the HTML entity code for those characters will be used.
	 * @param string $currency        Currency code.
	 */
	return apply_filters( 'lifterlms_currency_symbol', $currency_symbol, $currency );
}

/**
 * Get the number of decimals places used for prices as defined by the setting.
 *
 * @since 3.0.0
 *
 * @return int
 */
function get_lifterlms_decimals() {
	return absint( apply_filters( 'lifterlms_decimals', get_option( 'lifterlms_decimals', 2 ) ) );
}

/**
 * Retrieve the character used as a decimal separator
 *
 * @since 3.0.0
 *
 * @return string
 */
function get_lifterlms_decimal_separator() {
	return apply_filters( 'lifterlms_decimal_separator', get_option( 'lifterlms_decimal_separator', '.' ) );
}

/**
 * Retrieve the setting for trimming zero value decimals from the end of prices
 *
 * @since  3.0.0
 *
 * @return string Either 'yes' or 'no'.
 */
function get_lifterlms_trim_zero_decimals() {
	return apply_filters( 'lifterlms_trim_zero_decimals', get_option( 'lifterlms_trim_zero_decimals', 'no' ) );
}

/**
 * Get a format string that can be passed to printf or sprintf to format prices
 *
 * The format string is created using user-defined price formatting settings.
 *
 * @since  3.0.0
 *
 * @return string
 */
function get_lifterlms_price_format() {
	$pos    = get_option( 'lifterlms_currency_position', 'left' );
	$format = '%1$s%2$s';
	switch ( $pos ) {
		case 'left':
			$format = '%1$s%2$s';
			break;
		case 'right':
			$format = '%2$s%1$s';
			break;
		case 'left_space':
			$format = '%1$s&nbsp;%2$s';
			break;
		case 'right_space':
			$format = '%2$s&nbsp;%1$s';
			break;
	}
	return apply_filters( 'lifterlms_price_format', $format, $pos );
}

/**
 * Retrieve the character used as the thousands separator
 *
 * @since 3.0.0
 *
 * @return string
 */
function get_lifterlms_thousand_separator() {
	return apply_filters( 'lifterlms_thousand_separator', get_option( 'lifterlms_thousand_separator', '.' ) );
}

/**
 * Retrieve a list of available currency symbols
 *
 * Retrieves the symbols list from `languages/currency-symbols.php`.
 *
 * @since 5.0.0
 *
 * @return array Array of currency codes to their symbols. Any non-Latin characters found in a symbol are returned as an HTML character entity code.
 */
function llms_get_currency_symbols() {

	$symbols = require LLMS_PLUGIN_DIR . 'languages/currency-symbols.php';

	/**
	 * Filters the list of currency symbols
	 *
	 * @since Unknown
	 *
	 * @param array $symbols List of currency codes to their symbol. See "languages/currency-symbols.php" for details.
	 */
	return apply_filters( 'lifterlms_currency_symbols', $symbols );

}

/**
 * Get a formatted price price
 *
 * @since Unknown
 * @since 3.0.0 Unknown.
 *
 * @param int   $price Price to display.
 * @param array $args  Array of arguments.
 * @return string
 */
function llms_price( $price, $args = array() ) {

	extract(
		apply_filters(
			'llms_price_args',
			array_merge(
				array(
					'currency'           => '',
					'decimal_separator'  => get_lifterlms_decimal_separator(),
					'decimals'           => get_lifterlms_decimals(),
					'format'             => get_lifterlms_price_format(),
					'thousand_separator' => get_lifterlms_thousand_separator(),
					'trim_zeros'         => get_lifterlms_trim_zero_decimals(),
				),
				$args
			)
		)
	);

	$negative = $price < 0;
	$price    = apply_filters( 'raw_lifterlms_price', floatval( $negative ? $price * -1 : $price ) );
	$price    = apply_filters( 'formatted_lifterlms_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

	if ( 'yes' === $trim_zeros && $decimals > 0 ) {
		$price = llms_trim_zeros( $price );
	}

	$formatted_price = ( $negative ? '-' : '' ) . sprintf( $format, '<span class="llms-price-currency-symbol">' . get_lifterlms_currency_symbol( $currency ) . '</span>', $price );
	$r               = '<span class="lifterlms-price">' . $formatted_price . '</span>';

	return apply_filters( 'llms_price', $r, $price, $args );
}

/**
 * Get a simple string (no html) based on the output of llms_price
 *
 * @since Unknown
 * @since 3.0.0 Unknown.
 *
 * @param int   $price Price to display.
 * @param array $args  Array of arguments.
 * @return string
 */
function llms_price_raw( $price, $args = array() ) {
	return html_entity_decode( strip_tags( llms_price( $price, $args ) ) );
}

/**
 * Trim trailing zeros off a price
 *
 * @since 3.0.0
 *
 * @param mixed $price Price string.
 * @return string
 */
function llms_trim_zeros( $price ) {
	return preg_replace( '/' . preg_quote( get_lifterlms_decimal_separator(), '/' ) . '0++$/', '', $price );
}
