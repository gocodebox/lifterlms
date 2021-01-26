<?php
/**
 * Test LLMS_DOM_Document
 *
 * @package LifterLMS/Tests
 *
 * @group llms_dom_document
 *
 * @since 4.13.0
 */
class LLMS_Test_LLMS_DOM_Document extends LLMS_Unit_Test_Case {

	/**
	 * Test DOMDocument library missing
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_dom_document_missing_error() {
		$llms_dom = new LLMS_DOM_Document( 'some string to load' );

		// Simulate that the DOMDocument library is not available.
		LLMS_Unit_Test_Util::set_private_property(
			$llms_dom,
			'error',
			new WP_Error( 'llms-dom-document-missing', __( 'DOMDocument not available.', 'lifterlms' ) )
		);
		$load = $llms_dom->load();

		$this->assertWPError( $load );
		$this->assertWPErrorCodeEquals( 'llms-dom-document-missing', $load );
	}

	/**
	 * Test loading string success
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_loading_success() {

		$llms_dom = new LLMS_DOM_Document( 'some string to load' );
		$this->assertTrue( $llms_dom->load() );
	}

	/**
	 * Test loading method switch
	 *
	 * @since 4.13.0
	 *
	 * @return void
	 */
	public function test_loading_method_switch() {

		// Check that by default the loading method is 'load_with_mb_convert_encoding'.
		$llms_dom = new LLMS_DOM_Document( 'some string to load' );

		$load_method = LLMS_Unit_Test_Util::get_private_property_value(
			$llms_dom,
			'load_method'
		);

		$this->assertEquals( 'load_with_mb_convert_encoding', $load_method );

		// Force using utf fixer.
		add_filter( 'llms_dom_document_use_mb_convert_encoding', '__return_false', 999 );

		$llms_dom = new LLMS_DOM_Document( 'some other string to load' );

		$load_method = LLMS_Unit_Test_Util::get_private_property_value(
			$llms_dom,
			'load_method'
		);

		remove_filter( 'llms_dom_document_use_mb_convert_encoding', '__return_false', 999 );

		$this->assertEquals( 'load_with_meta_utf_fixer', $load_method );

	}

}
