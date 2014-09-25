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