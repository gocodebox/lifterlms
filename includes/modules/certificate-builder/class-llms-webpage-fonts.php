<?php

defined( 'ABSPATH' ) || exit;

/**
 * Handles retreival of active fonts from a webpage.
 *
 * @since    [version]
 * @version  [version]
 */
class LLMS_Webpage_Fonts {

	/**
	 * Webpage URL for retreival.
	 *
	 * @var string $url
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private $url;

	/**
	 * Markup used to extract fonts.
	 *
	 * @var string $html
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private $html;

	/**
	 * DOM of the retreived document.
	 *
	 * @var DOMDocument $dom
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private $dom;

	/**
	 * Webpage URL for retreival.
	 *
	 * @var string $url
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private $font_families;

	/**
	 * Generates a list of font families for use by TinyMCE.
	 *
	 * @return array
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_font_familes_for_tinymce( $url = false ) {

		// retreive the webpage.
		$this->url = ! empty( $url ) ? $url : get_site_url();

		// load the DOM document.
		$document_loaded = $this->load_document();

		// return, if error.
		if ( is_wp_error( $document_loaded ) ) {
			return $document_loaded;
		}

		// get all font-family definitions.
		$font_family_definitions = $this->get_font_family_definitions();

		// replace all quotes, commas and important! from definitions.
		$replacement_array = array( '"', "'", '!important', ',' );

		// loop through each definition.
		foreach ( $font_family_definitions as $font_family_definition ) {

			// initialise match result variable.
			$font_families = array();

			// set label (for tinymce) initially to the whole definition. this takes care of definitions with a single font family.
			$font_family_label = $font_family_definition;

			// break down definitions into an array, if there are multiple.
			explode( '/.*,/iU', $font_family_definition, $font_families );

			// if there are more than one font-family, set the first one as label.
			if ( ! empty( $font_families ) ) {
				$font_family_label = $font_families[0];
			}

			// replace any unnecessary characters from the label
			$font_family_label = trim( str_replace( $replacement_array, '', $font_family_label ) );

			// add to font family array with the label as index.
			$this->font_families[ $font_family_label ] = $font_family;
		}

		return $this->font_families;
	}


	/**
	 * Loads DOM Document from the URL.
	 *
	 * @return WP_Error|bool True when succesful, WP_Error on failure
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private function load_document() {

		// load a URL's html.
		$url_loaded = $this->load_url();

		// error?
		if ( is_wp_error( $url_loaded ) ) {
			return $url_loaded;
		}

		// html is empty for any reason.
		if ( empty( $this->html ) ) {
			return new WP_Error( 'no-html', __( 'No html found in the page', 'lifterlms' ) );
		}

		// DOMDocument instance.
		$this->dom = new DOMDocument;

		// attempt loading the DOM.
		$dom_loaded = $this->dom->loadHTML( $this->html );

		// dom loading failed.
		if ( ! $dom_loaded ) {
			return new WP_Error( 'dom-failed', __( 'Could not load DOM for the page', 'lifterlms' ) );
		}

		//everything worked out fine; document was loaded correctly!
		return true;

	}

	/**
	 * Loads a URL's html.
	 *
	 * @return WP_Error|bool True when succesful, WP_Error on failure
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private function load_url() {

		// fetch the url.
		$req = wp_remote_get( $this->url );

		// error?
		if ( is_wp_error( $req ) ) {
			return $req;
		}

		// get the html.
		$this->html = wp_remote_retrieve_body( $req );

		if ( ! class_exists( 'DOMDocument' ) ) {

			/**
			 * Filters the webpage html used for extracting styles.
			 *
			 * Useful only when DOMDocument is not available.
			 *
			 * @since    [version]
			 * @version  [version]
			 */
			$this->html = apply_filters( 'llms_get_webpage_html_for_styles', $html );

		}

		return true;
	}

	/**
	 * Get styles from linked stylesheets
	 *
	 * @return string
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_link_styles() {

		$raw = array();

		// get all <links>.
		$links = $this->dom->getElementsByTagName( 'link' );

		// inline stylesheets.
		foreach ( $links as $link ) {

			// only proceed for stylesheets.
			if ( 'stylesheet' !== $link->getAttribute( 'rel' ) ) {
				continue;
			}

			// save href for use later.
			$href = $link->getAttribute( 'href' );

			// get the stylesheet's path from its url.
			$stylepath = strtok( str_replace( get_site_url(), untrailingslashit( ABSPATH ), $href ), '?' );

			// get the contents on the stylesheet.
			$raw[] = file_get_contents( $stylepath );

		}

		return $raw;
	}

	/**
	 * Get inline styles
	 *
	 * @return string
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_inline_styles() {

		// get all style tags.
		$inline_styles = $this->dom->getElementsByTagName( 'style' );

		foreach ( $inline_styles as $inline_style ) {
			// @codingStandardsIgnoreLine
			$raw[] = $inline_style->nodeValue;
		}

		return $raw;
	}

	/**
	 * Gets font families from webpage style definitions.
	 *
	 * @return array
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_font_family_definitions() {

		// get style definitions from both inline styles and stylesheets.
		$raw = array_merge( $this->get_link_styles(), $this->get_inline_styles() );

		// implode into a string.
		$raw_css = implode( '', $raw );

		// initialise font definition array.
		$font_family_definitions = array();

		// match against pattern.
		preg_match_all( '/font-family:(.*)[;|\}]/iU', $raw_css, $font_family_definitions );

		// initialise array to return.
		$final_font_family_definitions = array();

		// filter font-families and remove duplicates.
		if ( ! empty( $font_family_definitions ) ) {
			$final_font_family_definitions = array_unique( array_filter( $font_family_definitions[1], array( $this, 'filter_fonts' ) ) );
		}

		return $final_font_family_definitions;
	}

	/**
	 * Filters font family definitions.
	 *
	 * @return bool
	 *
	 * @todo extend from px to other units (em, rem, etc)
	 *
	 * @since    [version]
	 * @version  [version]
	 */
	public function filter_fonts( $font_family_definition ) {

		// initialise variable for preg match result.
		$result = '';

		// make sure to pick up only those with pixel units.
		preg_match( '/\dpx/i', $font_family_definition, $result );

		// no matches.
		if ( ! empty( $result ) ) {
			return false;
		}

		// remove anything that doesn't contain font-families.
		if ( in_array( $font_family_definition, array( 'initial', 'inherit', 'unset' ) ) ) {
			return false;
		}

		return true;
	}

}
