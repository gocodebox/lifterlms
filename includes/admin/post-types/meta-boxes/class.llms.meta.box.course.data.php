<?php
/**
 * Course Data
 *
 * Displays the course data meta box.
 *
 * @author 		codeBOX
 * @category 	Admin
 * @package 	lifterLMS/Admin/Meta Boxes
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * LLMS_Meta_Box_Course_Data
 */
class LLMS_Meta_Box_Course_Data {

	public static function output( $post ) {
		
		global $post, $wpdb, $thepostid;

		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$thepostid = $post->ID;

		if ( $terms = wp_get_object_terms( $post->ID, 'course_type' ) )
			$course_type = sanitize_title( current( $terms )->name );
		else
			$course_type = apply_filters( 'default_course_type', 'simple' );

		//TO DO This has to go. I don't think I am going to do these
		$course_type_selector = apply_filters( 'course_type_selector', array(
			'simple' 	=> __( 'Simple course', 'lifterlms' ),
		), $course_type );



	    // SKU
		echo '<div>';

		lifterlms_wp_text_input( array( 'id' => '_sku', 'label' => '<abbr title="'. __( 'Stock Keeping Unit', 'lifterlms' ) .'">' . __( 'SKU', 'lifterlms' ) . '</abbr>', 'desc_tip' => 'true', 'description' => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct course and service that can be purchased.', 'lifterlms' ) ) );

		do_action('lifterlms_course_options_sku');

		echo '</div>';

		echo '<div class="options_group pricing show_if_simple show_if_external">';

		// Price
		lifterlms_wp_text_input( array( 'id' => '_regular_price', 'label' => __( 'Regular Price', 'lifterlms' ) . ' (' . get_lifterlms_currency_symbol() . ')', 'data_type' => 'price' ) );

		echo '<div>
		<label class="selectit">
		<input type="checkbox" name="meta-checkbox" id="checkme" value="yes" />
		 Course On Sale</label>
		 <div class="clear"></div>
		</div><div id="extra">';


		// Special Price
		lifterlms_wp_text_input( array( 'id' => '_sale_price', 'data_type' => 'price', 'label' => __( 'Sale Price', 'lifterlms' ) 
			. ' ('.get_lifterlms_currency_symbol().')', 'description' => '' 
			. '<div class="clear"></div>'
			. __( 'Schedule time period the course will be on sale for the price listed above.', 'lifterlms' ) . '' ) );

		// Special Price date range
		$sale_price_dates_from 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		// need to add better date validation. I don't want to hardcode pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01]) into the inputs. 
		echo '	<p class="form-field sale_price_dates_fields">
					<label for="_sale_price_dates_from">' . __( 'Sale Price Dates', 'lifterlms' ) . '</label>
					From<input type="text" class="datepicker short" name="_sale_price_dates_from" id="_sale_price_dates_from" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'lifterlms' ) . ' YYYY-MM-DD" maxlength="10" />
					To<input type="text" class="datepicker short" name="_sale_price_dates_to" id="_sale_price_dates_to" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'lifterlms' ) . '  YYYY-MM-DD" maxlength="10" />
					<a href="#" id="cancel-sale">Cancel Sale</a></p>';

			do_action( 'lifterlms_course_options_pricing' );

		echo '</div></div>';
	}

	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		$course_type  = empty( $_POST['course-type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['course-type'] ) );

		// Update post meta
		if ( isset( $_POST['_regular_price'] ) )
			update_post_meta( $post_id, '_regular_price', ( $_POST['_regular_price'] === '' ) ? '' : llms_format_decimal( $_POST['_regular_price'] ) );
		if ( isset( $_POST['_sale_price'] ) )
			update_post_meta( $post_id, '_sale_price', ( $_POST['_sale_price'] === '' ? '' : llms_format_decimal( $_POST['_sale_price'] ) ) );
		

		//Update Sales Price Dates
		$date_from = isset( $_POST['_sale_price_dates_from'] ) ? $_POST['_sale_price_dates_from'] : '';
			$date_to = isset( $_POST['_sale_price_dates_to'] ) ? $_POST['_sale_price_dates_to'] : '';

			// Dates
			if ( $date_from )
				update_post_meta( $post_id, '_sale_price_dates_from', strtotime( $date_from ) );
			else
				update_post_meta( $post_id, '_sale_price_dates_from', '' );

			if ( $date_to )
				update_post_meta( $post_id, '_sale_price_dates_to', strtotime( $date_to ) );
			else
				update_post_meta( $post_id, '_sale_price_dates_to', '' );

			if ( $date_to && ! $date_from )
				update_post_meta( $post_id, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );

			// Update price if on sale
			if ( $_POST['_sale_price'] !== '' && $date_to == '' && $date_from == '' )
				update_post_meta( $post_id, '_price', llms_format_decimal( $_POST['_sale_price'] ) );
			else
				update_post_meta( $post_id, '_price', ( $_POST['_regular_price'] === '' ) ? '' : llms_format_decimal( $_POST['_regular_price'] ) );

			if ( $_POST['_sale_price'] !== '' && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) )
				update_post_meta( $post_id, '_price', llms_format_decimal( $_POST['_sale_price'] ) );

			if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_price', ( $_POST['_regular_price'] === '' ) ? '' : llms_format_decimal( $_POST['_regular_price'] ) );
				update_post_meta( $post_id, '_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_sale_price_dates_to', '' );
			}


		// Unique SKU
		$sku = get_post_meta( $post_id, '_sku', true );
		$new_sku = llms_clean( stripslashes( $_POST['_sku'] ) );

		if ( $new_sku == '' ) {
			update_post_meta( $post_id, '_sku', '' );
		} elseif ( $new_sku !== $sku ) {
			if ( ! empty( $new_sku ) ) {
				if (
					$wpdb->get_var( $wpdb->prepare("
						SELECT $wpdb->posts.ID
					    FROM $wpdb->posts
					    LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
					    WHERE $wpdb->posts.post_type = 'course'
					    AND $wpdb->posts.post_status = 'publish'
					    AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
					 ", $new_sku ) )
					) {

					LLMS_Admin_Meta_Boxes::add_error( __( 'Course SKU must be unique.', 'lifterlms' ) );

				} else {
					update_post_meta( $post_id, '_sku', $new_sku );
				}
			} else {
				update_post_meta( $post_id, '_sku', '' );
			}
		}
		// Do action for course type
		do_action( 'lifterlms_process_course_meta_' . $course_type, $post_id );
	}
}
