<?php
/**
 * Base for tools listed on the LifterLMS -> Status -> Tools & Utilities screen
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.37.19
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Admin_Tool
 *
 * @since 3.37.19
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
	 * @since 3.37.19
	 *
	 * @return mixed
	 */
	abstract protected function handle();

	/**
	 * Retrieve a description of the tool
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since 3.37.19
	 *
	 * @return string
	 */
	abstract protected function get_description();

	/**
	 * Retrieve the tool's label
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since 3.37.19
	 *
	 * @return string
	 */
	abstract protected function get_label();

	/**
	 * Retrieve the tool's button text
	 *
	 * @since 3.37.19
	 *
	 * @return string
	 */
	abstract protected function get_text();

	/**
	 * Static constructor.
	 *
	 * @since 3.37.19
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'llms_status_tool', array( $this, 'maybe_handle' ) );
		add_filter( 'llms_status_tools', array( $this, 'register' ), $this->priority );

	}

	/**
	 * Processes the tool if the submitted tool matches the tool's ID.
	 *
	 * @since 3.37.19
	 * @since 5.0.0 Add before and after action hooks.
	 *
	 * @param string tool_id ID of the submitted tool.
	 * @return mixed|false
	 */
	public function maybe_handle( $tool_id ) {

		if ( $this->should_load() && $this->id === $tool_id ) {

			/**
			 * Action run prior to running an admin tool's main `handle()` method.
			 *
			 * The dynamic portion of this hook `{$tool_id}` refers to the unique ID
			 * of the admin tool.
			 *
			 * @since 5.0.0
			 *
			 * @param object $tool_class Instance of the extending tool class.
			 */
			do_action( "llms_before_handle_tool_{$tool_id}", $this );

			$handled = $this->handle();

			/**
			 * Action run prior to running an admin tool's main `handle()` method.
			 *
			 * The dynamic portion of this hook `{$tool_id}` refers to the unique ID
			 * of the admin tool.
			 *
			 * @since 5.0.0
			 *
			 * @param object $tool_class Instance of the extending tool class.
			 */
			do_action( "llms_after_handle_tool_{$tool_id}", $this );

			return $handled;

		}

		return false;

	}

	/**
	 * Register the tool.
	 *
	 * @since 3.37.19
	 *
	 * @see llms_status_tools (filter)
	 *
	 * @param array[] $tools Array of tool definitions.
	 * @return array[]
	 */
	public function register( $tools ) {

		if ( ! $this->should_load() ) {
			return $tools;
		}

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
	 * @since 3.37.19
	 *
	 * @return boolean Return `true` to load the tool and `false` to not load it.
	 */
	protected function should_load() {
		return true;
	}

}
