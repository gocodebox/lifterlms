<?php
/**
 * Base for tools listed on the LifterLMS -> Status -> Tools & Utilities screen
 *
 * @package LifterLMS/Abstracts
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Admin_Tool
 *
 * @since [version]
 */
abstract class LLMS_Abstract_Admin_Tool {

	/**
	 * Tool ID
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Tool Priority
	 *
	 * Passed to the `llms_status_tools` filter when registering the tool.
	 *
	 * @var integer
	 */
	protected $priority = 10;

	/**
	 * Process the tool.
	 *
	 * This method should do whatever the tool actually does.
	 *
	 * By the time this tool is called a nonce and the user's capabilities have already been checked.
	 *
	 * @since [version]
	 *
	 * @return mixed
	 */
	abstract protected function handle();

	/**
	 * Retrieve a description of the tool
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	abstract protected function get_description();

	/**
	 * Retrieve the tool's label
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	abstract protected function get_label();

	/**
	 * Retrieve the tool's button text
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	abstract protected function get_text();

	/**
	 * Static constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		if ( $this->should_load() ) {

			add_filter( 'llms_status_tools', array( $this, 'register' ), $this->priority );
			add_action( 'llms_status_tool', array( $this, 'maybe_handle' ) );

		}

	}

	/**
	 * Processes the tool if the submitted tool matches the tool's ID.
	 *
	 * @since [version]
	 *
	 * @param string tool_id ID of the submitted tool.
	 * @return mixed|false
	 */
	public function maybe_handle( $tool_id ) {

		if ( $this->id === $tool_id ) {
			return $this->handle();
		}

		return false;

	}

	/**
	 * Register the tool.
	 *
	 * @since [version]
	 *
	 * @see llms_status_tools (filter)
	 *
	 * @param array[] $tools Array of tool definitions.
	 * @return array[]
	 */
	public function register( $tools ) {

		$tools[ $this->id ] = array(
			'description' => $this->get_description(),
			'label'       => $this->get_label(),
			'text'        => $this->get_text(),
		);

		return $tools;

	}

	/**
	 * Conditionally load the tool
	 *
	 * This stub can be overridden by the tool to provide custom logic to determine
	 * whether or not the tool should be loaded and registered.
	 *
	 * @since [version]
	 *
	 * @return boolean Return `true` to load the tool and `false` to not load it.
	 */
	protected function should_load() {
		return true;
	}

}
