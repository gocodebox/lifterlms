<?php
/**
 * A convenient wrapper for the DOMDocument Class
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_DOM_Document Class
 *
 * @since [version]
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
	private $string;

	/**
	 * Stores the DOMDocument instance
	 *
	 * @var DOMDocument
	 */
	private $dom;

	/**
	 * Stores the libxml errors state
	 *
	 * @var boolean
	 */
	private $libxml_errors_state;

	/**
	 * This forces DOMDocument to convert non-utf8 characters into HTML entities and without relying on `mb_convert_encoding()`.
	 *
	 * @var string
	 */
	private $utf8_fixer = '<meta id="llms-get-dom-doc-utf-fixer" http-equiv="Content-Type" content="text/html; charset=utf-8">';

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @param string $string An HTML string, either a full HTML document or a partial string.
	 * @return void|WP_Error
	 */
	public function __construct( $string ) {

		if ( ! class_exists( 'DOMDocument' ) ) {
			return new WP_Error( 'llms-dom-document-missing', __( 'DOMDocument not available.', 'lifterlms' ) );
		}

		if ( ! apply_filters( 'llms_dom_document_use_mb_convert_encoding', true ) ) {
			$this->$load_method = 'load_with_meta_utf_fixer';
		}

		$this->string = $string;
		$this->dom    = new DOMDocument();
	}

	/**
	 * Load the HTML string in the DOMDocument and returns it
	 *
	 * This function suppresses PHP warnings that would be thrown by DOMDocument when
	 * loading a partial string or an HTML string with errors.
	 *
	 * @since [version]
	 *
	 * @return DOMDocument|WP_Error Returns an instance of DOMDocument with the html passed to the constructor loaded into it
	 *                              or an error object when an error is encountered during loading.
	 */
	public function load() {

		// Don't throw or log warnings.
		$libxml_state = libxml_use_internal_errors( true );

		$this->{$this->load_method}();

		// Clear and restore errors.
		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_state );

		return $this->dom;

	}

	/**
	 * Load the HTML string in the DOMDocument using mb_convert_econding
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function load_with_mb_convert_encoding() {
		if ( ! $this->dom->loadHTML( mb_convert_encoding( $this->string, 'HTML-ENTITIES', 'UTF-8' ) ) ) {
			$this->dom = new WP_Error( 'llms-dom-document-error', __( 'DOMDocument XML Error encountered.', 'lifterlms' ), libxml_get_errors() );
		}
	}

	/**
	 * Load the HTML string in the DOMDocument using the meta ut8 fixer
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function load_with_meta_utf_fixer() {

		if ( ! $this->dom->loadHTML( $this->utf8_fixer . $this->string ) ) {
			$this->dom = new WP_Error( 'llms-dom-document-error', __( 'DOMDocument XML Error encountered.', 'lifterlms' ), libxml_get_errors() );
		}

		if ( is_wp_error( $this->dom ) ) {
			return;
		}

		// Remove the fixer meta element, if it's not removed it creates invalid HTML5 Markup.
		$meta = $this->dom->getElementById( 'llms-get-dom-doc-utf-fixers' );
		if ( $meta ) {
			$meta->parentNode->removeChild( $meta ); // phpcs:ignore: WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

	}

}
