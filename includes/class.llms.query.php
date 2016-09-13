<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Query base class.
*
* Handles queries and endpoints
*/
class LLMS_Query {

	/**
	* Query var
	* @access public
	* @var array
	*/
	public $query_vars = array();

	/**
	 * Constructor for the query class. Hooks in methods.
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'add_endpoints' ) );

		if ( ! is_admin() ) {

			add_filter( 'query_vars', array( $this, 'set_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );

		}

		$this->init_query_vars();
	}

	/**
	 * Init queries
	 *
	 * @return void
	 */
	public function init_query_vars() {

		$this->query_vars = array(
			'confirm-payment' => get_option( 'lifterlms_myaccount_confirm_payment_endpoint', 'confirm-payment' ),
			'lost-password' => get_option( 'lifterlms_myaccount_lost_password_endpoint', 'lost-password' ),
			'person-logout' => get_option( 'lifterlms_logout_endpoint', 'person-logout' ),
		);

	}

	/**
	 * Get query variables
	 *
	 * @return void
	 */
	public function get_query_vars() {

		return apply_filters( 'llms_get_endpoints', $this->query_vars );

	}

	/**
	 * Set query variables
	 *
	 * @return void
	 */
	public function set_query_vars( $vars ) {

		foreach ( $this->get_query_vars() as $key => $var ) {

			$vars[] = $key; }

		return $vars;

	}

	/**
	 * Parse the request for query variables
	 *
	 * @return void
	 */
	public function parse_request() {
		global $wp;

		foreach ( $this->get_query_vars() as $key => $var ) {

			if ( isset( $_GET[ $var ] ) ) {

				$wp->query_vars[ $key ] = $_GET[ $var ];

			} elseif ( isset( $wp->query_vars[ $var ] ) ) {

				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];

			}
		}

	}

	/**
	 * Add query variables
	 * @param $vars [array of WP query variables available for query]
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key; }

		return $vars;
	}

	/**
	 * Add Query Endpoints
	 */
	public function add_endpoints() {
		foreach ( $this->get_query_vars() as $key => $var ) {
			add_rewrite_endpoint( $var, EP_PAGES ); }
	}

}

return new LLMS_Query();
