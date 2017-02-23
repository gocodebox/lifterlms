<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Notification implements LLMS_Interface_Notification {

	public $id = '';

	protected $action = '';
	protected $accepted_args = 1;
	protected $priority = 10;

	// public function callback() {}
	abstract protected function set_handlers();
	abstract protected function set_merge_codes();
	abstract protected function merge_code( $code );

	abstract protected function set_body( $subscriber_id = null, $type = null );
	abstract protected function set_icon( $subscriber_id = null, $type = null );
	abstract protected function set_title( $subscriber_id = null, $type = null );


	private $handlers = array();

	private $subscribers = array();


	public function __construct() {

		add_action( $this->action, array( $this, 'callback' ), $this->priority, $this->accepted_args );

		$this->handlers = $this->get_handlers();
		$this->subscribers = $this->get_subscribers();

	}

	public function handle() {

		foreach ( $this->get_handlers() as $handler ) {

			$obj = LLMS()->notifications()->get_handler( $handler );
			if ( is_subclass_of( $obj, 'LLMS_Notification_Handler' ) ) {
				$obj->handle( $this );
			}

		}

	}


	public function add_subscription( $user_id, $type ) {

		$this->subscribers[ $user_id ][] = $type;

	}

	public function get_subscribers( $type = null ) {

		// query subscriptions to find subscribers



		// add existing subscriptions added at runtime
		$subscribers = $this->subscribers;

		if ( $type ) {

			foreach ( $subscribers as $uid => $types ) {

				if ( ! in_array( $type, $types ) ) {
					unset( $subscribers[ $uid ] );
				}

			}

		}

		return apply_filters( $this->get_filter( 'get_body' ), $subscribers, $this );

	}


	/**
	 * Normalize filter names used for apply_filters()
	 * @param    string     $filter  filter name appended to the default filter prefix
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_filter( $filter ) {
		return 'llms_notification_' . $this->id . '_' . $filter;
	}

	public function get_body( $subscriber_id = null, $type = null ) {

		$body = $this->merge( $this->set_body( $subscriber_id, $type ), $subscriber_id );

		return apply_filters( $this->get_filter( 'get_body' ), $body, $this, $type );

	}
	public function get_icon( $type = null ) {

		return apply_filters( $this->get_filter( 'get_icon' ), $this->set_icon( $type = null ), $this, $type );

	}
	public function get_icon_dimensions( $type = null) {
		return apply_filters( $this->get_filter( 'get_icon_dimensions' ), array( 48, 48 ), $this, $type );
	}
	public function get_title( $subscriber_id = null, $type = null ) {

		$title = $this->merge( $this->set_title( $subscriber_id, $type ), $subscriber_id );

		return apply_filters( $this->get_filter( 'get_title' ), $title, $this, $type );

	}


	private function merge( $content ) {

		$codes = $this->get_merge_codes();

		$raw = array();
		$merged = array();

		foreach ( $codes as $code ) {

			$raw[] = '{{' . $code . '}}';
			$merged[] = $this->merge_code( $code );

		}

		return str_replace( $raw, $merged, $content );

	}

	protected function get_handlers() {
		return apply_filters( $this->get_filter( 'get_handlers' ), $this->set_handlers(), $this );
	}

	protected function get_merge_codes() {
		return apply_filters( $this->get_filter( 'get_merge_codes' ), $this->set_merge_codes(), $this );
	}

}
