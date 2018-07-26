<?php
/**
 * Allow testing of functions/methods which use `llms_redirect_and_exit()`
 *
 * Use `$this->expectException( LLMS_Testing_Exception_Redirect::class );` before calling the function
 * to test the that the function redirects
 *
 * Test the location & other options with `$this->expectExceptionMessage( "{$url} [{$code}] {$safe}" );`
 * 		where 	$url = the url to redirect to
 * 				$code = int val of the status code (defaults to 302)
 * 				$safe = YES or NO for bool val on the safemode option
 *
 * @since    3.19.4
 * @version  3.19.4
 */
class LLMS_Testing_Exception_Redirect extends LLMS_Testing_Exception_Exit {

	protected $safe = true;

	public function __construct( $location = '', $code = 0, Exception $prev = null, $safe = null ) {

		$this->safe = $safe;
		parent::__construct( $location, $code, $prev );
		$this->message = sprintf( '%1$s [%2$d] %3$s', $this->message, $this->code, $this->getSafe() ? 'YES' : 'NO' );

	}

	public function getSafe() {
		return $this->safe;
	}

}
