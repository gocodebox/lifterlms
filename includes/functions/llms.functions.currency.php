<?php
/**
 * Get the name of a currency
 * @param  string $currency a currency code
 * @return string
 *
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
 * Get Price
 *
 * @param  int    $price Price to display
 * @param  array  $args  Formatting arguments
 *                       @arg bool|int    $decimal_places  number of decimal places to include in the formatted price
 *                       @arg bool        $trim_zeros      if true, removes zeros from final price
 *                       @arg bool|string $with_currency   if true, add the default currency symbol, if false don't add, if string add the string as the currency symbol
 *
 * @return int $price
 */
function llms_price( $price, $args = array() ) {

	extract( array_merge( array(
		'decimal_places' => false,
		'trim_zeros'     => false,
		'with_currency'  => false,
	), $args ) );

	if ( $trim_zeros || $decimal_places ) {
		$price = llms_format_decimal( $price, $decimal_places, $trim_zeros );
	}

	// if true, add default currency symbol to price
	if ( true === $with_currency ) {
		$price = get_lifterlms_currency_symbol() . $price;
	} // if it's a string, assume that's the supplied currency symbol
	elseif ( is_string( $with_currency ) ) {
		$price = $with_currency . $price;
	}

	return $price;
}

/**
 * Format Number as decimal
 *
 * @param  int  $number     [price value]
 * @param  boolean $dp         [decimal points]
 * @param  boolean $trim_zeros [trim zeros?]
 *
 * @return string [formatted number]
 */
function llms_format_decimal( $number, $dp = false, $trim_zeros = false ) {
	  // Remove locale from string
	if ( ! is_float( $number ) ) {
		  $locale   = localeconv();
		  $decimals = array( get_option( 'lifterlms_price_decimal_sep' ), $locale['decimal_point'], $locale['mon_decimal_point'] );
		  $number   = llms_clean( str_replace( $decimals, '.', $number ) );
	}

	  // DP is false - don't use number format, just return a string in our format
	if ( $dp !== false ) {
		  $dp = 2;     //= intval( $dp == "" ? get_option( 'lifterlms_price_num_decimals' ) : $dp );
		  $number = number_format( floatval( $number ), $dp, '.', ',' );
	}

	if ( $trim_zeros && strstr( $number, '.' ) ) {
		  $number = rtrim( rtrim( $number, '0' ), '.' );
	}

	  return $number;
}


/**
 * Get Countries array for Select list
 * @return array [Countries list]
 */
function get_lifterlms_countries() {
	return array_unique(
		apply_filters( 'lifterlms_countries',
			array(
			'US' => __( 'United States', 'lifterlms' ),
			'AF' => __( 'Afghanistan', 'lifterlms' ),
			'AL' => __( 'Albania', 'lifterlms' ),
			'DZ' => __( 'Algeria', 'lifterlms' ),
			'AS' => __( 'American Samoa', 'lifterlms' ),
			'AD' => __( 'Andorra', 'lifterlms' ),
			'AO' => __( 'Angola', 'lifterlms' ),
			'AI' => __( 'Anguilla', 'lifterlms' ),
			'AQ' => __( 'Antarctica', 'lifterlms' ),
			'AG' => __( 'Antigua And Barbuda', 'lifterlms' ),
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
			'BZ' => __( 'Belize', 'lifterlms' ),
			'BJ' => __( 'Benin', 'lifterlms' ),
			'BM' => __( 'Bermuda', 'lifterlms' ),
			'BT' => __( 'Bhutan', 'lifterlms' ),
			'BO' => __( 'Bolivia', 'lifterlms' ),
			'BA' => __( 'Bosnia And Herzegowina', 'lifterlms' ),
			'BW' => __( 'Botswana', 'lifterlms' ),
			'BV' => __( 'Bouvet Island', 'lifterlms' ),
			'BR' => __( 'Brazil', 'lifterlms' ),
			'IO' => __( 'British Indian Ocean Territory', 'lifterlms' ),
			'BN' => __( 'Brunei Darussalam', 'lifterlms' ),
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
			'CG' => __( 'Congo', 'lifterlms' ),
			'CD' => __( 'Congo, The Democratic Republic Of The', 'lifterlms' ),
			'CK' => __( 'Cook Islands', 'lifterlms' ),
			'CR' => __( 'Costa Rica', 'lifterlms' ),
			'CI' => __( 'Cote D\'Ivoire', 'lifterlms' ),
			'HR' => __( 'Croatia', 'lifterlms' ),
			'CU' => __( 'Cuba', 'lifterlms' ),
			'CY' => __( 'Cyprus', 'lifterlms' ),
			'CZ' => __( 'Czech Republic', 'lifterlms' ),
			'DK' => __( 'Denmark', 'lifterlms' ),
			'DJ' => __( 'Djibouti', 'lifterlms' ),
			'DM' => __( 'Dominica', 'lifterlms' ),
			'DO' => __( 'Dominican Republic', 'lifterlms' ),
			'TP' => __( 'East Timor', 'lifterlms' ),
			'EC' => __( 'Ecuador', 'lifterlms' ),
			'EG' => __( 'Egypt', 'lifterlms' ),
			'SV' => __( 'El Salvador', 'lifterlms' ),
			'GQ' => __( 'Equatorial Guinea', 'lifterlms' ),
			'ER' => __( 'Eritrea', 'lifterlms' ),
			'EE' => __( 'Estonia', 'lifterlms' ),
			'ET' => __( 'Ethiopia', 'lifterlms' ),
			'FK' => __( 'Falkland Islands (Malvinas)', 'lifterlms' ),
			'FO' => __( 'Faroe Islands', 'lifterlms' ),
			'FJ' => __( 'Fiji', 'lifterlms' ),
			'FI' => __( 'Finland', 'lifterlms' ),
			'FR' => __( 'France', 'lifterlms' ),
			'FX' => __( 'France, Metropolitan', 'lifterlms' ),
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
			'GN' => __( 'Guinea', 'lifterlms' ),
			'GW' => __( 'Guinea-Bissau', 'lifterlms' ),
			'GY' => __( 'Guyana', 'lifterlms' ),
			'HT' => __( 'Haiti', 'lifterlms' ),
			'HM' => __( 'Heard And Mc Donald Islands', 'lifterlms' ),
			'VA' => __( 'Holy See (Vatican City State)', 'lifterlms' ),
			'HN' => __( 'Honduras', 'lifterlms' ),
			'HK' => __( 'Hong Kong', 'lifterlms' ),
			'HU' => __( 'Hungary', 'lifterlms' ),
			'IS' => __( 'Iceland', 'lifterlms' ),
			'IN' => __( 'India', 'lifterlms' ),
			'ID' => __( 'Indonesia', 'lifterlms' ),
			'IR' => __( 'Iran (Islamic Republic Of)', 'lifterlms' ),
			'IQ' => __( 'Iraq', 'lifterlms' ),
			'IE' => __( 'Ireland', 'lifterlms' ),
			'IL' => __( 'Israel', 'lifterlms' ),
			'IT' => __( 'Italy', 'lifterlms' ),
			'JM' => __( 'Jamaica', 'lifterlms' ),
			'JP' => __( 'Japan', 'lifterlms' ),
			'JO' => __( 'Jordan', 'lifterlms' ),
			'KZ' => __( 'Kazakhstan', 'lifterlms' ),
			'KE' => __( 'Kenya', 'lifterlms' ),
			'KI' => __( 'Kiribati', 'lifterlms' ),
			'KP' => __( 'Korea, Democratic People\'s Republic Of', 'lifterlms' ),
			'KR' => __( 'Korea, Republic Of', 'lifterlms' ),
			'KW' => __( 'Kuwait', 'lifterlms' ),
			'KG' => __( 'Kyrgyzstan', 'lifterlms' ),
			'LA' => __( 'Lao People\'s Democratic Republic', 'lifterlms' ),
			'LV' => __( 'Latvia', 'lifterlms' ),
			'LB' => __( 'Lebanon', 'lifterlms' ),
			'LS' => __( 'Lesotho', 'lifterlms' ),
			'LR' => __( 'Liberia', 'lifterlms' ),
			'LY' => __( 'Libyan Arab Jamahiriya', 'lifterlms' ),
			'LI' => __( 'Liechtenstein', 'lifterlms' ),
			'LT' => __( 'Lithuania', 'lifterlms' ),
			'LU' => __( 'Luxembourg', 'lifterlms' ),
			'MO' => __( 'Macau', 'lifterlms' ),
			'MK' => __( 'Macedonia, Former Yugoslav Republic Of', 'lifterlms' ),
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
			'FM' => __( 'Micronesia, Federated States Of', 'lifterlms' ),
			'MD' => __( 'Moldova, Republic Of', 'lifterlms' ),
			'MC' => __( 'Monaco', 'lifterlms' ),
			'MN' => __( 'Mongolia', 'lifterlms' ),
			'MS' => __( 'Montserrat', 'lifterlms' ),
			'MA' => __( 'Morocco', 'lifterlms' ),
			'MZ' => __( 'Mozambique', 'lifterlms' ),
			'MM' => __( 'Myanmar', 'lifterlms' ),
			'NA' => __( 'Namibia', 'lifterlms' ),
			'NR' => __( 'Nauru', 'lifterlms' ),
			'NP' => __( 'Nepal', 'lifterlms' ),
			'NL' => __( 'Netherlands', 'lifterlms' ),
			'AN' => __( 'Netherlands Antilles', 'lifterlms' ),
			'NC' => __( 'New Caledonia', 'lifterlms' ),
			'NZ' => __( 'New Zealand', 'lifterlms' ),
			'NI' => __( 'Nicaragua', 'lifterlms' ),
			'NE' => __( 'Niger', 'lifterlms' ),
			'NG' => __( 'Nigeria', 'lifterlms' ),
			'NU' => __( 'Niue', 'lifterlms' ),
			'NF' => __( 'Norfolk Island', 'lifterlms' ),
			'MP' => __( 'Northern Mariana Islands', 'lifterlms' ),
			'NO' => __( 'Norway', 'lifterlms' ),
			'OM' => __( 'Oman', 'lifterlms' ),
			'PK' => __( 'Pakistan', 'lifterlms' ),
			'PW' => __( 'Palau', 'lifterlms' ),
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
			'RU' => __( 'Russian Federation', 'lifterlms' ),
			'RW' => __( 'Rwanda', 'lifterlms' ),
			'KN' => __( 'Saint Kitts And Nevis', 'lifterlms' ),
			'LC' => __( 'Saint Lucia', 'lifterlms' ),
			'VC' => __( 'Saint Vincent And The Grenadines', 'lifterlms' ),
			'WS' => __( 'Samoa', 'lifterlms' ),
			'SM' => __( 'San Marino', 'lifterlms' ),
			'ST' => __( 'Sao Tome And Principe', 'lifterlms' ),
			'SA' => __( 'Saudi Arabia', 'lifterlms' ),
			'SN' => __( 'Senegal', 'lifterlms' ),
			'SC' => __( 'Seychelles', 'lifterlms' ),
			'SL' => __( 'Sierra Leone', 'lifterlms' ),
			'SG' => __( 'Singapore', 'lifterlms' ),
			'SK' => __( 'Slovakia (Slovak Republic)', 'lifterlms' ),
			'SI' => __( 'Slovenia', 'lifterlms' ),
			'SB' => __( 'Solomon Islands', 'lifterlms' ),
			'SO' => __( 'Somalia', 'lifterlms' ),
			'ZA' => __( 'South Africa', 'lifterlms' ),
			'GS' => __( 'South Georgia, South Sandwich Islands', 'lifterlms' ),
			'ES' => __( 'Spain', 'lifterlms' ),
			'LK' => __( 'Sri Lanka', 'lifterlms' ),
			'SH' => __( 'St. Helena', 'lifterlms' ),
			'PM' => __( 'St. Pierre And Miquelon', 'lifterlms' ),
			'SD' => __( 'Sudan', 'lifterlms' ),
			'SR' => __( 'Suriname', 'lifterlms' ),
			'SJ' => __( 'Svalbard And Jan Mayen Islands', 'lifterlms' ),
			'SZ' => __( 'Swaziland', 'lifterlms' ),
			'SE' => __( 'Sweden', 'lifterlms' ),
			'CH' => __( 'Switzerland', 'lifterlms' ),
			'SY' => __( 'Syrian Arab Republic', 'lifterlms' ),
			'TW' => __( 'Taiwan', 'lifterlms' ),
			'TJ' => __( 'Tajikistan', 'lifterlms' ),
			'TZ' => __( 'Tanzania, United Republic Of', 'lifterlms' ),
			'TH' => __( 'Thailand', 'lifterlms' ),
			'TG' => __( 'Togo', 'lifterlms' ),
			'TK' => __( 'Tokelau', 'lifterlms' ),
			'TO' => __( 'Tonga', 'lifterlms' ),
			'TT' => __( 'Trinidad And Tobago', 'lifterlms' ),
			'TN' => __( 'Tunisia', 'lifterlms' ),
			'TR' => __( 'Turkey', 'lifterlms' ),
			'TM' => __( 'Turkmenistan', 'lifterlms' ),
			'TC' => __( 'Turks And Caicos Islands', 'lifterlms' ),
			'TV' => __( 'Tuvalu', 'lifterlms' ),
			'UG' => __( 'Uganda', 'lifterlms' ),
			'UA' => __( 'Ukraine', 'lifterlms' ),
			'AE' => __( 'United Arab Emirates', 'lifterlms' ),
			'GB' => __( 'United Kingdom', 'lifterlms' ),
			'UM' => __( 'United States Minor Outlying Islands', 'lifterlms' ),
			'UY' => __( 'Uruguay', 'lifterlms' ),
			'UZ' => __( 'Uzbekistan', 'lifterlms' ),
			'VU' => __( 'Vanuatu', 'lifterlms' ),
			'VE' => __( 'Venezuela', 'lifterlms' ),
			'VN' => __( 'Viet Nam', 'lifterlms' ),
			'VG' => __( 'Virgin Islands (British)', 'lifterlms' ),
			'VI' => __( 'Virgin Islands (U.S.)', 'lifterlms' ),
			'WF' => __( 'Wallis And Futuna Islands', 'lifterlms' ),
			'EH' => __( 'Western Sahara', 'lifterlms' ),
			'YE' => __( 'Yemen', 'lifterlms' ),
			'YU' => __( 'Yugoslavia', 'lifterlms' ),
			'ZM' => __( 'Zambia', 'lifterlms' ),
			'ZW' => __( 'Zimbabwe', 'lifterlms' ),
				)
		)
	);
}

/**
 * Get Currency Selection
 * @return string [Currency Id]
 */
function get_lifterlms_currency() {
	  return apply_filters( 'lifterlms_currency', get_option( 'lifterlms_currency' ) );
}

/**
 * Get Currency array for select list
 * @return array [Currecies]
 */
function get_lifterlms_currencies() {
	  return array_unique(
		  apply_filters( 'lifterlms_currencies',
			  array(
						'AED' => __( 'United Arab Emirates Dirham', 'lifterlms' ),
						'AUD' => __( 'Australian Dollars', 'lifterlms' ),
						'BDT' => __( 'Bangladeshi Taka', 'lifterlms' ),
						'BRL' => __( 'Brazilian Real', 'lifterlms' ),
						'BGN' => __( 'Bulgarian Lev', 'lifterlms' ),
						'CAD' => __( 'Canadian Dollars', 'lifterlms' ),
						'CLP' => __( 'Chilean Peso', 'lifterlms' ),
						'CNY' => __( 'Chinese Yuan', 'lifterlms' ),
						'CZK' => __( 'Czech Koruna', 'lifterlms' ),
						'DKK' => __( 'Danish Krone', 'lifterlms' ),
						'EUR' => __( 'Euros', 'lifterlms' ),
						'HKD' => __( 'Hong Kong Dollar', 'lifterlms' ),
						'HRK' => __( 'Croatia kuna', 'lifterlms' ),
						'HUF' => __( 'Hungarian Forint', 'lifterlms' ),
						'ISK' => __( 'Icelandic krona', 'lifterlms' ),
						'IDR' => __( 'Indonesia Rupiah', 'lifterlms' ),
						'INR' => __( 'Indian Rupee', 'lifterlms' ),
						'ILS' => __( 'Israeli Shekel', 'lifterlms' ),
						'JPY' => __( 'Japanese Yen', 'lifterlms' ),
						'KRW' => __( 'South Korean Won', 'lifterlms' ),
						'MYR' => __( 'Malaysian Ringgits', 'lifterlms' ),
						'MXN' => __( 'Mexican Peso', 'lifterlms' ),
						'NGN' => __( 'Nigerian Naira', 'lifterlms' ),
						'NOK' => __( 'Norwegian Krone', 'lifterlms' ),
						'NZD' => __( 'New Zealand Dollar', 'lifterlms' ),
						'PHP' => __( 'Philippine Pesos', 'lifterlms' ),
						'PLN' => __( 'Polish Zloty', 'lifterlms' ),
						'GBP' => __( 'Pounds Sterling', 'lifterlms' ),
						'RON' => __( 'Romanian Leu', 'lifterlms' ),
						'RUB' => __( 'Russian Ruble', 'lifterlms' ),
						'SGD' => __( 'Singapore Dollar', 'lifterlms' ),
						'ZAR' => __( 'South African rand', 'lifterlms' ),
						'SEK' => __( 'Swedish Krona', 'lifterlms' ),
						'CHF' => __( 'Swiss Franc', 'lifterlms' ),
						'TWD' => __( 'Taiwan New Dollars', 'lifterlms' ),
						'THB' => __( 'Thai Baht', 'lifterlms' ),
						'TRY' => __( 'Turkish Lira', 'lifterlms' ),
						'USD' => __( 'US Dollars', 'lifterlms' ),
						'VND' => __( 'Vietnamese Dong', 'lifterlms' ),
				  )
		  )
	  );
}

/**
 * Get Currency Symbol text code
 * @param  string $currency [Currency Id]
 * @return string [Currency Code]
 */
function get_lifterlms_currency_symbol( $currency = '' ) {
	if ( ! $currency ) {
		  $currency = get_lifterlms_currency();
	}

	switch ( $currency ) {
		case 'AED' :
			  $currency_symbol = 'د.إ';
				break;
		case 'BDT':
			  $currency_symbol = '&#2547;&nbsp;';
				break;
		case 'BRL' :
			  $currency_symbol = '&#82;&#36;';
				break;
		case 'BGN' :
			  $currency_symbol = '&#1083;&#1074;.';
				break;
		case 'AUD' :
		case 'CAD' :
		case 'CLP' :
		case 'MXN' :
		case 'NZD' :
		case 'HKD' :
		case 'SGD' :
		case 'USD' :
			  $currency_symbol = '&#36;';
				break;
		case 'EUR' :
			  $currency_symbol = '&euro;';
				break;
		case 'CNY' :
		case 'RMB' :
		case 'JPY' :
			  $currency_symbol = '&yen;';
				break;
		case 'RUB' :
			  $currency_symbol = '&#1088;&#1091;&#1073;.';
				break;
		case 'KRW' : $currency_symbol = '&#8361;'; break;
		case 'TRY' : $currency_symbol = '&#84;&#76;'; break;
		case 'NOK' : $currency_symbol = '&#107;&#114;'; break;
		case 'ZAR' : $currency_symbol = '&#82;'; break;
		case 'CZK' : $currency_symbol = '&#75;&#269;'; break;
		case 'MYR' : $currency_symbol = '&#82;&#77;'; break;
		case 'DKK' : $currency_symbol = 'kr.'; break;
		case 'HUF' : $currency_symbol = '&#70;&#116;'; break;
		case 'IDR' : $currency_symbol = 'Rp'; break;
		case 'INR' : $currency_symbol = 'Rs.'; break;
		case 'ISK' : $currency_symbol = 'Kr.'; break;
		case 'ILS' : $currency_symbol = '&#8362;'; break;
		case 'PHP' : $currency_symbol = '&#8369;'; break;
		case 'PLN' : $currency_symbol = '&#122;&#322;'; break;
		case 'SEK' : $currency_symbol = '&#107;&#114;'; break;
		case 'CHF' : $currency_symbol = '&#67;&#72;&#70;'; break;
		case 'TWD' : $currency_symbol = '&#78;&#84;&#36;'; break;
		case 'THB' : $currency_symbol = '&#3647;'; break;
		case 'GBP' : $currency_symbol = '&pound;'; break;
		case 'RON' : $currency_symbol = 'lei'; break;
		case 'VND' : $currency_symbol = '&#8363;'; break;
		case 'NGN' : $currency_symbol = '&#8358;'; break;
		case 'HRK' : $currency_symbol = 'Kn'; break;
		default    : $currency_symbol = ''; break;
	}

	  return apply_filters( 'lifterlms_currency_symbol', $currency_symbol, $currency );
}
