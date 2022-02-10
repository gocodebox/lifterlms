<?php
/**
 * LifterLMS Privacy Export / Eraser Abstract
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.18.0
 * @version 3.18.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Privacy Export / Eraser abstract class.
 *
 * Thanks WooCommerce.
 *
 * @since 3.18.0
 */
abstract class LLMS_Abstract_Privacy {

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Registered erasers.
	 *
	 * @var array
	 */
	protected $erasers = array();

	/**
	 * Registered exporters.
	 *
	 * @var array
	 */
	protected $exporters = array();

	/**
	 * Constructor.
	 *
	 * @since 3.18.0
	 *
	 * @param string $name Plugin name.
	 * @return void
	 */
	public function __construct( $name = '' ) {

		$this->name = $name;
		$this->add_hooks();
	}

	/**
	 * Add filters for the registered exporters & erasers.
	 *
	 * @since 3.18.0
	 *
	 * @return void
	 */
	protected function add_hooks() {

		add_action( 'admin_init', array( $this, 'add_privacy_message' ) );

		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ), 5 );
	}

	/**
	 * Add privacy message sample content.
	 *
	 * @since 3.18.0
	 *
	 * @return void
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
	 * Get the privacy message sample content.
	 *
	 * This stub can be overloaded.
	 *
	 * @since 3.18.0
	 *
	 * @return string
	 */
	public function get_privacy_message() {

		return '';
	}

	/**
	 * Retrieve an instance of an LLMS_Student from email address.
	 *
	 * @since 3.18.0
	 *
	 * @param string $email Email address.
	 * @return false|LLMS_Student
	 */
	protected static function get_student_by_email( $email ) {

		$user = get_user_by( 'email', $email );
		if ( is_a( $user, 'WP_User' ) ) {
			return llms_get_student( $user );
		}

		return false;
	}

	/**
	 * Add all registered erasers to the array of existing erasers.
	 *
	 * @filter wp_privacy_personal_data_erasers
	 *
	 * @since 3.18.0
	 *
	 * @param array $erasers Existing erasers.
	 * @return array
	 */
	public function register_erasers( $erasers = array() ) {

		foreach ( $this->erasers as $id => $eraser ) {
			$erasers[ $id ] = $eraser;
		}
		return $erasers;
	}

	/**
	 * Add all registered erasers to the array of existing exporters.
	 *
	 * @filter wp_privacy_personal_data_exporters
	 *
	 * @since 3.18.0
	 *
	 * @param array $exporters Existing exporters.
	 * @return array
	 */
	public function register_exporters( $exporters = array() ) {

		foreach ( $this->exporters as $id => $exporter ) {
			$exporters[ $id ] = $exporter;
		}
		return $exporters;
	}

	/**
	 * Register an eraser.
	 *
	 * @since 3.18.0
	 *
	 * @param string $id       Eraser ID.
	 * @param string $name     Human-readable eraser name.
	 * @param mixed  $callback Callback function (callable).
	 * @return array
	 */
	public function add_eraser( $id, $name, $callback ) {

		$this->erasers[ $id ] = array(
			'eraser_friendly_name' => $name,
			'callback'             => $callback,
		);
		return $this->erasers;
	}

	/**
	 * Register an exporter.
	 *
	 * @since 3.18.0
	 *
	 * @param string   $id       Exporter ID.
	 * @param string   $name     Human-readable exporter name.
	 * @param callable $callback Callback function.
	 * @return array
	 */
	public function add_exporter( $id, $name, $callback ) {

		$this->exporters[ $id ] = array(
			'exporter_friendly_name' => $name,
			'callback'               => $callback,
		);
		return $this->exporters;
	}
}
