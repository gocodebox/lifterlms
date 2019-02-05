<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS hash ID encrypt/decrypt
 *
 * Based on PseudoCrypt by KevBurns (http://blog.kevburnsjr.com/php-unique-hash)
 * Reference/source: http://stackoverflow.com/a/1464155/933782
 * Modified from original source to remove reliance on bcmath functions
 *
 * @since    3.16.7
 * @version  3.24.0
 */
class LLMS_Hasher {

	/* Key: Next prime greater than 62 ^ n / 1.618033988749894848 */
	/* Value: modular multiplicative inverse */
	private static $golden_primes = array(
		'1'                  => '1',
		'41'                 => '59',
		'2377'               => '1677',
		'147299'             => '187507',
		'9132313'            => '5952585',
		'566201239'          => '643566407',
		'35104476161'        => '22071637057',
		'2176477521929'      => '294289236153',
		'134941606358731'    => '88879354792675',
		'8366379594239857'   => '7275288500431249',
		'518715534842869223' => '280042546585394647',
	);

	/* Ascii :                    0  9,         A  Z,         a  z     */
	/* $chars = array_merge(range(48,57), range(65,90), range(97,122)) */
	private static $chars62 = array(
		0 => 48,
		1 => 49,
		2 => 50,
		3 => 51,
		4 => 52,
		5 => 53,
		6 => 54,
		7 => 55,
		8 => 56,
		9 => 57,
		10 => 65,
		11 => 66,
		12 => 67,
		13 => 68,
		14 => 69,
		15 => 70,
		16 => 71,
		17 => 72,
		18 => 73,
		19 => 74,
		20 => 75,
		21 => 76,
		22 => 77,
		23 => 78,
		24 => 79,
		25 => 80,
		26 => 81,
		27 => 82,
		28 => 83,
		29 => 84,
		30 => 85,
		31 => 86,
		32 => 87,
		33 => 88,
		34 => 89,
		35 => 90,
		36 => 97,
		37 => 98,
		38 => 99,
		39 => 100,
		40 => 101,
		41 => 102,
		42 => 103,
		43 => 104,
		44 => 105,
		45 => 106,
		46 => 107,
		47 => 108,
		48 => 109,
		49 => 110,
		50 => 111,
		51 => 112,
		52 => 113,
		53 => 114,
		54 => 115,
		55 => 116,
		56 => 117,
		57 => 118,
		58 => 119,
		59 => 120,
		60 => 121,
		61 => 122,
	);

	/**
	 * Modulo function
	 * @todo  figure this out better...
	 *        64 bit systems can use % without issue
	 *        32 bit systems have problems with % and needs to use fmod
	 * 				  however after 100001 unhash no longer works correctly when using fmod
	 *
	 * @param    int     $a  first int
	 * @param    int     $b  second int
	 * @return   int
	 * @since    3.16.10
	 * @version  3.16.10
	 */
	private static function mod( $a, $b ) {

		// 64 bit systems
		if ( 8 === PHP_INT_SIZE ) {
			return $a % $b;
		}
		// 32 bit systems?
		return fmod( $a, $b );

	}

	/**
	 * Base 62 encode a number
	 * @param    int     $int  number to encode
	 * @return   string
	 * @since    3.16.7
	 * @version  3.16.10
	 */
	public static function base62( $int ) {
		$key = '';
		while ( $int > 0 ) {
			$mod = self::mod( $int, 62 );
			$key .= chr( self::$chars62[ $mod ] );
			$int = floor( $int / 62 );
		}
		return strrev( $key );
	}

	/**
	 * Hash a number
	 * @param    int     $num   number to hash
	 * @return   string
	 * @since    3.16.7
	 * @version  3.24.0
	 */
	public static function hash( $num ) {

		$numlen = strlen( $num );
		if ( $numlen <= 3 ) {
			$len = 3;
		} elseif ( 4 === $numlen || 5 === $numlen ) {
			$len = 4;
		} else {
			$len = 5;
		}

		$ceil = pow( 62, $len );
		$primes = array_keys( self::$golden_primes );
		$prime = $primes[ $len ];
		$dec = self::mod( ( $num * $prime ), $ceil );
		$hash = self::base62( $dec );
		return str_pad( $hash, $len, '0', STR_PAD_LEFT );

	}

	/**
	 * Decode a base62 encoded string to get the associated number
	 * @param    string     $key   encoded character
	 * @return   int
	 * @since    3.16.7
	 * @version  3.16.7
	 */
	public static function unbase62( $key ) {
		$int = 0;
		foreach ( str_split( strrev( $key ) ) as $i => $char ) {
			$dec = array_search( ord( $char ), self::$chars62 );
			$int = ( ( $dec * pow( 62, $i ) ) + $int );
		}
		return $int;
	}

	/**
	 * Decode a hashed string to get the original number
	 * @param    [type]     $hash  encoded hash string
	 * @return   int
	 * @since    3.16.7
	 * @version  3.16.10
	 */
	public static function unhash( $hash ) {

		$len = strlen( $hash );
		$ceil = pow( 62, $len );
		$mmiprimes = array_values( self::$golden_primes );
		$mmi = $mmiprimes[ $len ];
		$num = self::unbase62( $hash );
		$dec = self::mod( ( $num * $mmi ), $ceil );
		return $dec;

	}

}
