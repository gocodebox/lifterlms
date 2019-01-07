<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Privacy Export / Eraser Abstract
 * @thanks   WooCommerce <3
 * @since    3.18.0
 * @version  3.18.0
 */
abstract class LLMS_Abstract_Privacy {

	/**
	 * Plugin name
	 * @var string
	 */
	public $name;

	/**
	 * Registered erasers
	 * @var array
	 */
	protected $erasers = array();

	/**
	 * Registered exporters
	 * @var array
	 */
	protected $exporters = array();

	/**
	 * Constructor
	 * @param    string     $name  plugin name
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function __construct( $name = '' ) {
		$this->name = $name;
		$this->add_hooks();
	}

	/**
	 * Add filters for the registered exporters & erasers
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	protected function add_hooks() {

		add_action( 'admin_init', array( $this, 'add_privacy_message' ) );

		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ), 5 );

	}

	/**
	 * Add privacy message sample content
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function add_privacy_message() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			$content = $this->get_privacy_message();
			if ( $content ) {
				wp_add_privacy_policy_content( $this->name, $this->get_privacy_message() );
			}
		}
	}

	/**
	 * Get the privacy message sample content
	 * This stub can be overloaded
	 * @return   [type]
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function get_privacy_message() {
		return '';
	}

	/**
	 * Retrive an instance of an LLMS_Student from email address
	 * @param    string     $email  Email addres
	 * @return   false|LLMS_Student
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	protected static function get_student_by_email( $email ) {

		$user = get_user_by( 'email', $email );
		if ( is_a( $user, 'WP_User' ) ) {
			return llms_get_student( $user );
		}

		return false;

	}

	/**
	 * Add all registered erasers to the array of existing erasers
	 * @filter   wp_privacy_personal_data_erasers
	 * @param    array      $erasers  existing erasers
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function register_erasers( $erasers = array() ) {
		foreach ( $this->erasers as $id => $eraser ) {
			$erasers[ $id ] = $eraser;
		}
		return $erasers;
	}

	/**
	 * Add all registered erasers to the array of existing exporters
	 * @filter   wp_privacy_personal_data_exporters
	 * @param    array      $exporters  existing exporters
	 * @return   array
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function register_exporters( $exporters = array() ) {
		foreach ( $this->exporters as $id => $exporter ) {
			$exporters[ $id ] = $exporter;
		}
		return $exporters;
	}

	/**
	 * Register an eraser
	 * @param    stirng    $id        Eraser ID
	 * @param    string    $name      Human-readable eraser name
	 * @param    mixed     $callback  Callback function (callable)
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function add_eraser( $id, $name, $callback ) {
		$this->erasers[ $id ] = array(
			'eraser_friendly_name' => $name,
			'callback'             => $callback,
		);
		return $this->erasers;
	}

	/**
	 * Register an exporter
	 * @param    stirng    $id        exporter ID
	 * @param    string    $name      Human-readable exporter name
	 * @param    mixed     $callback  Callback function (callable)
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function add_exporter( $id, $name, $callback ) {
		$this->exporters[ $id ] = array(
			'exporter_friendly_name' => $name,
			'callback'               => $callback,
		);
		return $this->exporters;
	}

}
