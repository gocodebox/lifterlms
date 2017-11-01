<?php
// restrict direct access
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS REST API
 * @author    LifterLMS
 * @category  API
 * @package   LifterLMS/API
 * @since     [version]
 * @version   [version]
 */
class LLMS_API {

	/**
	 * Constructor
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct() {

		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->includes();

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

	}

	/**
	 * Retrive the URL to the namespaced/versioned rest api
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_url() {
		return get_rest_url( null, 'llms/v1/' );
	}

	/**
	 * Include all rest api files
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function includes() {

		include_once dirname( __FILE__ ) . '/controllers/class-llms-rest-controller-notifications.php';

	}

	/**
	 * Register all our rest api route controllers
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function register_routes() {

		$controllers = array(
			'LLMS_REST_Controller_Notifications',
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}

	}

}
