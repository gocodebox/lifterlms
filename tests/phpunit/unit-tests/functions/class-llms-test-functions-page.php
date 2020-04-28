<?php
/**
 * Test page functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_page
 *
 * @since [version]
 */
class LLMS_Test_Functions_Fage extends LLMS_UnitTestCase {

	/**
	 * Test the llms_confirm_payment_url() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_confirm_payment_url() {

		LLMS_Install::create_pages();

		$base = get_permalink( llms_get_page_id( 'checkout' ) ) . '&confirm-payment';

		// No additional args provided.
		$this->assertEquals( $base, llms_confirm_payment_url() );

		// Has order key.
		$this->assertEquals( $base . '&order=fake', llms_confirm_payment_url( 'fake' ) );

		// Has redirect.
		$this->mockGetRequest( array(
			'redirect' => get_site_url(),
		) );
		$this->assertEquals( $base . '&redirect=' . urlencode( get_site_url() ), llms_confirm_payment_url() );

		// Has both.
		$this->assertEquals( $base . '&order=fake&redirect=' . urlencode( get_site_url() ), llms_confirm_payment_url( 'fake' ) );

	}

}
