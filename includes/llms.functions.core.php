<?php
/**
* Core functions file
*
* Misc functions used by lifterLMS core.
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) exit;

//include other function files
include( 'functions/llms.functions.course.php' );
include( 'functions/llms.functions.notice.php' );
include( 'functions/llms.functions.page.php' );
include( 'functions/llms.functions.person.php' );
include( 'functions/llms.functions.access.php' );


//add_filter( 'lifterlms_get_single_price', 'llms_get_adjusted_price', 10, 2 );
// function llms_get_adjusted_price($price, $product) {
      
//       $adjustment = llms_get_coupon();
//       LLMS_log($adjustment);
//       if ( !empty( $adjustment && $adjustment->amount > 0 ) ) {
//             if ($adjustment->type == 'percent') {
//                   LLMS_log('percent was found');
//                   $amount =  (1 - ($adjustment->amount / 100));
//                   LLMS_log($amount);
//                   $total = ($price * $amount);
//                   LLMS_log($total);
//             }
//             elseif ($adjustment->type == 'dollar') {
//                   $amount = round( $adjustment->amount, 2 );
//                   $total = ($price - $amount);
//             }
//       }

//       return $total;

// }

function llms_get_coupon() {
      $coupon = LLMS()->session->get( 'llms_coupon', array() );
      LLMS_log('coupon session');
      LLMS_log($coupon);
      return $coupon;
}
/**
 * Get attribute taxonomies.
 *
 * @return array
 */
function llms_get_attribute_taxonomies() {

	$transient_name = 'llms_attribute_taxonomies';

	if ( false === ( $attribute_taxonomies = get_transient( $transient_name ) ) ) {

		global $wpdb;

		$attribute_taxonomies = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "lifterlms_attribute_taxonomies" );

		set_transient( $transient_name, $attribute_taxonomies );
	}

	return apply_filters( 'lifterlms_attribute_taxonomies', $attribute_taxonomies );
}

function _llms_term_recount( $terms, $taxonomy, $callback = true, $terms_are_term_taxonomy_ids = true ) {
	global $wpdb;

	// Standard callback
	if ( $callback ) {
		_update_post_term_count( $terms, $taxonomy );
	}

	// Stock query
	if ( get_option( 'lifterlms_hide_out_of_stock_items' ) == 'yes' ) {
		$stock_join  = "LEFT JOIN {$wpdb->postmeta} AS meta_stock ON posts.ID = meta_stock.post_id";
		$stock_query = "
		AND meta_stock.meta_key = '_stock_status'
		AND meta_stock.meta_value = 'instock'
		";
	} else {
		$stock_query = $stock_join = '';
	}

	// Main query
	$count_query = "
		SELECT COUNT( DISTINCT posts.ID ) FROM {$wpdb->posts} as posts
		LEFT JOIN {$wpdb->postmeta} AS meta_visibility ON posts.ID = meta_visibility.post_id
		LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
		LEFT JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id )
		LEFT JOIN {$wpdb->terms} AS term USING( term_id )
		LEFT JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
		$stock_join
		WHERE 	post_status = 'publish'
		AND 	post_type 	= 'course'
		AND 	meta_visibility.meta_key = '_visibility'
		AND 	meta_visibility.meta_value IN ( 'visible', 'catalog' )
		$stock_query
	";

	// Pre-process term taxonomy ids
	if ( ! $terms_are_term_taxonomy_ids ) {
		// We passed in an array of TERMS in format id=>parent
		$terms = array_filter( (array) array_keys( $terms ) );
	} else {
		// If we have term taxonomy IDs we need to get the term ID
		$term_taxonomy_ids = $terms;
		$terms             = array();
		foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {
			$term    = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, $taxonomy->name );
			$terms[] = $term->term_id;
		}
	}

	// Exit if we have no terms to count
	if ( ! $terms ) {
		return;
	}

	// Ancestors need counting
	if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
		foreach ( $terms as $term_id ) {
			$terms = array_merge( $terms, get_ancestors( $term_id, $taxonomy->name ) );
		}
	}

	// Unique terms only
	$terms = array_unique( $terms );

	// Count the terms
	foreach ( $terms as $term_id ) {
		$terms_to_count = array( absint( $term_id ) );

		if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
			// We need to get the $term's hierarchy so we can count its children too
			if ( ( $children = get_term_children( $term_id, $taxonomy->name ) ) && ! is_wp_error( $children ) ) {
				$terms_to_count = array_unique( array_map( 'absint', array_merge( $terms_to_count, $children ) ) );
			}
		}

		// Generate term query
		$term_query = 'AND term_id IN ( ' . implode( ',', $terms_to_count ) . ' )';

		// Get the count
		$count = $wpdb->get_var( $count_query . $term_query );

		// Update the count
		update_lifterlms_term_meta( $term_id, 'course_count_' . $taxonomy->name, absint( $count ) );
	}
}

function update_lifterlms_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'lifterlms_term', $term_id, $meta_key, $meta_value, $prev_value );
}

function lifterlms_wp_text_input( $field ) {
	global $thepostid, $post, $lifterlms;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

	switch ( $data_type ) {
		case 'price' :
			$field['class'] .= ' llms_input_price';
			$field['value']  = llms_format_localized_price( $field['value'] );
		break;
		case 'decimal' :
			$field['class'] .= ' llms_input_decimal';
			$field['value']  = llms_format_localized_decimal( $field['value'] );
		break;
	}

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {

			echo '<i class="dashicons dashicons-info help_tip" data-tip="' . esc_attr( $field['description'] ) . '"></i>';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}
	echo '</p>';
}

function get_lifterlms_countries() {
	return array_unique(
		apply_filters( 'lifterlms_countries',
			array(
      'GB' => __( 'United Kingdom', 'lifterlms' ),
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
      'ZW' => __( 'Zimbabwe', 'lifterlms' )
		    )  
		)
	);
}

function get_lifterlms_currency() {
      return apply_filters( 'lifterlms_currency', get_option('lifterlms_currency') );
}

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
				'COP' => __( 'Colombian Peso', 'lifterlms' ),
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


function llms_format_localized_price( $value ) {
	return str_replace( '.', '.', strval( $value ) );
}

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

function llms_clean( $var ) {
	return sanitize_text_field( $var );
}

function llms_get_template_part( $slug, $name = '' ) {
	$template = '';

	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", LLMS()->template_path() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( LLMS()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = LLMS()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", LLMS()->template_path() . "{$slug}.php" ) );
	}

	// Allow 3rd party plugin filter template file from their plugin
	$template = apply_filters( 'llms_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

function llms_get_template_part_contents( $slug, $name = '' ) {
	$template = '';

	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", LLMS()->template_path() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( LLMS()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = LLMS()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", LLMS()->template_path() . "{$slug}.php" ) );
	}

	// Allow 3rd party plugin filter template file from their plugin
	//$template = apply_filters( 'llms_get_template_part', $template, $slug, $name );

	if ( $template ) {
		return $template;
		//load_template( $template, false );
	}
}


function llms_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$located = llms_locate_template( $template_name, $template_path, $default_path );

	do_action( 'lifterlms_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'lifterlms_after_template_part', $template_name, $template_path, $located, $args );
}

function llms_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = LLMS()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = LLMS()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found
	return apply_filters('lifterlms_locate_template', $template, $template_name, $template_path);
}

// debug function
function llms_log($message) {

    if ( WP_DEBUG === true ) {

        if ( is_array($message) || is_object($message ) ) {

            error_log( print_r( $message, true ) );

        } else {

            error_log( $message);
        }
    }
}

function add_query_var_course_id( $vars ){
  $vars[] = "product-id";
  return $vars;
}
add_filter( 'query_vars', 'add_query_var_course_id' );

function add_query_var_product_id( $vars ){
  $vars[] = "product-id";
  return $vars;
}
add_filter( 'query_vars', 'add_query_var_product_id' );


function get_section_id($course_id, $lesson_id) {
	
	$course = new LLMS_Course($course_id);
	$syllabus = $course->get_syllabus();
		$sections = array();
		$section;

		foreach ($syllabus as $key => $value) {

			$sections[$value['section_id']] = $value['lessons'];
			foreach($value['lessons'] as $keys => $values) {
				if ($values['lesson_id'] == $lesson_id) {
					$section = $value['section_id'];
				}
			}
		}
		return $section;
}

function get_update_keys($query){
	$update_key = get_option('lifterlms_update_key', '');
	$url = urlencode(get_bloginfo('url'));
	$query['updatekey'] = $update_key;
	$query['url'] = $url;

	return $query;
}



// $this->app = new LLMSIS_SDK;
    // LLMSIS_log('llmsis_find_overdue_invoices called');

    // // get payment plan items
    // $pp_items = $this->app->get_pay_plan_items();


    // // get payment plan ids from database
    // $args = array(
    //   'post_type'    => 'llms_order',
    //   'posts_per_page' => -1,
    //   'meta_query'     => array(
    //    'key' => '_llms_order_is_payplan_id'
    //    ),
    // );
  
       
    // $posts = get_posts( $args );
     
    // foreach ($posts as $post) {
    //  //do your stuff
    // }
    //get all overdue invoice ids in array
    //get all invoice ids
    //insert invoice ids into option
    //
    //get overdue invoices from databae
    //check every invoice id to an array
    //for every match:
    //find the userid and the product id
    //update the enrollment



add_action( 'wp', 'llms_expire_membership_schedule' );
function llms_expire_membership_schedule() {
    if ( ! wp_next_scheduled('llms_check_for_expired_memberships')) {
      wp_schedule_event( time(), 'daily', 'llms_check_for_expired_memberships' );
    }
  }


add_action( 'llms_check_for_expired_memberships', 'llms_expire_membership' );
add_action('init', 'llms_expire_membership');
function llms_expire_membership() {
  global $wpdb;

  //find all memberships wth an expiration date
  $args = array(
    'post_type'     => 'llms_membership',
    'posts_per_page'  => 500,
    'meta_query'    => array(
      'key' => '_llms_expiration_terms'
      )
  );

  $posts = get_posts( $args );
 
  foreach ($posts as $post) {
    $exp_terms = get_post_meta($post->ID, '_llms_expiration_terms', true);
    $interval = $exp_terms['interval'];
    $period = $exp_terms['period'];

    // query postmeta table and find all users enrolled
    $table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
    $meta_key_status = '_status';
    $meta_value_status = 'Enrolled';

    $results = $wpdb->get_results( $wpdb->prepare(
      'SELECT * FROM '.$table_name.' WHERE post_id = %d AND meta_key = "%s" AND meta_value = %s ORDER BY updated_date DESC', $post->ID, $meta_key_status, $meta_value_status ) );

    for ($i=0; $i < count($results); $i++) {
      $results[$results[$i]->post_id] = $results[$i];
      unset($results[$i]);
    }

    $enrolled_users = $results;

    foreach ( $enrolled_users as $user ) {

      $start_date = $user->updated_date;
      $user_id = $user->user_id;
      $meta_key_start_date = '_start_date';
      $meta_value_start_date = 'yes';

      $start_date = $wpdb->get_results( $wpdb->prepare(
      'SELECT updated_date FROM '.$table_name.' WHERE user_id = %d AND post_id = %d AND meta_key = %s AND meta_value = %s ORDER BY updated_date DESC', $user_id, $post->ID, $meta_key_start_date, $meta_value_start_date) );

      //add expiration terms to start date
      $exp_date = date('Y-m-d',strtotime(date('Y-m-d', strtotime($start_date[0]->updated_date)) . ' +'.$interval. ' ' . $period));

      // get current datetime
      $today = current_time( 'mysql' );
      $today = date("Y-m-d", strtotime($today));

      //compare expiration date to current date.
      if ( $exp_date < $today ) {
        $set_user_expired = array(
          'post_id' => $post->ID,
          'user_id' => $user_id,
          'meta_key' => '_status'
        );

        $status_update = array(
          'meta_value' => 'Expired',
          'updated_date' => current_time( 'mysql' )
        );

        // change enrolled to expired in user_postmeta
        $update_user_meta = $wpdb->update( $table_name, $status_update, $set_user_expired );

        // remove membership id from usermeta array
        $users_levels = get_user_meta( $user_id, '_llms_restricted_levels', true );
        if ( in_array( $post->ID, $users_levels ) ) {
            $key = array_search( $post->ID, $users_levels );
            unset( $users_levels[$key] );
            
            update_user_meta( $user_id, '_llms_restricted_levels', $users_levels );
        }
      }

    }

  }

}
