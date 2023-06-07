<?php
/**
 * Allow testing of functions/methods which use `llms_redirect_and_exit()`
 *
 * Use `$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );` before calling the function
 * to test the that the function redirects
 *
 * Test the location & other options with `$this->expectExceptionMessage( "{$url} [{$code}] {$safe}" );`
 * 		where 	$url = the url to redirect to
 * 				$code = int val of the status code (defaults to 302)
 * 				$safe = YES or NO for bool val on the safemode option
 */
class LLMS_Unit_Test_Exception_Redirect extends LLMS_Unit_Test_Exception_Exit {

	protected $safe = true;

	/**
	 * Constructor
	 *
	 * @param string $location URL of the redirect.
	 * @param int $code HTTP Status code.
	 * @param Exception|null $prev The previous exception used for the exception chaining.
	 * @param bool $safe Determines Whether or not the redirect function used is "safe" or regular.
	 */
	public function __construct( $location = '', $code = 0, Exception $prev = null, $safe = false ) {
		$this->safe = $safe;
		parent::__construct( $location, $code, $prev );
		$this->message = sprintf( '%1$s [%2$d] %3$s', $this->message, $this->code, $this->getSafe() ? 'YES' : 'NO' );
	}

	/**
	 * Retrieve the value of the "safe" property.
	 *
	 * @return boolean
	 */
	public function getSafe() {
		return $this->safe;
	}

}
