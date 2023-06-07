<?php
/**
 * Mock Request methods
 * @since    1.3.0
 * @version  1.3.0
 */
trait LLMS_Unit_Test_Mock_Requests {

	/**
	 * Setup Get data to mock post and request data
	 *
	 * @param    array      $vars  mock get data
	 * @return   void
	 * @since    1.0.0
	 * @version  1.3.0
	 */
	protected function mockGetRequest( $vars = array() ) {
		$this->mockRequest( 'GET', $vars );
	}

	/**
	 * Setup Post data to mock post and request data
	 *
	 * @param    array      $vars  mock post data
	 * @return   void
	 * @since    1.0.0
	 * @version  1.3.0
	 */
	protected function mockPostRequest( $vars = array() ) {
		$this->mockRequest( 'POST', $vars );
	}

	/**
	 * Setup reuqest data to mock post/get and request data
	 *
	 * @param    array      $vars  mock request data
	 * @return   void
	 * @since    1.0.0
	 * @version  1.3.0
	 */
	private function mockRequest( $method, $vars = array() ) {
		putenv( 'REQUEST_METHOD=' . $method );
		if ( 'POST' === $method ) {
			$_POST = $vars;
		} elseif ( 'GET' === $method ) {
			$_GET = $vars;
		}
		$_REQUEST = $vars;
	}

}
