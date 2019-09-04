<?php
/**
 * Currency and Price related functions for LifterLMS Products
 *
 * @since 1.0.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Format Number as decimal
 *
 * @param    int     $number      price value
 * @param    boolean $dp          decimal points
 * @param    boolean $trim_zeros  trim zeros
 * @return   string
 * @since    ??
 * @version  3.24.0
 * @todo     maybe deprecate
 */
function llms_format_decimal( $number, $dp = false, $trim_zeros = false ) {
	  // Remove locale from string
	if ( ! is_float( $number ) ) {
		  $locale   = localeconv();
		  $decimals = array( get_option( 'lifterlms_price_decimal_sep' ), $locale['decimal_point'], $locale['mon_decimal_point'] );
		  $number   = llms_clean( str_replace( $decimals, '.', $number ) );
	}

	  // DP is false - don't use number format, just return a string in our format
	if ( false !== $dp ) {
		  $dp     = 2;     // = intval( $dp == "" ? get_option( 'lifterlms_price_num_decimals' ) : $dp );
		  $number = number_format( floatval( $number ), $dp, '.', ',' );
	}

	if ( $trim_zeros && strstr( $number, '.' ) ) {
		  $number = rtrim( rtrim( $number, '0' ), '.' );
	}

	  return $number;
}

/**
 * Get Countries array for Select list
 *
 * @return array
 * @since 1.0.0
 * @version 3.28.2
 */
function get_lifterlms_countries() {
	return array_unique(
		apply_filters(
			'lifterlms_countries',
			array(
				'AF' => __( 'Afghanistan', 'lifterlms' ),
				'AX' => __( '&#197;land Islands', 'lifterlms' ),
				'AL' => __( 'Albania', 'lifterlms' ),
				'DZ' => __( 'Algeria', 'lifterlms' ),
				'AS' => __( 'American Samoa', 'lifterlms' ),
				'AD' => __( 'Andorra', 'lifterlms' ),
				'AO' => __( 'Angola', 'lifterlms' ),
				'AI' => __( 'Anguilla', 'lifterlms' ),
				'AQ' => __( 'Antarctica', 'lifterlms' ),
				'AG' => __( 'Antigua and Barbuda', 'lifterlms' ),
				'AR' => __( 'Argentina', 'lifterlms' ),
				'AM' => __( 'Armenia', 'lifterlms' ),
				'AW' => __( 'Aruba', 'lifterlms' ),
				'AU' => __( 'Australia', 'lifterlms' ),
				'AT' => __( 'Austria', 'lifterlms' ),
				'AZ' => __( 'Azerbaijan', 'lifterlms' ),
				'BS' => __( 'Bahamas', 'lifterlms' ),
				'BH' => __( 'Bahrain', 'lifterlms' ),
				'BD' => __( 'Bangladesh', 'lifterlms' ),
				'BB' => __( 'Barbados', 'lifterlms' ),
				'BY' => __( 'Belarus', 'lifterlms' ),
				'BE' => __( 'Belgium', 'lifterlms' ),
				'PW' => __( 'Belau', 'lifterlms' ),
				'BZ' => __( 'Belize', 'lifterlms' ),
				'BJ' => __( 'Benin', 'lifterlms' ),
				'BM' => __( 'Bermuda', 'lifterlms' ),
				'BT' => __( 'Bhutan', 'lifterlms' ),
				'BO' => __( 'Bolivia', 'lifterlms' ),
				'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'lifterlms' ),
				'BA' => __( 'Bosnia and Herzegovina', 'lifterlms' ),
				'BW' => __( 'Botswana', 'lifterlms' ),
				'BV' => __( 'Bouvet Island', 'lifterlms' ),
				'BR' => __( 'Brazil', 'lifterlms' ),
				'IO' => __( 'British Indian Ocean Territory', 'lifterlms' ),
				'BN' => __( 'Brunei', 'lifterlms' ),
				'BG' => __( 'Bulgaria', 'lifterlms' ),
				'BF' => __( 'Burkina Faso', 'lifterlms' ),
				'BI' => __( 'Burundi', 'lifterlms' ),
				'KH' => __( 'Cambodia', 'lifterlms' ),
				'CM' => __( 'Cameroon', 'lifterlms' ),
				'CA' => __( 'Canada', 'lifterlms' ),
				'CV' => __( 'Cape Verde', 'lifterlms' ),
				'KY' => __( 'Cayman Islands', 'lifterlms' ),
				'CF' => __( 'Central African Republic', 'lifterlms' ),
				'TD' => __( 'Chad', 'lifterlms' ),
				'CL' => __( 'Chile', 'lifterlms' ),
				'CN' => __( 'China', 'lifterlms' ),
				'CX' => __( 'Christmas Island', 'lifterlms' ),
				'CC' => __( 'Cocos (Keeling) Islands', 'lifterlms' ),
				'CO' => __( 'Colombia', 'lifterlms' ),
				'KM' => __( 'Comoros', 'lifterlms' ),
				'CG' => __( 'Congo (Brazzaville)', 'lifterlms' ),
				'CD' => __( 'Congo (Kinshasa)', 'lifterlms' ),
				'CK' => __( 'Cook Islands', 'lifterlms' ),
				'CR' => __( 'Costa Rica', 'lifterlms' ),
				'HR' => __( 'Croatia', 'lifterlms' ),
				'CU' => __( 'Cuba', 'lifterlms' ),
				'CW' => __( 'Cura&ccedil;ao', 'lifterlms' ),
				'CY' => __( 'Cyprus', 'lifterlms' ),
				'CZ' => __( 'Czech Republic', 'lifterlms' ),
				'DK' => __( 'Denmark', 'lifterlms' ),
				'DJ' => __( 'Djibouti', 'lifterlms' ),
				'DM' => __( 'Dominica', 'lifterlms' ),
				'DO' => __( 'Dominican Republic', 'lifterlms' ),
				'EC' => __( 'Ecuador', 'lifterlms' ),
				'EG' => __( 'Egypt', 'lifterlms' ),
				'SV' => __( 'El Salvador', 'lifterlms' ),
				'GQ' => __( 'Equatorial Guinea', 'lifterlms' ),
				'ER' => __( 'Eritrea', 'lifterlms' ),
				'EE' => __( 'Estonia', 'lifterlms' ),
				'ET' => __( 'Ethiopia', 'lifterlms' ),
				'FK' => __( 'Falkland Islands', 'lifterlms' ),
				'FO' => __( 'Faroe Islands', 'lifterlms' ),
				'FJ' => __( 'Fiji', 'lifterlms' ),
				'FI' => __( 'Finland', 'lifterlms' ),
				'FR' => __( 'France', 'lifterlms' ),
				'GF' => __( 'French Guiana', 'lifterlms' ),
				'PF' => __( 'French Polynesia', 'lifterlms' ),
				'TF' => __( 'French Southern Territories', 'lifterlms' ),
				'GA' => __( 'Gabon', 'lifterlms' ),
				'GM' => __( 'Gambia', 'lifterlms' ),
				'GE' => __( 'Georgia', 'lifterlms' ),
				'DE' => __( 'Germany', 'lifterlms' ),
				'GH' => __( 'Ghana', 'lifterlms' ),
				'GI' => __( 'Gibraltar', 'lifterlms' ),
				'GR' => __( 'Greece', 'lifterlms' ),
				'GL' => __( 'Greenland', 'lifterlms' ),
				'GD' => __( 'Grenada', 'lifterlms' ),
				'GP' => __( 'Guadeloupe', 'lifterlms' ),
				'GU' => __( 'Guam', 'lifterlms' ),
				'GT' => __( 'Guatemala', 'lifterlms' ),
				'GG' => __( 'Guernsey', 'lifterlms' ),
				'GN' => __( 'Guinea', 'lifterlms' ),
				'GW' => __( 'Guinea-Bissau', 'lifterlms' ),
				'GY' => __( 'Guyana', 'lifterlms' ),
				'HT' => __( 'Haiti', 'lifterlms' ),
				'HM' => __( 'Heard Island and McDonald Islands', 'lifterlms' ),
				'HN' => __( 'Honduras', 'lifterlms' ),
				'HK' => __( 'Hong Kong', 'lifterlms' ),
				'HU' => __( 'Hungary', 'lifterlms' ),
				'IS' => __( 'Iceland', 'lifterlms' ),
				'IN' => __( 'India', 'lifterlms' ),
				'ID' => __( 'Indonesia', 'lifterlms' ),
				'IR' => __( 'Iran', 'lifterlms' ),
				'IQ' => __( 'Iraq', 'lifterlms' ),
				'IE' => __( 'Ireland', 'lifterlms' ),
				'IM' => __( 'Isle of Man', 'lifterlms' ),
				'IL' => __( 'Israel', 'lifterlms' ),
				'IT' => __( 'Italy', 'lifterlms' ),
				'CI' => __( 'Ivory Coast', 'lifterlms' ),
				'JM' => __( 'Jamaica', 'lifterlms' ),
				'JP' => __( 'Japan', 'lifterlms' ),
				'JE' => __( 'Jersey', 'lifterlms' ),
				'JO' => __( 'Jordan', 'lifterlms' ),
				'KZ' => __( 'Kazakhstan', 'lifterlms' ),
				'KE' => __( 'Kenya', 'lifterlms' ),
				'KI' => __( 'Kiribati', 'lifterlms' ),
				'KW' => __( 'Kuwait', 'lifterlms' ),
				'KG' => __( 'Kyrgyzstan', 'lifterlms' ),
				'LA' => __( 'Laos', 'lifterlms' ),
				'LV' => __( 'Latvia', 'lifterlms' ),
				'LB' => __( 'Lebanon', 'lifterlms' ),
				'LS' => __( 'Lesotho', 'lifterlms' ),
				'LR' => __( 'Liberia', 'lifterlms' ),
				'LY' => __( 'Libya', 'lifterlms' ),
				'LI' => __( 'Liechtenstein', 'lifterlms' ),
				'LT' => __( 'Lithuania', 'lifterlms' ),
				'LU' => __( 'Luxembourg', 'lifterlms' ),
				'MO' => __( 'Macao S.A.R., China', 'lifterlms' ),
				'MK' => __( 'Macedonia', 'lifterlms' ),
				'MG' => __( 'Madagascar', 'lifterlms' ),
				'MW' => __( 'Malawi', 'lifterlms' ),
				'MY' => __( 'Malaysia', 'lifterlms' ),
				'MV' => __( 'Maldives', 'lifterlms' ),
				'ML' => __( 'Mali', 'lifterlms' ),
				'MT' => __( 'Malta', 'lifterlms' ),
				'MH' => __( 'Marshall Islands', 'lifterlms' ),
				'MQ' => __( 'Martinique', 'lifterlms' ),
				'MR' => __( 'Mauritania', 'lifterlms' ),
				'MU' => __( 'Mauritius', 'lifterlms' ),
				'YT' => __( 'Mayotte', 'lifterlms' ),
				'MX' => __( 'Mexico', 'lifterlms' ),
				'FM' => __( 'Micronesia', 'lifterlms' ),
				'MD' => __( 'Moldova', 'lifterlms' ),
				'MC' => __( 'Monaco', 'lifterlms' ),
				'MN' => __( 'Mongolia', 'lifterlms' ),
				'ME' => __( 'Montenegro', 'lifterlms' ),
				'MS' => __( 'Montserrat', 'lifterlms' ),
				'MA' => __( 'Morocco', 'lifterlms' ),
				'MZ' => __( 'Mozambique', 'lifterlms' ),
				'MM' => __( 'Myanmar', 'lifterlms' ),
				'NA' => __( 'Namibia', 'lifterlms' ),
				'NR' => __( 'Nauru', 'lifterlms' ),
				'NP' => __( 'Nepal', 'lifterlms' ),
				'NL' => __( 'Netherlands', 'lifterlms' ),
				'NC' => __( 'New Caledonia', 'lifterlms' ),
				'NZ' => __( 'New Zealand', 'lifterlms' ),
				'NI' => __( 'Nicaragua', 'lifterlms' ),
				'NE' => __( 'Niger', 'lifterlms' ),
				'NG' => __( 'Nigeria', 'lifterlms' ),
				'NU' => __( 'Niue', 'lifterlms' ),
				'NF' => __( 'Norfolk Island', 'lifterlms' ),
				'MP' => __( 'Northern Mariana Islands', 'lifterlms' ),
				'KP' => __( 'North Korea', 'lifterlms' ),
				'NO' => __( 'Norway', 'lifterlms' ),
				'OM' => __( 'Oman', 'lifterlms' ),
				'PK' => __( 'Pakistan', 'lifterlms' ),
				'PS' => __( 'Palestinian Territory', 'lifterlms' ),
				'PA' => __( 'Panama', 'lifterlms' ),
				'PG' => __( 'Papua New Guinea', 'lifterlms' ),
				'PY' => __( 'Paraguay', 'lifterlms' ),
				'PE' => __( 'Peru', 'lifterlms' ),
				'PH' => __( 'Philippines', 'lifterlms' ),
				'PN' => __( 'Pitcairn', 'lifterlms' ),
				'PL' => __( 'Poland', 'lifterlms' ),
				'PT' => __( 'Portugal', 'lifterlms' ),
				'PR' => __( 'Puerto Rico', 'lifterlms' ),
				'QA' => __( 'Qatar', 'lifterlms' ),
				'RE' => __( 'Reunion', 'lifterlms' ),
				'RO' => __( 'Romania', 'lifterlms' ),
				'RU' => __( 'Russia', 'lifterlms' ),
				'RW' => __( 'Rwanda', 'lifterlms' ),
				'BL' => __( 'Saint Barth&eacute;lemy', 'lifterlms' ),
				'SH' => __( 'Saint Helena', 'lifterlms' ),
				'KN' => __( 'Saint Kitts and Nevis', 'lifterlms' ),
				'LC' => __( 'Saint Lucia', 'lifterlms' ),
				'MF' => __( 'Saint Martin (French part)', 'lifterlms' ),
				'SX' => __( 'Saint Martin (Dutch part)', 'lifterlms' ),
				'PM' => __( 'Saint Pierre and Miquelon', 'lifterlms' ),
				'VC' => __( 'Saint Vincent and the Grenadines', 'lifterlms' ),
				'SM' => __( 'San Marino', 'lifterlms' ),
				'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'lifterlms' ),
				'SA' => __( 'Saudi Arabia', 'lifterlms' ),
				'SN' => __( 'Senegal', 'lifterlms' ),
				'RS' => __( 'Serbia', 'lifterlms' ),
				'SC' => __( 'Seychelles', 'lifterlms' ),
				'SL' => __( 'Sierra Leone', 'lifterlms' ),
				'SG' => __( 'Singapore', 'lifterlms' ),
				'SK' => __( 'Slovakia', 'lifterlms' ),
				'SI' => __( 'Slovenia', 'lifterlms' ),
				'SB' => __( 'Solomon Islands', 'lifterlms' ),
				'SO' => __( 'Somalia', 'lifterlms' ),
				'ZA' => __( 'South Africa', 'lifterlms' ),
				'GS' => __( 'South Georgia/Sandwich Islands', 'lifterlms' ),
				'KR' => __( 'South Korea', 'lifterlms' ),
				'SS' => __( 'South Sudan', 'lifterlms' ),
				'ES' => __( 'Spain', 'lifterlms' ),
				'LK' => __( 'Sri Lanka', 'lifterlms' ),
				'SD' => __( 'Sudan', 'lifterlms' ),
				'SR' => __( 'Suriname', 'lifterlms' ),
				'SJ' => __( 'Svalbard and Jan Mayen', 'lifterlms' ),
				'SZ' => __( 'Swaziland', 'lifterlms' ),
				'SE' => __( 'Sweden', 'lifterlms' ),
				'CH' => __( 'Switzerland', 'lifterlms' ),
				'SY' => __( 'Syria', 'lifterlms' ),
				'TW' => __( 'Taiwan', 'lifterlms' ),
				'TJ' => __( 'Tajikistan', 'lifterlms' ),
				'TZ' => __( 'Tanzania', 'lifterlms' ),
				'TH' => __( 'Thailand', 'lifterlms' ),
				'TL' => __( 'Timor-Leste', 'lifterlms' ),
				'TG' => __( 'Togo', 'lifterlms' ),
				'TK' => __( 'Tokelau', 'lifterlms' ),
				'TO' => __( 'Tonga', 'lifterlms' ),
				'TT' => __( 'Trinidad and Tobago', 'lifterlms' ),
				'TN' => __( 'Tunisia', 'lifterlms' ),
				'TR' => __( 'Turkey', 'lifterlms' ),
				'TM' => __( 'Turkmenistan', 'lifterlms' ),
				'TC' => __( 'Turks and Caicos Islands', 'lifterlms' ),
				'TV' => __( 'Tuvalu', 'lifterlms' ),
				'UG' => __( 'Uganda', 'lifterlms' ),
				'UA' => __( 'Ukraine', 'lifterlms' ),
				'AE' => __( 'United Arab Emirates', 'lifterlms' ),
				'GB' => __( 'United Kingdom (UK)', 'lifterlms' ),
				'US' => __( 'United States (US)', 'lifterlms' ),
				'UM' => __( 'United States (US) Minor Outlying Islands', 'lifterlms' ),
				'UY' => __( 'Uruguay', 'lifterlms' ),
				'UZ' => __( 'Uzbekistan', 'lifterlms' ),
				'VU' => __( 'Vanuatu', 'lifterlms' ),
				'VA' => __( 'Vatican', 'lifterlms' ),
				'VE' => __( 'Venezuela', 'lifterlms' ),
				'VN' => __( 'Vietnam', 'lifterlms' ),
				'VG' => __( 'Virgin Islands (British)', 'lifterlms' ),
				'VI' => __( 'Virgin Islands (US)', 'lifterlms' ),
				'WF' => __( 'Wallis and Futuna', 'lifterlms' ),
				'EH' => __( 'Western Sahara', 'lifterlms' ),
				'WS' => __( 'Samoa', 'lifterlms' ),
				'YE' => __( 'Yemen', 'lifterlms' ),
				'ZM' => __( 'Zambia', 'lifterlms' ),
				'ZW' => __( 'Zimbabwe', 'lifterlms' ),
			)
		)
	);
}

/**
 * Get the default LLMS country
 *
 * @return   string     country code
 * @since    3.0.0
 * @version  3.0.0
 */
function get_lifterlms_country() {
	return apply_filters( 'lifterlms_country', get_option( 'lifterlms_country', 'US' ) );
}

/**
 * Get the currency selected
 *
 * @return string      currency code
 * @since  1.0.0
 * @version 3.0.0 - added USD default
 */
function get_lifterlms_currency() {
	return apply_filters( 'lifterlms_currency', get_option( 'lifterlms_currency', 'USD' ) );
}

/**
 * Get the name of a currency
 *
 * @param  string $currency a currency code
 * @return string
 * @since  3.0.0
 * @version  3.0.0
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

	return apply_filters( 'lifterlms_currency_name', $name, $currency );
}

/**
 * Get array of supported currencies
 *
 * @return array
 * @version  3.0.0
 */
function get_lifterlms_currencies() {
	return array_unique(
		apply_filters(
			'lifterlms_currencies',
			array(
				'AED' => __( 'United Arab Emirates dirham', 'lifterlms' ),
				'AFN' => __( 'Afghan afghani', 'lifterlms' ),
				'ALL' => __( 'Albanian lek', 'lifterlms' ),
				'AMD' => __( 'Armenian dram', 'lifterlms' ),
				'ANG' => __( 'Netherlands Antillean guilder', 'lifterlms' ),
				'AOA' => __( 'Angolan kwanza', 'lifterlms' ),
				'ARS' => __( 'Argentine peso', 'lifterlms' ),
				'AUD' => __( 'Australian dollar', 'lifterlms' ),
				'AWG' => __( 'Aruban florin', 'lifterlms' ),
				'AZN' => __( 'Azerbaijani manat', 'lifterlms' ),
				'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'lifterlms' ),
				'BBD' => __( 'Barbadian dollar', 'lifterlms' ),
				'BDT' => __( 'Bangladeshi taka', 'lifterlms' ),
				'BGN' => __( 'Bulgarian lev', 'lifterlms' ),
				'BHD' => __( 'Bahraini dinar', 'lifterlms' ),
				'BIF' => __( 'Burundian franc', 'lifterlms' ),
				'BMD' => __( 'Bermudian dollar', 'lifterlms' ),
				'BND' => __( 'Brunei dollar', 'lifterlms' ),
				'BOB' => __( 'Bolivian boliviano', 'lifterlms' ),
				'BRL' => __( 'Brazilian real', 'lifterlms' ),
				'BSD' => __( 'Bahamian dollar', 'lifterlms' ),
				'BTC' => __( 'Bitcoin', 'lifterlms' ),
				'BTN' => __( 'Bhutanese ngultrum', 'lifterlms' ),
				'BWP' => __( 'Botswana pula', 'lifterlms' ),
				'BYR' => __( 'Belarusian ruble', 'lifterlms' ),
				'BZD' => __( 'Belize dollar', 'lifterlms' ),
				'CAD' => __( 'Canadian dollar', 'lifterlms' ),
				'CDF' => __( 'Congolese franc', 'lifterlms' ),
				'CHF' => __( 'Swiss franc', 'lifterlms' ),
				'CLP' => __( 'Chilean peso', 'lifterlms' ),
				'CNY' => __( 'Chinese yuan', 'lifterlms' ),
				'COP' => __( 'Colombian peso', 'lifterlms' ),
				'CRC' => __( 'Costa Rican col&oacute;n', 'lifterlms' ),
				'CUC' => __( 'Cuban convertible peso', 'lifterlms' ),
				'CUP' => __( 'Cuban peso', 'lifterlms' ),
				'CVE' => __( 'Cape Verdean escudo', 'lifterlms' ),
				'CZK' => __( 'Czech koruna', 'lifterlms' ),
				'DJF' => __( 'Djiboutian franc', 'lifterlms' ),
				'DKK' => __( 'Danish krone', 'lifterlms' ),
				'DOP' => __( 'Dominican peso', 'lifterlms' ),
				'DZD' => __( 'Algerian dinar', 'lifterlms' ),
				'EGP' => __( 'Egyptian pound', 'lifterlms' ),
				'ERN' => __( 'Eritrean nakfa', 'lifterlms' ),
				'ETB' => __( 'Ethiopian birr', 'lifterlms' ),
				'EUR' => __( 'Euro', 'lifterlms' ),
				'FJD' => __( 'Fijian dollar', 'lifterlms' ),
				'FKP' => __( 'Falkland Islands pound', 'lifterlms' ),
				'GBP' => __( 'Pound sterling', 'lifterlms' ),
				'GEL' => __( 'Georgian lari', 'lifterlms' ),
				'GGP' => __( 'Guernsey pound', 'lifterlms' ),
				'GHS' => __( 'Ghana cedi', 'lifterlms' ),
				'GIP' => __( 'Gibraltar pound', 'lifterlms' ),
				'GMD' => __( 'Gambian dalasi', 'lifterlms' ),
				'GNF' => __( 'Guinean franc', 'lifterlms' ),
				'GTQ' => __( 'Guatemalan quetzal', 'lifterlms' ),
				'GYD' => __( 'Guyanese dollar', 'lifterlms' ),
				'HKD' => __( 'Hong Kong dollar', 'lifterlms' ),
				'HNL' => __( 'Honduran lempira', 'lifterlms' ),
				'HRK' => __( 'Croatian kuna', 'lifterlms' ),
				'HTG' => __( 'Haitian gourde', 'lifterlms' ),
				'HUF' => __( 'Hungarian forint', 'lifterlms' ),
				'IDR' => __( 'Indonesian rupiah', 'lifterlms' ),
				'ILS' => __( 'Israeli new shekel', 'lifterlms' ),
				'IMP' => __( 'Manx pound', 'lifterlms' ),
				'INR' => __( 'Indian rupee', 'lifterlms' ),
				'IQD' => __( 'Iraqi dinar', 'lifterlms' ),
				'IRR' => __( 'Iranian rial', 'lifterlms' ),
				'ISK' => __( 'Icelandic kr&oacute;na', 'lifterlms' ),
				'JEP' => __( 'Jersey pound', 'lifterlms' ),
				'JMD' => __( 'Jamaican dollar', 'lifterlms' ),
				'JOD' => __( 'Jordanian dinar', 'lifterlms' ),
				'JPY' => __( 'Japanese yen', 'lifterlms' ),
				'KES' => __( 'Kenyan shilling', 'lifterlms' ),
				'KGS' => __( 'Kyrgyzstani som', 'lifterlms' ),
				'KHR' => __( 'Cambodian riel', 'lifterlms' ),
				'KMF' => __( 'Comorian franc', 'lifterlms' ),
				'KPW' => __( 'North Korean won', 'lifterlms' ),
				'KRW' => __( 'South Korean won', 'lifterlms' ),
				'KWD' => __( 'Kuwaiti dinar', 'lifterlms' ),
				'KYD' => __( 'Cayman Islands dollar', 'lifterlms' ),
				'KZT' => __( 'Kazakhstani tenge', 'lifterlms' ),
				'LAK' => __( 'Lao kip', 'lifterlms' ),
				'LBP' => __( 'Lebanese pound', 'lifterlms' ),
				'LKR' => __( 'Sri Lankan rupee', 'lifterlms' ),
				'LRD' => __( 'Liberian dollar', 'lifterlms' ),
				'LSL' => __( 'Lesotho loti', 'lifterlms' ),
				'LYD' => __( 'Libyan dinar', 'lifterlms' ),
				'MAD' => __( 'Moroccan dirham', 'lifterlms' ),
				'MDL' => __( 'Moldovan leu', 'lifterlms' ),
				'MGA' => __( 'Malagasy ariary', 'lifterlms' ),
				'MKD' => __( 'Macedonian denar', 'lifterlms' ),
				'MMK' => __( 'Burmese kyat', 'lifterlms' ),
				'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'lifterlms' ),
				'MOP' => __( 'Macanese pataca', 'lifterlms' ),
				'MRO' => __( 'Mauritanian ouguiya', 'lifterlms' ),
				'MUR' => __( 'Mauritian rupee', 'lifterlms' ),
				'MVR' => __( 'Maldivian rufiyaa', 'lifterlms' ),
				'MWK' => __( 'Malawian kwacha', 'lifterlms' ),
				'MXN' => __( 'Mexican peso', 'lifterlms' ),
				'MYR' => __( 'Malaysian ringgit', 'lifterlms' ),
				'MZN' => __( 'Mozambican metical', 'lifterlms' ),
				'NAD' => __( 'Namibian dollar', 'lifterlms' ),
				'NGN' => __( 'Nigerian naira', 'lifterlms' ),
				'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'lifterlms' ),
				'NOK' => __( 'Norwegian krone', 'lifterlms' ),
				'NPR' => __( 'Nepalese rupee', 'lifterlms' ),
				'NZD' => __( 'New Zealand dollar', 'lifterlms' ),
				'OMR' => __( 'Omani rial', 'lifterlms' ),
				'PAB' => __( 'Panamanian balboa', 'lifterlms' ),
				'PEN' => __( 'Peruvian nuevo sol', 'lifterlms' ),
				'PGK' => __( 'Papua New Guinean kina', 'lifterlms' ),
				'PHP' => __( 'Philippine peso', 'lifterlms' ),
				'PKR' => __( 'Pakistani rupee', 'lifterlms' ),
				'PLN' => __( 'Polish z&#x142;oty', 'lifterlms' ),
				'PRB' => __( 'Transnistrian ruble', 'lifterlms' ),
				'PYG' => __( 'Paraguayan guaran&iacute;', 'lifterlms' ),
				'QAR' => __( 'Qatari riyal', 'lifterlms' ),
				'RON' => __( 'Romanian leu', 'lifterlms' ),
				'RSD' => __( 'Serbian dinar', 'lifterlms' ),
				'RUB' => __( 'Russian ruble', 'lifterlms' ),
				'RWF' => __( 'Rwandan franc', 'lifterlms' ),
				'SAR' => __( 'Saudi riyal', 'lifterlms' ),
				'SBD' => __( 'Solomon Islands dollar', 'lifterlms' ),
				'SCR' => __( 'Seychellois rupee', 'lifterlms' ),
				'SDG' => __( 'Sudanese pound', 'lifterlms' ),
				'SEK' => __( 'Swedish krona', 'lifterlms' ),
				'SGD' => __( 'Singapore dollar', 'lifterlms' ),
				'SHP' => __( 'Saint Helena pound', 'lifterlms' ),
				'SLL' => __( 'Sierra Leonean leone', 'lifterlms' ),
				'SOS' => __( 'Somali shilling', 'lifterlms' ),
				'SRD' => __( 'Surinamese dollar', 'lifterlms' ),
				'SSP' => __( 'South Sudanese pound', 'lifterlms' ),
				'STD' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'lifterlms' ),
				'SYP' => __( 'Syrian pound', 'lifterlms' ),
				'SZL' => __( 'Swazi lilangeni', 'lifterlms' ),
				'THB' => __( 'Thai baht', 'lifterlms' ),
				'TJS' => __( 'Tajikistani somoni', 'lifterlms' ),
				'TMT' => __( 'Turkmenistan manat', 'lifterlms' ),
				'TND' => __( 'Tunisian dinar', 'lifterlms' ),
				'TOP' => __( 'Tongan pa&#x2bb;anga', 'lifterlms' ),
				'TRY' => __( 'Turkish lira', 'lifterlms' ),
				'TTD' => __( 'Trinidad and Tobago dollar', 'lifterlms' ),
				'TWD' => __( 'New Taiwan dollar', 'lifterlms' ),
				'TZS' => __( 'Tanzanian shilling', 'lifterlms' ),
				'UAH' => __( 'Ukrainian hryvnia', 'lifterlms' ),
				'UGX' => __( 'Ugandan shilling', 'lifterlms' ),
				'USD' => __( 'United States dollar', 'lifterlms' ),
				'UYU' => __( 'Uruguayan peso', 'lifterlms' ),
				'UZS' => __( 'Uzbekistani som', 'lifterlms' ),
				'VEF' => __( 'Venezuelan bol&iacute;var', 'lifterlms' ),
				'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'lifterlms' ),
				'VUV' => __( 'Vanuatu vatu', 'lifterlms' ),
				'WST' => __( 'Samoan t&#x101;l&#x101;', 'lifterlms' ),
				'XAF' => __( 'Central African CFA franc', 'lifterlms' ),
				'XCD' => __( 'East Caribbean dollar', 'lifterlms' ),
				'XOF' => __( 'West African CFA franc', 'lifterlms' ),
				'XPF' => __( 'CFP franc', 'lifterlms' ),
				'YER' => __( 'Yemeni rial', 'lifterlms' ),
				'ZAR' => __( 'South African rand', 'lifterlms' ),
				'ZMW' => __( 'Zambian kwacha', 'lifterlms' ),
			)
		)
	);
}

/**
 * Get Currency Symbol text code
 *
 * @since Unknown
 * @since 3.30.3 Removed duplicate key "MAD".
 *
 * @param  string $currency Currency Code.
 * @return string
 */
function get_lifterlms_currency_symbol( $currency = '' ) {
	if ( ! $currency ) {
		$currency = get_lifterlms_currency();
	}

	$symbols = apply_filters(
		'lifterlms_currency_symbols',
		array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => '&fnof;',
			'AZN' => 'AZN',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'DKK',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x10da;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'Kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x639;.&#x62f;',
			'IRR' => '&#xfdfc;',
			'ISK' => 'Kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x43b;&#x432;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => 'KZT',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x644;.&#x62f;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'L',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRO' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/.',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#x434;&#x438;&#x43d;.',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STD' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'L',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'Fr',
			'XCD' => '&#36;',
			'XOF' => 'Fr',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		)
	);

	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

	return apply_filters( 'lifterlms_currency_symbol', $currency_symbol, $currency );
}

/**
 * Get the number of decimals places used for prices
 * as defined by the setting
 *
 * @return int
 * @since  3.0.0
 */
function get_lifterlms_decimals() {
	return absint( apply_filters( 'lifterlms_decimals', get_option( 'lifterlms_decimals', 2 ) ) );
}

/**
 * Retrieve the character used as a decimal separator
 *
 * @return string
 * @since  3.0.0
 */
function get_lifterlms_decimal_separator() {
	return apply_filters( 'lifterlms_decimal_separator', get_option( 'lifterlms_decimal_separator', '.' ) );
}

/**
 * Retrieve the setting for trimming zero value decimals from the end of prices
 *
 * @return string    yes or no
 * @since  3.0.0
 */
function get_lifterlms_trim_zero_decimals() {
	return apply_filters( 'lifterlms_trim_zero_decimals', get_option( 'lifterlms_trim_zero_decimals', 'no' ) );
}

/**
 * Get a format string that can be passed to printf or sprintf to format prices
 * as per user-defined price formatting settings
 *
 * @return string
 * @since  3.0.0
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
 * @return string
 * @since  3.0.0
 */
function get_lifterlms_thousand_separator() {
	return apply_filters( 'lifterlms_thousand_separator', get_option( 'lifterlms_thousand_separator', '.' ) );
}

/**
 * Retrieve the country name by country code
 *
 * @param    string $code  country code
 * @return   string
 * @since    3.8.0
 * @version  3.8.0
 */
function llms_get_country_name( $code ) {
	$countries = get_lifterlms_countries();
	return isset( $countries[ $code ] ) ? $countries[ $code ] : $code;
}

/**
 * Get a formatted price price
 *
 * @param  int   $price Price to display
 * @param  array $args  array of arguments
 * @return string
 * @version  3.0.0
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
 * @param  int   $price Price to display
 * @param  array $args  array of arguments
 * @return string
 * @version  3.0.0
 */
function llms_price_raw( $price, $args = array() ) {

	return html_entity_decode( strip_tags( llms_price( $price, $args ) ) );

}

/**
 * Trim trailing zeros off a price
 *
 * @param mixed $price
 * @return string
 * @since  3.0.0
 */
function llms_trim_zeros( $price ) {
	return preg_replace( '/' . preg_quote( get_lifterlms_decimal_separator(), '/' ) . '0++$/', '', $price );
}
