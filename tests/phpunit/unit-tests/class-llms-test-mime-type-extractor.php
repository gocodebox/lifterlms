<?php
/**
 * Test LLMS_Mime_Type_Extractor
 *
 * @package LifterLMS/Tests
 *
 * @group mime_type_extractor
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Mime_Type_Extractor extends LLMS_UnitTestCase {

	/**
	 * Test files.
	 *
	 * @var array
	 */
	protected $files = array(
		'json' => 'import-error.json',
		'jpg'  => 'christian-fregnan-unsplash.jpg',
	);

	/**
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_mime_type_in_list() {

		global $lifterlms_tests;
		$this->assertEquals(
			'image/jpeg',
			LLMS_Mime_Type_Extractor::from_file_path( $lifterlms_tests->assets_dir . $this->files['jpg'] )
		);

	}

	/**
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_mime_type_not_in_list() {

		global $lifterlms_tests;

		// I expect json to be recognized either by one of the fallback functions as 'text/plain'.
		if ( function_exists( 'finfo_file' ) ) {
			$this->assertEquals(
				'text/plain',
				LLMS_Mime_Type_Extractor::from_file_path( $lifterlms_tests->assets_dir . $this->files['json'] )
			);
		} elseif ( function_exists( 'mime_content_type' ) ) {
			$this->assertEquals(
				'application/json',
				LLMS_Mime_Type_Extractor::from_file_path( $lifterlms_tests->assets_dir . $this->files['json'] )
			);
		} else {
			$this->assertEquals(
				LLMS_Mime_Type_Extractor::DEFAULT_MIME_TYPE,
				LLMS_Mime_Type_Extractor::from_file_path( $lifterlms_tests->assets_dir . $this->files['json'] )
			);
		}


	}

	/**
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_mime_type_not_exitent() {

		global $lifterlms_tests;
		$this->assertEquals(
			LLMS_Mime_Type_Extractor::DEFAULT_MIME_TYPE,
			LLMS_Mime_Type_Extractor::from_file_path( $lifterlms_tests->assets_dir . 'SomeoneJoinsSomethingTheyDoNotBelongTo.jpg' )
		);

	}

	/**
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_mime_type_of_a_dir() {

		global $lifterlms_tests;
		$this->assertEquals(
			LLMS_Mime_Type_Extractor::DEFAULT_MIME_TYPE,
			LLMS_Mime_Type_Extractor::from_file_path( $lifterlms_tests->assets_dir )
		);

	}
}
