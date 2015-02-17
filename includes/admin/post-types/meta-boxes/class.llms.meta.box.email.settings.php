<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Certificate Options
* 
* displays email settings metabox. only dislays on email post
*/
class LLMS_Meta_Box_Email_Settings {

	/**
	 * Set up email settings
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$email_subject = get_post_meta( $post->ID, '_email_subject', true );
		$email_heading = get_post_meta( $post->ID, '_email_heading', true );

		$html = '';
		$html .= '<label for="_email_subject">' . __( 'Email Subject', 'lifterlms' ) . '</label> ';
		$html .= '<input type="text" class="code" name="_email_subject" id="_email_subject" value="' . $email_subject . '"/>';
		$html .= '<p>' .  __( 'This will be used for the subject line of your email. The Subject allows mergefields.', 'lifterlms' ) . '</p>';

		$html .= '<label for="_email_heading">' . __( 'Email Heading', 'lifterlms' ) . '</label> ';
		$html .= '<input type="text" class="code" name="_email_heading" id="_email_heading" value="' . $email_heading . '"/>';
		$html .= '<p>' .  __( 'This is the heading for your email. It will display above the content.', 'lifterlms' ) . '</p>';

		echo $html;

		?>
		<br/>
		<p>
		<?php _e( 'Use the text editor above to add content to your email. 
		You can include any of the following merge fields to give the email a personal touch.' ); ?>
		</p>
		<ul>
		<!-- cannot translate merge fields -->
		<li>{site_title}</li>
		<li>{user_login}</li>
		<li>{site_url}</li>
		<li>{first_name}</li>
		<li>{last_name}</li>
		<li>{email_address}</li>
		<li>{current_date}</li>
		</ul>
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

		if ( isset( $_POST['_email_subject'] ) ) {
			//update email subject textbox
			$subject = ( llms_clean( $_POST['_email_subject']  ) );
			update_post_meta( $post_id, '_email_subject', ( $subject === '' ) ? '' : $subject );
			
		}

		if ( isset( $_POST['_email_heading'] ) ) {
			//update heading textbox
			$heading = ( llms_clean( $_POST['_email_heading']  ) );
			update_post_meta( $post_id, '_email_heading', ( $heading === '' ) ? '' : $heading );
			
		}
	}

}