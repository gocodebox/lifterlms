<?php
defined( 'ABSPATH' ) || exit;

/**
 * Meta Box Voucher Export
 * @since    ??
 * @version  3.22.0
 */
class LLMS_Meta_Box_Voucher_Export {


	public static $prefix = '_';

	public function __construct() {}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 * @param    object  $post  WP global post object
	 * @return   void
	 * @since    ??
	 * @version  3.24.0
	 */
	public static function output( $post ) {

		global $post;
		if ( 'publish' !== $post->post_status ) {
			_e( 'You need to publish this post before you can generate a CSV.', 'lifterlms' );
			return;
		}
		ob_start();
		?>
		<div class="llms-voucher-export-wrapper" id="llms-form-wrapper">

			<div class="llms-voucher-export-type">
				<input type="radio" name="llms_voucher_export_type" id="vouchers_only_type" value="vouchers">
				<label for="vouchers_only_type"><strong><?php _e( 'Vouchers only', 'lifterlms' ); ?></strong></label>
				<p><?php _e( 'Generates a CSV of voucher codes, uses, and remaining uses.', 'lifterlms' ); ?></p>
			</div>

			<div class="llms-voucher-export-type">
				<input type="radio" name="llms_voucher_export_type" id="redeemed_codes_type" value="redeemed">
				<label for="redeemed_codes_type"><strong><?php _e( 'Redeemed codes', 'lifterlms' ); ?></strong></label>
				<p><?php _e( 'Generated a CSV of student emails, redemption date, and used code.', 'lifterlms' ); ?></p>
			</div>


			<div class="llms-voucher-email-wrapper">
				<input type="checkbox" name="llms_voucher_export_send_email" id="llms_voucher_export_send_email"
					   value="true">
				<label for="llms_voucher_export_send_email"><?php _e( 'Email CSV', 'lifterlms' ); ?></label>
				<input type="text" placeholder="Email" name="llms_voucher_export_email">
				<p><?php _e( 'Send to multiple emails by separating emails addresses with commas.', 'lifterlms' ); ?></p>
			</div>

			<button type="submit" name="llms_generate_export" value="generate" class="button-primary"><?php _e( 'Generate Export', 'lifterlms' ); ?></button>
			<?php wp_nonce_field( 'lifterlms_csv_export_data', 'lifterlms_export_nonce' ); ?>
			<div class="clear"></div>
		</div>
		<?php

		echo ob_get_clean();
	}

	public static function export() {

		if ( empty( $_POST['llms_generate_export'] ) || empty( $_POST['lifterlms_export_nonce'] ) || ! wp_verify_nonce( $_POST['lifterlms_export_nonce'], 'lifterlms_csv_export_data' ) ) {
			return false;
		}

		$type = ( isset( $_POST['llms_voucher_export_type'] ) ) ? $_POST['llms_voucher_export_type'] : false;
		if ( isset( $type ) && ! empty( $type ) ) {

			if ( 'vouchers' === $type || 'redeemed' === $type ) {

				// export CSV

				$csv = array();
				$file_name = '';

				global $post;
				$voucher = new LLMS_Voucher( $post->ID );

				switch ( $type ) {
					case 'vouchers':

						$voucher = new LLMS_Voucher( $post->ID );
						$codes = $voucher->get_voucher_codes( 'ARRAY_A' );

						if ( ! $codes ) {
							/**
							 * @todo  error handling here
							 */
							return;
						}

						foreach ( $codes as $k => $v ) {
							unset( $codes[ $k ]['id'] );
							unset( $codes[ $k ]['voucher_id'] );
							$codes[ $k ]['count'] = $codes[ $k ]['redemption_count'];
							$codes[ $k ]['used'] = $codes[ $k ]['used'];
							$codes[ $k ]['created'] = $codes[ $k ]['created_at'];
							$codes[ $k ]['updated'] = $codes[ $k ]['updated_at'];
							unset( $codes[ $k ]['redemption_count'] );
							unset( $codes[ $k ]['created_at'] );
							unset( $codes[ $k ]['updated_at'] );
							unset( $codes[ $k ]['is_deleted'] );

						}
						$csv = self::array_to_csv( $codes );

						$file_name = 'vouchers.csv';
					break;

					case 'redeemed':

						$redeemed_codes = $voucher->get_redeemed_codes( 'ARRAY_A' );

						if ( ! $redeemed_codes ) {
							/**
							 * @todo  error handling here
							 */
							return;
						}

						foreach ( $redeemed_codes as $k => $v ) {
							unset( $redeemed_codes[ $k ]['id'] );
							unset( $redeemed_codes[ $k ]['code_id'] );
							unset( $redeemed_codes[ $k ]['voucher_id'] );
							unset( $redeemed_codes[ $k ]['redemption_count'] );
							unset( $redeemed_codes[ $k ]['user_id'] );

						}

						$csv = self::array_to_csv( $redeemed_codes );

						$file_name = 'redeemed_codes.csv';

					break;
				}// End switch().

				$send_email = isset( $_POST['llms_voucher_export_send_email'] ) ? $_POST['llms_voucher_export_send_email'] : false;

				if ( isset( $send_email ) && ! empty( $send_email ) && true == $send_email ) {

					// send email
					$email_text = trim( $_POST['llms_voucher_export_email'] );
					if ( isset( $email_text ) && ! empty( $email_text ) ) {

						$emails = explode( ',', $email_text );

						if ( ! empty( $emails ) ) {

							$voucher = new LLMS_Voucher( $post->ID );

							self::send_email( $csv, $emails, $voucher->get_voucher_title() );
						}
					}

					return false;
				}

				self::download_csv( $csv, $file_name );
			}// End if().
		}// End if().

	}

	public static function array_to_csv( $data, $delimiter = ',', $enclosure = '"' ) {

		$handle = fopen( 'php://temp', 'r+' );
		$contents = '';

		$names = array();

		foreach ( $data[0] as $name => $item ) {
			$names[] = $name;
		}

		fputcsv( $handle, $names, $delimiter, $enclosure );

		foreach ( $data as $line ) {
			fputcsv( $handle, $line, $delimiter, $enclosure );
		}
		rewind( $handle );
		while ( ! feof( $handle ) ) {
			$contents .= fread( $handle, 8192 );
		}
		fclose( $handle );
		return $contents;
	}

	public static function download_csv( $csv, $name ) {

		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition: attachement; filename="' . $name . '";' );

		echo $csv;
		exit;
	}

	public static function send_email( $csv, $emails ) {

		$subject = 'Your LifterLMS Voucher Export';
		$message = 'Please find the attached voucher csv export for ' . $title . '.';

		// create temp file
		$temp = tempnam( '/tmp', 'vouchers' );

		// write csv
		$handle = fopen( $temp, 'w' );
		fwrite( $handle, $csv );

		// prepare filename
		$temp_data = stream_get_meta_data( $handle );
		$temp_filename = $temp_data['uri'];

		$new_filename = substr_replace( $temp_filename, '', 13 ) . '.csv';
		rename( $temp_filename, $new_filename );

		// send email/s
		$mail = wp_mail( $emails, $subject, $message, '', $new_filename );

		// and remove it
		fclose( $handle );
		unlink( $new_filename );

		return $mail;
	}

}
