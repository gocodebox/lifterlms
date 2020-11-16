<?php
/**
 * Certificates
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 4.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Certificates class
 *
 * @see LLMS()->certificates()
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 3.37.3 Refactored `get_export_html()` method.
 *               Added an action `llms_certificate_generate_export` to allow modification of certificate exports before being stored on the server.
 * @since 3.38.1 Use `LLMS_Mime_Type_Extractor::from_file_path()` when retrieving the certificate's imgs mime types during html export.
 * @since 4.3.1 When generating the certificate the to export, if `$this->scrape_certificate()` generates a WP_Error early return it to avoid fatals.
 */
class LLMS_Certificates {

	/**
	 * Instance
	 *
	 * @var LLMS_Certificates
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
	 * Award a certificate to a user.
	 *
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
	 * @since 3.37.3 Added action `llms_certificate_generate_export`.
	 * @since 4.3.1 Introduce `llms_certificate_error` WP_Error code.
	 *
	 * @param string $filepath       Full path for the created file.
	 * @param int    $certificate_id WP_Post ID of the earned certificate.
	 * @return mixed WP_Error or full path to the generated export.
	 */
	private function generate_export( $filepath, $certificate_id ) {

		$html = $this->get_export_html( $certificate_id );

		if ( is_wp_error( $html ) ) {
			return $html;
		}

		/**
		 * Run actions prior to certificate export generation.
		 *
		 * @param string $filepath       Full path where the created file will be stored. Passed as a reference.
		 * @param string $html           Certificate HTML. Passed as a reference.
		 * @param int    $certificate_id WP_Post ID of the earned certificate.
		 */
		do_action_ref_array( 'llms_certificate_generate_export', array( &$filepath, &$html, $certificate_id ) );

		$file = fopen( $filepath, 'w' );
		if ( false === $file ) {
			return new WP_Error( 'llms_certificate_error', __( 'Unable to open export file (HTML certificate) for writing.', 'lifterlms' ) );
		}

		if ( false === fwrite( $file, $html ) ) {
			return new WP_Error( 'llms_certificate_error', __( 'Unable to write to export file (HTML certificate).', 'lifterlms' ) );
		}

		fclose( $file );

		return $filepath;

	}

	/**
	 * Retrieve an existing or generate a downloadable HTML file for a certificate
	 *
	 * @since 3.18.0
	 *
	 * @param int  $certificate_id WP Post ID of the earned certificate.
	 * @param bool $use_cache      If true will check for existence of a cached version of the file first.
	 * @return mixed WP_Error or full path to the generated export.
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

		// Generate the file.
		$filepath = $this->generate_export( $filepath, $certificate_id );

		if ( $use_cache && ! is_wp_error( $filepath ) ) {
			update_post_meta( $certificate_id, '_llms_export_filepath', $filepath );
		}

		return $filepath;

	}

	/**
	 * Retrieves the HTML of a certificate which can be used to create an exportable download
	 *
	 * @since 3.18.0
	 * @since 3.24.3 Unknown.
	 * @since 3.37.3 Refactored method into multiple functions.
	 * @since 4.3.1 If `$this->scrape_certificate()` generates a `WP_Error` early return it.
	 * @since 4.8.0 Remove redundant check for the presence of `DOMDocument`.
	 *
	 * @param int $certificate_id WP_Post ID of the earned certificate.
	 * @return WP_Error|string HTML of the certificate on success, otherwise an error object.
	 */
	private function get_export_html( $certificate_id ) {

		// Retrieve the raw HTML of the page.
		$html = $this->scrape_certificate( $certificate_id );
		if ( is_wp_error( $html ) ) {
			return $html;
		}

		// Modify the DOM.
		$html = $this->modify_dom( $html );

		/**
		 * Modify the HTML of a certificate export.
		 *
		 * @since  3.18.0
		 *
		 * @param string $html           HTML to be exported.
		 * @param int    $certificate_id WP_Post ID of the earned certificate.
		 */
		return apply_filters( 'llms_get_certificate_export_html', $html, $certificate_id );

	}

	/**
	 * Modify the HTML using DOMDocument.
	 *
	 * Preparations include:
	 *
	 *     1. Removing all `script` tags .
	 *     2. Converting all stylesheets into inline `style` tags.
	 *     3. Removes all non stylesheet `link` tags.
	 *     4. Converts `img` tags into data uris.
	 *     5. Adds inline CSS to hide anything hidden in a print view.
	 *     6. Removes the WP Admin Bar.
	 *
	 * @since 3.37.3
	 * @since 3.38.1 Use `LLMS_Mime_Type_Extractor::from_file_path()` in place of `mime_content_type()` to avoid issues with PHP installs that do not support it.
	 * @since 4.8.0 Use `llms_get_dom_document()` in favor of loading `DOMDOcument` directly.
	 *
	 * @param string $html Certificate HTML.
	 * @return string
	 */
	private function modify_dom( $html ) {

		$dom = llms_get_dom_document( $html );
		if ( is_wp_error( $dom ) ) {
			return $html;
		}

		// Don't throw or log warnings.
		$libxml_state = libxml_use_internal_errors( true );

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
			$data    = base64_encode( file_get_contents( $imgpath ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$img->setAttribute( 'src', 'data:' . LLMS_Mime_Type_Extractor::from_file_path( $imgpath ) . ';base64,' . $data );

		}

		// Hide print stuff (this is faster than traversing the dom to remove the element).
		$header = $dom->getElementsByTagName( 'head' )->item( 0 );
		$header->appendChild( $dom->createELement( 'style', '.no-print { display: none !important; }' ) );

		// Remove the admin bar (if found).
		$admin_bar = $dom->getElementById( 'wpadminbar' );
		if ( $admin_bar ) {
			$admin_bar->parentNode->removeChild( $admin_bar ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		$html = $dom->saveHTML();

		// Handle errors.
		libxml_clear_errors();

		// Restore.
		libxml_use_internal_errors( $libxml_state );

		return $html;

	}

	/**
	 * Scrape a LifterLMS Certificate permalink and return the generated HTML.
	 *
	 * @since 3.37.3
	 *
	 * @param int $certificate_id WP_Post ID of the earned certificate (an "llms_my_certificate" post).
	 * @return WP_Error|string WP_Error on failure or the full page HTML on success.
	 */
	private function scrape_certificate( $certificate_id ) {

		// Create a nonce for getting the export HTML.
		$token = wp_generate_password( 32, false );
		update_post_meta( $certificate_id, '_llms_auth_nonce', $token );

		/**
		 * Modify the URL used to scrape the HTML of a certificate in preparation for a certificate export.
		 *
		 * @since 3.18.0
		 *
		 * @param string $url            Certificate permalink with a one-time use authorization token appended as a query string variable.
		 * @param int    $certificate_id WP_Post ID of the earned certificate (an "llms_my_certificate" post).
		 */
		$url = apply_filters(
			'llms_get_certificate_export_html_url',
			add_query_arg(
				'_llms_cert_auth',
				$token,
				get_permalink( $certificate_id )
			),
			$certificate_id
		);

		// Perform the request.
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

		return wp_remote_retrieve_body( $req );

	}

}
