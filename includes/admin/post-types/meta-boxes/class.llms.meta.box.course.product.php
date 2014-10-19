<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Course Product info. 
*
* Fields for managing the course as a sellable product. 
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Course_Product {

	
	/**
	 * outputs product fields
	 *
	 * @return string (html)
	 * @param string $post
	 */
	public static function output( $post ) {
		
		global $post, $wpdb, $thepostid;

		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$thepostid = $post->ID;

		if ( $terms = wp_get_object_terms( $post->ID, 'course_type' ) )
			$course_type = sanitize_title( current( $terms )->name );
		else
			$course_type = apply_filters( 'default_course_type', 'basic' );

		//TO DO This has to go. I don't think I am going to do these
		$course_type_selector = apply_filters( 'course_type_selector', array(
			'basic' 	=> __( 'Basic course', 'lifterlms' ),
		), $course_type );

		$sku = get_post_meta( $thepostid, '_sku', true );
		$regular_price = get_post_meta( $thepostid, '_regular_price', true );
		$sale_price = get_post_meta( $thepostid, '_sale_price', true );

		$sale_price_dates_from 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';


		?>
		<table class="form-table">
		<tbody>
			<tr>
				<th><label for="_sku">SKU</label></th>
				<td>
					<input type="text" name="_sku" id="_sku" value="<?php echo $sku ?>">
					<?php do_action('lifterlms_course_options_sku'); ?>
				</td>
			</tr>

			<tr>
				<th><label for="_regular_price">Regular Price (<?php echo get_lifterlms_currency_symbol(); ?>)</label></th>
				<td>
					<input type="text" name="_regular_price" id="_regular_price" value="<?php echo $regular_price; ?>">
				</td>
			</tr>

			<tr>
				<th><label class="selectit">Course On Sale</label></th>
				<td><input type="checkbox" name="meta-checkbox" id="checkme" value="yes" /></td>
			</tr>

			<tr>
				<table id="extra" class="form-table">
					<tr>
						<th><label for="_test_price">Sale Price (<?php echo get_lifterlms_currency_symbol(); ?>)</label></th>
						<td>
							<input type="text" name="_sale_price" id="_sale_price" value="<?php echo $sale_price; ?>">
						</td>
					</tr>

					<tr>
						<th><label for="_sale_price_dates_from"><?php  _e( 'Sale Price Dates', 'lifterlms' ) ?></label></th>
						<td>
							<?php
							echo '		
							From <input type="text" class="datepicker short" name="_sale_price_dates_from" id="_sale_price_dates_from" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'lifterlms' ) . ' YYYY-MM-DD" maxlength="10" />
							To <input type="text" class="datepicker short" name="_sale_price_dates_to" id="_sale_price_dates_to" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'lifterlms' ) . '  YYYY-MM-DD" maxlength="10" />
							<a href="#" id="cancel-sale">Cancel Sale</a>';
							do_action( 'lifterlms_course_options_pricing' );
							?>
						</td>
					</tr>
				</table>
			</tr>
		</tbody>
		</table>

<?php
	}

	/**
	 * saves all product metabox data
	 *
	 * @return void
	 * @param $post_id, $post
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;
		LLMS_log($_POST['_sale_price']);

		$course_type  = empty( $_POST['course-type'] ) ? 'basic' : sanitize_title( stripslashes( $_POST['course-type'] ) );

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

					LLMS_Admin_Meta_Boxes::get_error( __( 'The SKU used already exists. Please create a unique SKU.', 'lifterlms' ) );

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
