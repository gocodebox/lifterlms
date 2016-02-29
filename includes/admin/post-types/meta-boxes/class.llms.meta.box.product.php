<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Product info.
*
* Fields for managing the Product as a sellable product.
*/
class LLMS_Meta_Box_Product {


	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 * Calls static class metabox_options
	 * Loops through meta-options array and displays appropriate fields based on type.
	 *
	 * @param  object $post [WP post object]
	 *
	 * @return void
	 */
	public static function output( $post ) {

		global $post, $wpdb, $thepostid;

		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$thepostid = $post->ID;

		$sku = get_post_meta( $thepostid, '_sku', true );
		$regular_price = get_post_meta( $thepostid, '_regular_price', true );
		$sale_price = get_post_meta( $thepostid, '_sale_price', true );

		$sale_price_dates_from 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';

		$recurring_enabled  		= get_post_meta( $thepostid, '_llms_recurring_enabled', true );
		$subscription_price 		= get_post_meta( $thepostid, '_llms_subscription_price', true );
		$subscription_first_payment = get_post_meta( $thepostid, '_llms_subscription_first_payment', true );
		$billing_period 			= get_post_meta( $thepostid, '_llms_billing_period', true );
		$billing_freq 				= get_post_meta( $thepostid, '_llms_billing_freq', true );
		$billing_cycle				= get_post_meta( $thepostid, '_llms_billing_cycle', true );



		$billing_periods = array(
			'day' => 'Day',
			'week' => 'Week',
			'month' => 'Month',
			'year' => 'Year',
		);

		?>
		<?php do_action( 'lifterlms_before_product_meta_box' ); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="_sku">SKU</label></th>
					<td>
						<input type="text" name="_sku" id="_sku" value="<?php echo $sku ?>">
						<?php do_action('lifterlms_product_options_sku'); ?>
					</td>
				</tr>
			</tbody>
		</table>

		<h2><?php _e('Single Payment Options', 'lifterlms'); ?></h2>
		<table class="form-table">
		<tbody>
			<tr>
				<th><label for="_regular_price">Regular Price (<?php echo get_lifterlms_currency_symbol(); ?>)</label></th>
				<td>
					<input type="text" name="_regular_price" id="_regular_price" value="<?php echo $regular_price; ?>">
				</td>
			</tr>

			<tr>
				<th><label class="selectit">On Sale</label></th>
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
							do_action( 'lifterlms_product_options_pricing' );
							?>
						</td>
					</tr>
				</table>
			</tr>
		</tbody>
		</table>

		<?php
		//only display recurring options if infusionsoft is not enabled. REFACTOR!!!!
		$infusionsoft_enabled = get_option('lifterlms_gateway_is_enabled', 'no');
		//if ( empty($infusionsoft_enabled) || $infusionsoft_enabled == 'no' ) : ?>

		<h2><?php _e('Recurring Payment Options', 'lifterlms'); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label class="_llms_recurring_enabled">Enable Recurring Payments</label></th>
					<td><input type="checkbox" name="_llms_recurring_enabled" id="_llms_recurring_enabled" <?php if ( ! empty( $recurring_enabled ) ){  echo 'checked="checked"'; } ?> /></td>
				</tr>
				<tr>
					<table id="recurring_options" class="form-table">
						<tr>
							<th><label for="_llms_subscription_price">Subscription Price (<?php echo get_lifterlms_currency_symbol(); ?>)</label></th>
							<td>
								<input type="text" name="_llms_subscription_price" id="_llms_subscription_price" value="<?php echo $subscription_price; ?>">
							</td>
						</tr>

						<tr>
							<th><label for="_llms_subscription_first_payment">Initial Payment (<?php echo get_lifterlms_currency_symbol(); ?>)</label></th>
							<td>
								<input type="text" name="_llms_subscription_first_payment" id="_llms_subscription_first_payment" value="<?php echo $subscription_first_payment ?>">
								<br><span class="description">Initial payment charged on purchase.</span>
							</td>
						</tr>

						<tr>
							<th><label>Billing Period</label></th>
							<td>
								<select id="_llms_billing_period" name="_llms_billing_period">
									<option value="" selected disabled>Select a period...</option>
									<?php foreach ( $billing_periods as $key => $value  ) :
										if ( $key == $billing_period ) {
									?>
										<option value="<?php echo $key; ?>" selected="selected"><?php echo $value; ?></option>

									<?php } else { ?>
										<option value="<?php echo $key; ?>"><?php echo $value; ?></option>

									<?php } ?>
									<?php endforeach; ?>
							 	</select>
							</td>
						</tr>
						<tr>
							<th><label>Billing Cycles</label></th>
							<td>
								<input type="text" name="_llms_billing_cycle" id="_llms_billing_cycle" value="<?php echo $billing_cycle; ?>">
								<br><span class="description">Enter 0 to charge indefinately. IE: 12 would bill for 12 months.</span>
							</td>
						</tr>
						<tr>
							<th><label>Billing Frequency</label></th>
							<td>
								<input type="text" name="_llms_billing_freq" id="_llms_billing_freq" value="<?php echo $billing_freq; ?>">
								<br><span class="description">Frequency of payments. IE if month is set for period and frequency is 2 you will bill every 2 months.</span>
							</td>
						</tr>

					</table>
				</tr>

			</tbody>
		</table>
	<?php //endif; ?>
		<?php do_action( 'lifterlms_after_product_meta_box' ); ?>
<?php
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;
		do_action( 'lifterlms_before_save_product_meta_box', $post_id, $post );

		// Update post meta
		if ( isset( $_POST['_regular_price'] ) )
			update_post_meta( $post_id, '_regular_price', ( $_POST['_regular_price'] === '' ) ? '' : llms_format_decimal( $_POST['_regular_price'] ) );
		if ( isset( $_POST['_sale_price'] ) )
			update_post_meta( $post_id, '_sale_price', ( $_POST['_sale_price'] === '' ? '' : llms_format_decimal( $_POST['_sale_price'] ) ) );


		//Update Sales Price Dates
		$date_from = isset( $_POST['_sale_price_dates_from'] ) && $_POST['_sale_price_dates_from'] ? LLMS_Date::db_date( $_POST['_sale_price_dates_from'] ) : '';
		$date_to = isset( $_POST['_sale_price_dates_to'] ) && $_POST['_sale_price_dates_to'] ? LLMS_Date::db_date($_POST['_sale_price_dates_to'] ) : '';

		// Dates
		update_post_meta( $post_id, '_sale_price_dates_from', $date_from );
		update_post_meta( $post_id, '_sale_price_dates_to', $date_to );

		if ( $date_to && !$date_from ) {
			update_post_meta($post_id, '_sale_price_dates_from', LLMS_Date::db_date(strtotime('NOW', current_time('timestamp'))));
			$date_from = LLMS_Date::db_date(strtotime('NOW', current_time('timestamp')));
		}

		// Update price if on sale
		if (isset( $_POST['_sale_price'] ) ) {
			if ( $_POST['_sale_price'] !== '' && $date_to == '' && $date_from == '' ) {
				update_post_meta($post_id, '_price', llms_format_decimal($_POST['_sale_price']));
			} elseif ( $_POST['_sale_price'] !== '' && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) && $date_to == '' ) {
				update_post_meta($post_id, '_price', llms_format_decimal($_POST['_sale_price']));
			} elseif ( !$date_to || ($date_to && strtotime( $date_to ) > strtotime( 'NOW', current_time( 'timestamp' ) ) ) ) {
				update_post_meta( $post_id, '_price', ( $_POST['_sale_price'] === '' ) ? '' : llms_format_decimal( $_POST['_sale_price'] ) );
			} else {
				update_post_meta($post_id, '_price', ($_POST['_regular_price'] === '') ? '' : llms_format_decimal($_POST['_regular_price']));
			}
		}

		if (isset( $_POST['_on_sale'])) {
			$on_sale = llms_clean($_POST['_on_sale']);
			update_post_meta( $post_id, '_on_sale', $on_sale );
		} else {
			update_post_meta( $post_id, '_on_sale', '' );
		}


		//Update Recurring Payments
		if ( isset($_POST['_llms_recurring_enabled'])
			&& !empty($_POST['_llms_subscription_price'])
			&& isset($_POST['_llms_billing_period'])
			&& !empty($_POST['_llms_billing_freq']))  {

			$recurring_enabled 			= llms_clean( $_POST['_llms_recurring_enabled'] );
			$subscription_price 		= llms_clean( $_POST['_llms_subscription_price'] );
			$subscription_first_payment = (!$_POST['_llms_subscription_first_payment'] == '' ? llms_clean( $_POST['_llms_subscription_first_payment']) : '0' );
			$billing_period 			= llms_clean( $_POST['_llms_billing_period'] );
			$billing_freq 				= llms_clean( $_POST['_llms_billing_freq'] );
			$billing_cycle				= llms_clean( $_POST['_llms_billing_cycle'] );

			update_post_meta( $post_id, '_llms_recurring_enabled', $recurring_enabled );
			update_post_meta( $post_id, '_llms_subscription_price', llms_format_decimal( $subscription_price ) );
			update_post_meta( $post_id, '_llms_subscription_first_payment', llms_format_decimal( $subscription_first_payment ) );
			update_post_meta( $post_id, '_llms_billing_period', $billing_period );
			update_post_meta( $post_id, '_llms_billing_freq', $billing_freq );
			update_post_meta( $post_id, '_llms_billing_cycle', $billing_cycle );

			$llms_subs = array();
			$llms_sub = array();
			$llms_sub['billing_cycle'] = $billing_cycle;
			$llms_sub['billing_freq'] = $billing_freq;
			$llms_sub['billing_period'] = $billing_period;
			$llms_sub['total_price'] = ( ( $subscription_price * $billing_cycle ) + $subscription_first_payment );
			$llms_sub['sub_price'] = $subscription_price;
			$llms_sub['first_payment'] = $subscription_first_payment;

			$llms_subs[] = $llms_sub;
			update_post_meta( $post_id, '_llms_subscriptions', $llms_subs );


		}
		else {
			update_post_meta( $post_id, '_llms_recurring_enabled', '' );
			update_post_meta( $post_id, '_llms_subscription_price', '' );
			update_post_meta( $post_id, '_llms_subscription_first_payment', '' );
			update_post_meta( $post_id, '_llms_billing_period', '' );
			update_post_meta( $post_id, '_llms_billing_freq', '' );
			update_post_meta( $post_id, '_llms_billing_cycle', '' );

			$llms_subs = array();
			update_post_meta( $post_id, '_llms_subscriptions', $llms_subs );
		}


		// Unique SKU
		if ( isset( $_POST['_sku'] ) ) {
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
						    WHERE ($wpdb->posts.post_type = 'course'
						    OR $wpdb->posts.post_type = 'llms_membership')
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
		}

        // custom text for price checkbox
        if (isset($_POST['_is_custom_single_price']) &&
            (isset($_POST['_custom_single_price_html']) && strlen(trim($_POST['_custom_single_price_html'])) > 0))
        {
            $is_custom_single_price = llms_clean($_POST['_is_custom_single_price']);
            $custom_single_price = llms_clean($_POST['_custom_single_price_html']);
            update_post_meta( $post_id, '_is_custom_single_price', $is_custom_single_price );
            update_post_meta( $post_id, '_custom_single_price_html', $custom_single_price );
        }
        else
        {
            update_post_meta( $post_id, '_is_custom_single_price', '' );
            update_post_meta( $post_id, '_custom_single_price_html', '' );
        }

		do_action( 'lifterlms_after_save_product_meta_box', $post_id, $post );
	}
}
