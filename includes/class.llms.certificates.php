<?php
/**
 * LLMS_Certificates class file
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 4.21.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main LifterLMS Certificates "factory"
 *
 * Handles certificate generation and exports.
 *
 * @see LLMS()->certificates()
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 3.37.3 Refactored `get_export_html()` method.
 *               Added an action `llms_certificate_generate_export` to allow modification of certificate exports before being stored on the server.
 * @since 3.38.1 Use `LLMS_Mime_Type_Extractor::from_file_path()` when retrieving the certificate's imgs mime types during html export.
 * @since 4.3.1 When generating the certificate the to export, if `$this->scrape_certificate()` generates a WP_Error early return it to avoid fatals.
 * @since 4.21.0 Added new class properties: `$export_local_hosts`, `$export_blocked_stylesheet_hosts`, and `$export_blocked_image_hosts`.
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
	 * Array of local hosts
	 *
	 * @var string[]
	 */
	private $export_local_hosts;

	/**
	 * Array of hosts from which stylesheets won't be retrieved during the export
	 *
	 * @var string[]
	 */
	private $export_blocked_stylesheet_hosts;

	/**
	 * Array of hosts from which images won't be retrieved during the export
	 *
	 * @var string[]
	 */
	private $export_blocked_image_hosts;

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
	 * @since 4.21.0 Define useful class properties used when exporting.
	 *
	 * @return void
	 */
	public function init() {

		include_once 'class.llms.certificate.php';
		$this->certs['LLMS_Certificate_User'] = isset( $this->certs['LLMS_Certificate_User'] ) ? $this->certs['LLMS_Certificate_User'] : include_once 'certificates/class.llms.certificate.user.php';

		$this->export_local_hosts = array_unique(
			array(
				wp_parse_url( get_home_url(), PHP_URL_HOST ),
				wp_parse_url( get_site_url(), PHP_URL_HOST ),
			)
		);

		$this->export_blocked_stylesheet_hosts = array_unique(
			/**
			 * Filters the blocked hosts for stylesheets in certificate exports
			 *
			 * @since 4.21.0
			 *
			 * @param string[] Array of hosts to block.
			 */
			apply_filters(
				'llms_certificate_export_blocked_stylesheet_hosts',
				array(
					'fonts.googleapis.com',
				)
			)
		);

		$this->export_blocked_image_hosts = array_unique(
			/**
			 * Filters the blocked hosts for images in certificate exports
			 *
			 * @since 4.21.0
			 *
			 * @param string[] Array of hosts to block.
			 */
			apply_filters(
				'llms_certificate_export_blocked_image_hosts',
				array()
			)
		);

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
	 *     1. Removing all `script` tags.
	 *     2. Removes the WP Admin Bar.
	 *     3. Converting all stylesheets into inline `style` tags.
	 *     4. Removes all non stylesheet `link` tags.
	 *     5. Converts `img` tags into data uris.
	 *     6. Adds inline CSS to hide anything hidden in a print view.
	 *
	 * @since 3.37.3
	 * @since 3.38.1 Use `LLMS_Mime_Type_Extractor::from_file_path()` in place of `mime_content_type()` to avoid issues with PHP installs that do not support it.
	 * @since 4.8.0 Use `llms_get_dom_document()` in favor of loading `DOMDOcument` directly.
	 * @since 4.21.0 Allow external assets (e.g. images/stylesheets from CDN) to be embedded/inlined.
	 *               Also, remove the WP Admin Bar earlier.
	 *               Move the links and images modification in specific methods.
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

		// Remove the admin bar (if found).
		$admin_bar = $dom->getElementById( 'wpadminbar' );
		if ( $admin_bar ) {
			$admin_bar->parentNode->removeChild( $admin_bar ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		$this->modify_dom_links( $dom );
		$this->modify_dom_images( $dom );

		// Hide print stuff (this is faster than traversing the dom to remove the element).
		$header = $dom->getElementsByTagName( 'head' )->item( 0 );
		$header->appendChild( $dom->createELement( 'style', '.no-print { display: none !important; }' ) );

		$html = $dom->saveHTML();

		// Handle errors.
		libxml_clear_errors();

		// Restore.
		libxml_use_internal_errors( $libxml_state );

		return $html;

	}

	/**
	 * Modify head's <link>s of the DOMDocument.
	 *
	 * @since 4.21.0
	 *
	 * @param DOMDocument $dom The DOMDocument containing the certificate.
	 * @return void
	 */
	private function modify_dom_links( $dom ) {

		// Get all <links>.
		$links      = $dom->getElementsByTagName( 'link' );
		$to_replace = array();

		// Inline stylesheets.
		foreach ( $links as $link ) {

			// Only proceed for stylesheets.
			if ( 'stylesheet' !== $link->getAttribute( 'rel' ) ) {
				continue;
			}

			$raw = $this->get_stylesheet_raw( $link->getAttribute( 'href' ) );

			if ( empty( $raw ) ) {
				continue;
			}

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

	}

	/**
	 * Get stylesheet raw content given its URL
	 *
	 * @since 4.21.0
	 *
	 * @param string  $stylesheet_href The stylesheet href.
	 * @param boolean $allowed_only    Optional. Get only stylesheet whose host is not in the `export_blocked_stylesheet_hosts` list.
	 * @return string|false
	 */
	private function get_stylesheet_raw( $stylesheet_href, $allowed_only = true ) {

		$href_host = wp_parse_url( $stylesheet_href, PHP_URL_HOST );

		// Only include stylesheets from non blocked hosts.
		if ( $allowed_only && in_array( $href_host, $this->export_blocked_stylesheet_hosts, true ) ) {
			return false;
		}

		// Get the actual CSS.
		if ( in_array( $href_host, $this->export_local_hosts, true ) ) { // Is local?
			$raw = file_get_contents( untrailingslashit( ABSPATH ) . wp_parse_url( $stylesheet_href, PHP_URL_PATH ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- getting a local file.
		} else {
			$response = wp_remote_get( $stylesheet_href );
			$raw      = wp_remote_retrieve_body( $response );
		}

		return $raw;

	}

	/**
	 * Modify images of the DOMDocument
	 *
	 * @since 4.21.0
	 *
	 * @param DOMDocument $dom The DOMDocument containing the certificate.
	 * @return void
	 */
	private function modify_dom_images( $dom ) {

		$images    = $dom->getElementsByTagName( 'img' );
		$to_remove = array();

		// Convert images to data uris.
		foreach ( $images as $img ) {

			$img_data_type = $this->get_image_data_and_type( $img->getAttribute( 'src' ) );

			if ( empty( $img_data_type['data'] ) || empty( $img_data_type['type'] ) ) {
				$to_remove[] = $img; // Save images to remove: removing them directly here will alter the collection iteration (skip).
				continue;
			}

			$data = base64_encode( $img_data_type['data'] );// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

			$img->setAttribute( 'src', 'data:' . $img_data_type['type'] . ';base64,' . $data );

			// Remove srcset and sizes attributes.
			$img->removeAttribute( 'sizes' );
			$img->removeAttribute( 'srcset' );
			// Remove useless loading attribute.
			$img->removeAttribute( 'loading' );
		}

		foreach ( $to_remove as $img ) {
			$img->parentNode->removeChild( $img ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

	}

	/**
	 * Get image data and type given its source URL
	 *
	 * @since 4.21.0
	 *
	 * @param string  $image_src    The image src.
	 * @param boolean $allowed_only Optional. Get only images whose host is not in the `export_blocked_image_hosts` list.
	 * @return array|false
	 */
	private function get_image_data_and_type( $image_src, $allowed_only = true ) {

		$src_host = wp_parse_url( $image_src, PHP_URL_HOST );

		// Only include images from non blocked hosts.
		if ( $allowed_only && in_array( $src_host, $this->export_blocked_image_hosts, true ) ) {
			return false;
		}

		if ( in_array( $src_host, $this->export_local_hosts, true ) ) { // Is local?
			$imgpath = untrailingslashit( ABSPATH ) . wp_parse_url( $image_src, PHP_URL_PATH );
			$data    = file_get_contents( $imgpath ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- getting a local file.
			$type    = LLMS_Mime_Type_Extractor::from_file_path( $imgpath );
		} else {
			$response = wp_remote_get( $image_src );
			$data     = wp_remote_retrieve_body( $response );
			$type     = wp_remote_retrieve_header( $response, 'content-type' );
		}

		return compact( 'data', 'type' );

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
