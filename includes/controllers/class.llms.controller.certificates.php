<?php
defined( 'ABSPATH' ) || exit;

/**
 * User Account Edit Forms
 *
 * @since   3.7.0
 * @version 3.17.8
 */
class LLMS_Controller_Certificates {

	public function __construct() {

		add_action( 'init', array( $this, 'maybe_generate_pdf' ) );
		add_action( 'wp', array( $this, 'maybe_authenticate_pdf_generation' ) );

	}

	public function maybe_authenticate_pdf_generation() {

		if ( empty( $_REQUEST['_llms_cert_auth'] ) ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! in_array( get_post_type( $post_id ), array( 'llms_my_certificate', 'llms_certificate' ) ) ) {
			return;
		}

		if ( $_REQUEST['_llms_cert_auth'] !== get_post_meta( $post_id, '_llms_auth_nonce', true ) ) {
			return;
		}

		$cert = new LLMS_User_Certificate( $post_id );
		wp_set_current_user( $cert->get_user_id() );

	}


	public function maybe_generate_pdf() {

		if ( ! llms_verify_nonce( '_llms_gen_cert_nonce', 'llms-generate-cert' ) ) {
			return;
		}
		$this->generate_pdf( $_POST['certificate_id'] );

	}

	public function replace_images( $html ) {

	}

	public function generate_pdf( $certificate_id ) {

		$token = wp_generate_password( 32, false );
		update_post_meta( $certificate_id, '_llms_auth_nonce', $token );

		$url = add_query_arg( '_llms_cert_auth', $token, get_permalink( $certificate_id ) );
		// llms_log( $url );
		$req = wp_safe_remote_get( $url, array(
			'sslverify' => false,
		) );

		delete_post_meta( $certificate_id, '_llms_auth_nonce', $token );

		if ( is_wp_error( $req ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $req );

		$mpdf = new \Mpdf\Mpdf( array(
			// 'debug' => true,
			'format' => 'A4-L',
		) );

		$mpdf->SetBasePath( ABSPATH );

		$body = str_replace( get_site_url(), '.', $body );

		// llms_log( $body );

		// preg_match_all( '/(?<=src=\")([^\"])+(png|jpg|jpeg|gif)/i', $body, $matches );
		// $replace = array();
		// $i = 1;
		// foreach ( $matches[0] as $match ) {
		// 	$id = 'img' . $i;
		// 	$mpdf->imageVars[ $id ] = file_get_contents( str_replace( get_site_url(), '.', $match ), true );
		// 	$body = str_replace( $match, 'var:' . $id, $body );
		// 	$i++;
		// }
		$mpdf->showImageErrors = true;
		$mpdf->WriteHTML( $body );
		// $mpdf->Output( LLMS_TMP_DIR . 'cert.pdf', \Mpdf\Output\Destination::FILE );
		$mpdf->Output( 'cert.pdf', \Mpdf\Output\Destination::DOWNLOAD );

	}

}

return new LLMS_Controller_Certificates();
