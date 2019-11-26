<?php
/**
 * Certificates
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Certificates class
 *
 * @see LLMS()->certificates()
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 */
class LLMS_Certificates {

	/**
	 * Instance
	 *
	 * @var  LLMS_Certificates
	 */
	protected static $_instance = null;

	/**
	 * Array of Certificate types.
	 *
	 * @var array
	 */
	public $certs = array();

	/**
	 * Instance singleton
	 *
	 * @since 1.0.0
	 *
	 * @return LLMS_Certificates
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize Class
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		include_once 'class.llms.certificate.php';
		$this->certs['LLMS_Certificate_User'] = include_once 'certificates/class.llms.certificate.user.php';
	}

	/**
	 * Award a certificate to a user
	 * Calls trigger method passing arguments
	 *
	 * @since 1.0.0
	 *
	 * @param int $person_id       WP_User ID.
	 * @param int $certificate_id  WP_Post ID of the certificate template.
	 * @param int $related_post_id WP_Post ID of the related post, for example a lesson id.
	 * @return void
	 */
	public function trigger_engagement( $person_id, $certificate_id, $related_post_id ) {
		$certificate = $this->certs['LLMS_Certificate_User'];
		$certificate->trigger( $person_id, $certificate_id, $related_post_id );
	}

	/**
	 * Generate a downloadable HTML file for a certificate
	 *
	 * @since 3.18.0
	 *
	 * @param string $filepath Full path for the created file.
	 * @param int    $certificate_id WP_Post ID of the earned certificate.
	 * @return mixed WP_Error or full path to the generated export.
	 */
	private function generate_export( $filepath, $certificate_id ) {

		$html = $this->get_export_html( $certificate_id );

		if ( is_wp_error( $html ) ) {
			return $html;
		}

		$file = fopen( $filepath, 'w' );
		if ( false === $file ) {
			return new WP_Error( __( 'Unable to open export file (HTML certificate) for writing.', 'lifterlms' ) );
		}

		if ( false === fwrite( $file, $html ) ) {
			return new WP_Error( __( 'Unable to write to export file (HTML certificate).', 'lifterlms' ) );
		}

		fclose( $file );

		return $filepath;

	}

	/**
	 * Retrieve an existing or generate a downloadable HTML file for a certificate
	 *
	 * @since 3.18.0
	 *
	 * @param int $certificate_id WP Post ID of the earned certificate.
	 * @param bool $use_cache If true will check for existence of a cached version of the file first.
	 * @return   mixed WP_Error or full path to the generated export.
	 */
	public function get_export( $certificate_id, $use_cache = false ) {

		if ( $use_cache ) {
			$cached = get_post_meta( $certificate_id, '_llms_export_filepath', true );
			if ( $cached && file_exists( $cached ) ) {
				return $cached;
			}
		}

		$cert = new LLMS_User_Certificate( $certificate_id );

		// Translators: %1$s = url-safe certificate title, %2$s = random alpha-numeric characters for filename obscurity.
		$filename  = sanitize_title( sprintf( esc_attr_x( 'certificate-%1$s-%2$s', 'certificate download filename', 'lifterlms' ), $cert->get( 'certificate_title' ), wp_generate_password( 12, false, false ) ) );
		$filename .= '.html';
		$filepath  = LLMS_TMP_DIR . $filename;

		if ( $use_cache ) {
			update_post_meta( $certificate_id, '_llms_export_filepath', $filepath );
		}

		return $this->generate_export( $filepath, $certificate_id );

	}

	/**
	 * Retrieves the HTML of a certificate which can be used to create an exportable download
	 *
	 * @since 3.18.0
	 * @since 3.24.3 Unknown.
	 *
	 * @param int $certificate_id WP_Post ID of the earned certificate.
	 * @return string
	 */
	private function get_export_html( $certificate_id ) {

		// Create a nonce for getting the export HTML.
		$token = wp_generate_password( 32, false );
		update_post_meta( $certificate_id, '_llms_auth_nonce', $token );

		// Scrape the html from a one-time use URL.
		$url = apply_filters( 'llms_get_certificate_export_html_url', add_query_arg( '_llms_cert_auth', $token, get_permalink( $certificate_id ) ), $certificate_id );
		$req = wp_safe_remote_get(
			$url,
			array(
				'sslverify' => false,
			)
		);

		// Delete the token after the request.
		delete_post_meta( $certificate_id, '_llms_auth_nonce', $token );

		// Error.
		if ( is_wp_error( $req ) ) {
			return $req;
		}

		$html = wp_remote_retrieve_body( $req );

		if ( ! class_exists( 'DOMDocument' ) ) {
			return apply_filters( 'llms_get_certificate_export_html', $html, $certificate_id );
		}

		// Don't throw or log warnings.
		$libxml_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument();

		if ( $dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) ) ) {

			$header = $dom->getElementsByTagName( 'head' )->item( 0 );

			// Remove all <scripts>.
			$scripts = $dom->getElementsByTagName( 'script' );
			while ( $scripts && $scripts->length ) {
				$scripts->item( 0 )->parentNode->removeChild( $scripts->item( 0 ) );
			}

			// Get all <links>.
			$links      = $dom->getElementsByTagName( 'link' );
			$to_replace = array();

			// Inline stylesheets.
			foreach ( $links as $link ) {

				// Only proceed for stylesheets.
				if ( 'stylesheet' !== $link->getAttribute( 'rel' ) ) {
					continue;
				}

				// Save href for use later.
				$href = $link->getAttribute( 'href' );

				/**
				 * Only include local stylesheets.
				 * This means that external fonts (google, for example) are excluded from the download.
				 */
				if ( 0 !== strpos( $href, get_site_url() ) ) {
					continue;
				}

				// Get the actual CSS.
				$stylepath = strtok( str_replace( get_site_url(), untrailingslashit( ABSPATH ), $href ), '?' );
				$raw       = file_get_contents( $stylepath );

				// Add it to be inlined late.
				$tag          = $dom->createElement( 'style', $raw );
				$to_replace[] = array(
					'old' => $link,
					'new' => $tag,
				);

			}

			// Do replacements, ensures cascade order is retained.
			foreach ( $to_replace as $replacement ) {
				$replacement['old']->parentNode->replaceChild( $replacement['new'], $replacement['old'] );
			}

			// Remove all remaining non stylesheet <links>.
			$links = $dom->getElementsByTagName( 'link' );
			while ( $links && $links->length ) {
				$links->item( 0 )->parentNode->removeChild( $links->item( 0 ) );
			}

			// Convert images to data uris.
			$images = $dom->getElementsByTagName( 'img' );
			foreach ( $images as $img ) {

				$src = $img->getAttribute( 'src' );

				// Only include local images.
				if ( 0 !== strpos( $src, get_site_url() ) ) {
					continue;
				}

				$imgpath = strtok( str_replace( get_site_url(), untrailingslashit( ABSPATH ), $src ), '?' );
				$data    = base64_encode( file_get_contents( $imgpath ) );
				$img->setAttribute( 'src', 'data:' . mime_content_type( $imgpath ) . ';base64,' . $data );

			}

			// Hide print stuff (this is faster than traversing the dom to remove the element).
			$header->appendChild( $dom->createELement( 'style', '.no-print { display: none !important; }' ) );

			// Remove the admin bar (if found).
			$admin_bar = $dom->getElementById( 'wpadminbar' );
			if ( $admin_bar ) {
				$admin_bar->parentNode->removeChild( $admin_bar ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar.
			}

			$html = $dom->saveHTML();

		}

		// Handle errors.
		libxml_clear_errors();

		// Restore.
		libxml_use_internal_errors( $libxml_state );

		// Return the html.
		return apply_filters( 'llms_get_certificate_export_html', $html, $certificate_id );

	}

}
