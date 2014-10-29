<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Video
*
* diplays text input for oembed video
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Order {

	/**
	 * Set up video input
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$user_id = get_post_meta( $post->ID, '_llms_user_id', true );
		$payment_method = get_post_meta( $post->ID, '_llms_payment_method', true );
		$product_title = get_post_meta( $post->ID, '_llms_product_title', true );
		$order_total = get_post_meta( $post->ID, '_llms_order_total', true );
		$order_type = get_post_meta( $post->ID, '_llms_order_type', true );
		$order_date = get_post_meta( $post->ID, '_llms_order_date', true );
		$order_type = get_post_meta( $post->ID, '_llms_order_type', true );
		

		$rec_payment = get_post_meta( $post->ID, '_llms_order_recurring_price', true );
		$rec_first_payment = get_post_meta( $post->ID, '_llms_order_first_payment', true );
		$rec_billing_period = get_post_meta( $post->ID, '_llms_order_billing_period', true );
		$rec_billing_freq = get_post_meta( $post->ID, '_llms_order_billing_freq', true );
		$rec_billing_start_date = get_post_meta( $post->ID, '_llms_order_billing_start_date', true );

		$usermeta = get_user_meta($user_id);
		$user = get_user_by('id', $user_id);
		LLMS_log($user);

		if ($usermeta['first_name'][0] != '') {
			$user_name = $usermeta['first_name'][0] . ' ' . $usermeta['last_name'][0];
		}
		else {
			$user_name = $user->user_nicename;
		}
		$user_email = $user->user_email;
		?>

		<table class="form-table">
		<tbody>
			<tr>
				<th>
					<?php
					$label  = '';
					$label .= '<label>' . __( 'General Details', 'lifterlms' ) . '</label> ';
					echo $label;
					?>
				</th>
				<td>
					<table class="form-table">
			
						<tr>
							<td><label>Order Date</label></td>
							<td><?php echo $order_date ?></td>
						</tr>
						<tr>
							<td><label>Payment Method</label></td>
							<td><?php echo $payment_method; ?></td>
						</tr>
						<tr>
							<td><label>Product Title</label></td>
							<td><?php echo $product_title; ?></td>
						</tr>
						<tr>
							<td><label>Purchase Total</label></td>
							<td><?php echo get_lifterlms_currency_symbol() . $order_total; ?></td>
						</tr>
						<tr>
							<td><label>Buyer Name</label></td>
							<td><?php echo $user_name; ?></td>
						</tr>
						<tr>
							<td><label>Buyer Email</label></td>
							<td><a href="mailto:<?php echo $user_email ?>"><?php echo $user_email; ?></td>
						</tr>
						<tr>
							<td><label>Payment Type</label></td>
							<td><?php echo $order_type; ?></td>
						</tr>

						<?php if ($order_type == 'recurring' ) { ?>

						<tr>
							<td><label>First Payment (paid)</label></td>
							<td><?php echo $rec_first_payment ?></td>
						</tr>
						<tr>
							<td><label>Billing Amount</label></td>
							<td><?php echo $rec_payment ?></td>
						</tr>
						<tr>
							<td><label>Billing Period</label></td>
							<td><?php echo $rec_billing_period ?></td>
						</tr>
						<tr>
							<td><label>Billing Frequency</label></td>
							<td><?php echo $rec_billing_freq ?></td>
						</tr>
						<tr>
							<td><label>Billing Start Date</label></td>
							<td><?php echo $rec_billing_start_date ?></td>
						</tr>

						<?php } ?>
	
					</table>
				</td>
			</tr>

		</tbody>
		</table>

		<?php  
	}

	public static function save( $post_id, $post ) {
		global $wpdb;

		if ( isset( $_POST['_video_embed'] ) ) {
			$video = ( llms_clean( $_POST['_video_embed']  ) );
			update_post_meta( $post_id, '_video_embed', ( $video === '' ) ? '' : $video );		
		}
		if ( isset( $_POST['_audio_embed'] ) ) {
			$audio = ( llms_clean( $_POST['_audio_embed']  ) );
			update_post_meta( $post_id, '_audio_embed', ( $audio === '' ) ? '' : $audio );		
		}
	}

}