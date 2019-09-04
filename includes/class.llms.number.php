<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Number Class
 *
 * Manages formatting numbers for I/O and display
 */
class LLMS_Number {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * Format number to money with decimals
	 *
	 * @param  [int] $number
	 * @return [string]
	 */
	public static function format_money( $number ) {

		return get_lifterlms_currency_symbol() . number_format( (int) $number, 2, '.', ',' );

	}

	/**
	 * Format number to money with no decimals
	 *
	 * @param  [int] $number
	 * @return [string]
	 */
	public static function format_money_no_decimal( $number ) {
		return get_lifterlms_currency_symbol() . number_format( $number );
	}

	/**
	 * Converts and rounds a decimal to a whole number
	 *
	 * @param  [decimal] $decimal [percentage]
	 * @return [int]        [whole number representation of decimal value]
	 */
	public static function whole_number( $decimal ) {

			return round( $decimal * 100 );

	}

}

return new LLMS_Number();
