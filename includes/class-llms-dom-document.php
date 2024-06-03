<?php
/**
 * A convenient wrapper for the DOMDocument Class
 *
 * @package LifterLMS/Classes
 *
 * @since 4.13.0
 * @version 4.13.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_DOM_Document Class
 *
 * @since 4.13.0
 */
class LLMS_DOM_Document {

	/**
	 * Stores the load method name
	 *
	 * @var string
	 */
	private $load_method = 'load_with_mb_convert_encoding';

	/**
	 * Stores the HTML string to load
	 *
	 * @var string
	 */
	private $source;

	/**
	 * Stores the DOMDocument instance
	 *
	 * @var DOMDocument
	 */
	private $dom;

	/**
	 * Stores loading errors
	 *
	 * @var null|WP_Error
	 */
	private $error;

	/**
	 * This forces DOMDocument to convert non-utf8 characters into HTML entities and without relying on `mb_convert_encoding()`.
	 *
	 * @var string
	 */
	private $utf8_fixer = '<meta id="llms-get-dom-doc-utf-fixer" http-equiv="Content-Type" content="text/html; charset=utf-8">';

	/**
	 * Constructor
	 *
	 * @since 4.13.0
	 *
	 * @param string $source An HTML string, either a full HTML document or a partial string.
	 * @return void
	 */
	public function __construct( $source ) {

		if ( ! class_exists( 'DOMDocument' ) ) {
			$this->error = new WP_Error( 'llms-dom-document-missing', __( 'DOMDocument not available.', 'lifterlms' ) );
			return;
		}

		/**
		 * Filters the convert encoding method to be used when loading the source in the DOMDocument
		 *
		 * @param boolean $use_mb_convert_encoding Whether or not the convert encoding method should be used when loading the source in the DOMDocument.
		 *                                         Default is `true`. Requires `mbstring` PHP extension.
		 */
		$use_mb_convert_encoding = apply_filters( 'llms_dom_document_use_mb_convert_encoding', true );
		if ( ! ( $use_mb_convert_encoding && function_exists( 'mb_convert_encoding' ) ) ) {
			$this->load_method = 'load_with_meta_utf_fixer';
		}

		$this->source = $source;
		$this->dom    = new DOMDocument();
	}

	/**
	 * Load the HTML string in the DOMDocument
	 *
	 * This function suppresses PHP warnings that would be thrown by DOMDocument when
	 * loading a partial string or an HTML string with errors.
	 *
	 * @since 4.13.0
	 *
	 * @return boolean|WP_Error Returns `true` if the source is loaded fine.
	 *                          Or an error object when DOMDocument isn't available or an error is encountered during loading.
	 */
	public function load() {

		if ( is_wp_error( $this->error ) && $this->error->has_errors() ) {
			return $this->error;
		}

		// Don't throw or log warnings.
		$libxml_state = libxml_use_internal_errors( true );

		$this->{$this->load_method}();

		// Clear and restore errors.
		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_state );

		return is_wp_error( $this->error ) && $this->error->has_errors() ? $this->error : true;

	}

	/**
	 * Returns the DOMDocument
	 *
	 * @since 4.13.0
	 *
	 * @return DOMDocument Returns an instance of DOMDocument.
	 */
	public function dom() {

		return $this->dom;

	}

	/**
	 * Load the HTML string in the DOMDocument using mb_convert_econding
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	private function load_with_mb_convert_encoding() {
		if ( ! $this->dom->loadHTML( mb_convert_encoding( $this->source, 'HTML-ENTITIES', 'UTF-8' ) ) ) {
			$this->error = new WP_Error( 'llms-dom-document-error', __( 'DOMDocument XML Error encountered.', 'lifterlms' ), libxml_get_errors() );
		}
	}

	/**
	 * Load the HTML string in the DOMDocument using the meta ut8 fixer
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	private function load_with_meta_utf_fixer() {
		if ( ! $this->dom->loadHTML( $this->utf8_fixer . $this->source ) ) {
			$this->error = new WP_Error( 'llms-dom-document-error', __( 'DOMDocument XML Error encountered.', 'lifterlms' ), libxml_get_errors() );
			return;
		}

		// Remove the fixer meta element, if it's not removed it creates invalid HTML5 Markup.
		$meta = $this->dom->getElementById( 'llms-get-dom-doc-utf-fixer' );
		if ( $meta ) {
			$meta->parentNode->removeChild( $meta ); // phpcs:ignore: WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

	}

}
