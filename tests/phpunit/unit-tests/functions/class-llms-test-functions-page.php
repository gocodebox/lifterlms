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

	/**
	 * Test the llms_get_page_id() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_page_id() {

		$pages = array(
			'checkout' => 'checkout',
			'courses' => 'shop',
			'myaccount' => 'myaccount',
			'memberships' => 'memberships',
		);

		// Clear options maybe installed by other tests.
		foreach ( array_values( $pages ) as $option ) {
			delete_option( 'lifterlms_' . $option . '_page_id' );
		}

		// Options don't exist.

		// Backwards compat.
		$this->assertEquals( -1, llms_get_page_id( 'shop' ) );

		foreach ( array_keys( $pages ) as $slug ) {
			$this->assertEquals( -1, llms_get_page_id( $slug ) );
		}

		// Options do exist.
		LLMS_Install::create_pages();

		// Backwards compat.
		$this->assertEquals( get_option( 'lifterlms_shop_page_id' ), llms_get_page_id( 'shop' ) );

		foreach ( $pages as $slug => $option ) {

			$id = llms_get_page_id( $slug );

			// Number.
			$this->assertTrue( is_int( $id ) );

			// Equals expected option value.
			$this->assertEquals( get_option( 'lifterlms_' . $option . '_page_id' ), $id );

		}

	}

}
