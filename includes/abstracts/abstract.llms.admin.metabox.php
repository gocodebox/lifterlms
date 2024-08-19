<?php
/**
 * Admin Metabox Abstract.
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.0.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin metabox abstract class.
 *
 * @since 3.0.0
 * @since 3.35.0 Sanitize and verify nonce when saving metabox data.
 * @since 3.36.0 Allow quotes to be saved without being encoded for some special fields that store a shortcode.
 * @since 3.36.1 Improve `save()` method.
 * @since 3.37.12 Simplify `save()` by moving logic to sanitize and update posted data to `save_field()`.
 *                Add field sanitize option "no_encode_quotes" which functions like previous "shortcode" but is more semantically accurate.
 * @since 3.37.19 Bail if the global `$post` is empty, before registering our meta boxes.
 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
 */
abstract class LLMS_Admin_Metabox {

	/**
	 * Metabox ID.
	 *
	 * Define this in extending class's $this->configure() method.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Post Types this metabox should be added to.
	 *
	 * Can be a string of a single post type or an indexed array of multiple post types.
	 * Define this in extending class's $this->configure() method.
	 *
	 * @var array
	 */
	public $screens = array();

	/**
	 * Title of the metabox.
	 *
	 * Define this in extending class's $this->configure() method.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Capability to check in order to display the metabox to the user.
	 *
	 * @var string
	 */
	public $capability = 'edit_post';

	/**
	 * Optional context to register the metabox with.
	 *
	 * Accepts anything that can be passed to WP core add_meta_box() function: 'normal', 'side', 'advanced'.
	 *
	 * Define this in extending class's $this->configure() method.
	 *
	 * @var string
	 */
	public $context = 'normal';

	/**
	 * Optional priority for the metabox.
	 *
	 * Accepts anything that can be passed to WP core add_meta_box() function: 'default', 'high', 'low'.
	 *
	 * Define this in extending class's $this->configure() method.
	 *
	 * @var string
	 */
	public $priority = 'default';

	/**
	 * Array of callback arguments passed to `add_meta_box()`.
	 *
	 * @var null
	 */
	public $callback_args = null;

	/**
	 * Instance of WP_Post for the current post.
	 *
	 * @var WP_Post
	 */
	public $post;

	/**
	 * Meta Key Prefix for all elements in the metabox.
	 *
	 * @var string
	 */
	public $prefix = '_llms_';

	/**
	 * Array of error messages to be displayed after an update attempt.
	 *
	 * @var string[]|WP_Error[]
	 */
	private $errors = array();

	/**
	 * Option keyname where error options are stored.
	 *
	 * @var string
	 */
	protected $error_opt_key = '';

	/**
	 * HTML for the Metabox Content.
	 *
	 * Content handled by $this->process_fields().
	 *
	 * @var string
	 */
	private $content = '';

	/**
	 * HTML for the Metabox Navigation.
	 *
	 * Content handled by $this->process_fields().
	 *
	 * @var string
	 */
	private $navigation = '';

	/**
	 * The number of tabs registered to the metabox.
	 *
	 * This will be calculated automatically.
	 *
	 * Navigation will not display unless there's 2 or more tabs.
	 *
	 * @var integer
	 */
	private $total_tabs = 0;

	/**
	 * Metabox Version Number.
	 *
	 * @var integer
	 */
	private $version = 1;

	/**
	 * Used to prevent save action from running
	 * multiple times on a single load.
	 *
	 * @since 7.5.0
	 * @var bool
	 */
	private $_saved;

	/**
	 * Constructor.
	 *
	 * Configure the metabox and automatically add required actions.
	 *
	 * @since 3.0.0
	 * @since 3.37.12 Use `$this->error_opt_key()` in favor of hardcoded option name.
	 *
	 * @return void
	 */
	public function __construct() {

		// Allow child classes to configure variables.
		$this->configure();

		// Set the error option key.
		$this->error_opt_key = sprintf( 'lifterlms_metabox_errors%s', $this->id );

		// Register the metabox.
		add_action( 'add_meta_boxes', array( $this, 'register' ) );

		// Register save actions for applicable screens (post types).
		foreach ( $this->get_screens() as $screen ) {
			add_action( 'save_post_' . $screen, array( $this, 'save_actions' ), 10, 1 );
		}

		// Display errors.
		add_action( 'admin_notices', array( $this, 'output_errors' ) );

		// Save errors.
		add_action( 'shutdown', array( $this, 'save_errors' ) );
	}

	/**
	 * Add an Error Message.
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Unknown.
	 *
	 * @param string|WP_Error $error Error message text.
	 * @return void
	 */
	public function add_error( $error ) {
		$this->errors[] = $error;
	}

	/**
	 * This function allows extending classes to configure required class properties.
	 *
	 * Properties $id, $title, and $screens should be configured in this function.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	abstract public function configure();

	/**
	 * Retrieve stored metabox errors.
	 *
	 * @since 3.37.12
	 *
	 * @return string[]|WP_Error[]
	 */
	public function get_errors() {
		return get_option( $this->error_opt_key, array() );
	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox.
	 *
	 * The function must return an array which can be consumed by the "output" function.
	 *
	 * @return array
	 */
	abstract public function get_fields();

	/**
	 * Normalizes $this->screens to ensure it's an array.
	 *
	 * @since 3.0.0
	 * @since 3.37.12 Remove unnecessary `else` condition.
	 *
	 * @return array
	 */
	private function get_screens() {
		if ( is_string( $this->screens ) ) {
			return array( $this->screens );
		}
		return $this->screens;
	}

	/**
	 * Determine if any errors have been added to the metabox.
	 *
	 * @since Unknown
	 *
	 * @return boolean
	 */
	public function has_errors() {
		return count( $this->errors ) ? true : false;
	}

	/**
	 * Generate and output the HTML for the metabox.
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function output() {

		// Setup html for nav and content.
		$this->process_fields();

		// output the html.
		echo '<div class="llms-mb-container">';
		// only show tabbed navigation when there's more than 1 tab.
		if ( $this->total_tabs > 1 ) {
			echo '<nav class="llms-nav-tab-wrapper llms-nav-style-tabs"><ul class="tabs llms-nav-items">' . wp_kses_post( $this->navigation ) . '</ul></nav>';
		}
		do_action( 'llms_metabox_before_content', $this->id );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped via process_fields().
		echo $this->content;
		do_action( 'llms_metabox_after_content', $this->id );
		echo '</div>';
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );
	}

	/**
	 * Display the messages as a WP Admin Notice.
	 *
	 * @since 3.0.0
	 * @since 3.37.12 Load errors using `$this->get_errors()` instead of `get_option()`.
	 * @since 6.0.0 Handle WP_Error objects.
	 *
	 * @return void
	 */
	public function output_errors() {

		$errors = $this->get_errors();

		if ( empty( $errors ) ) {
			return;
		}

		foreach ( $errors as $error ) {
			if ( is_wp_error( $error ) ) {
				$error = $error->get_error_message();
			}
			echo '<div id="lifterlms_errors" class="error"><p>' . wp_kses_post( $error ) . '</p></div>';
		}

		delete_option( $this->error_opt_key );
	}

	/**
	 * Process fields to setup navigation and content with minimal PHP loops.
	 *
	 * Called by `$this->output()` before actually outputting html.
	 *
	 * @since 3.0.0
	 * @since 3.16.14 Unknown.
	 * @since 6.0.0 Move single field processing logic to a specific method {@see LLMS_Admin_Metabox::process_field()}.
	 *
	 * @return void
	 */
	private function process_fields() {

		// Create a filter-safe ID that conforms to WordPress coding standards for hooks.
		$id = str_replace( '-', '_', $this->id );

		/**
		 * Customize metabox fields prior to field processing.
		 *
		 * The dynamic portion of this filter, `$id`, corresponds to the classes `$id` property with
		 * dashes (`-`) replaced with underscores (`_`). If the class id is "my-metabox" the filter would be
		 * "llms_metabox_fields_my_metabox".
		 *
		 * @since Unknown
		 *
		 * @param array $fields Array of metabox fields.
		 */
		$fields = apply_filters( "llms_metabox_fields_{$id}", $this->get_fields() );

		$this->total_tabs = count( $fields );

		foreach ( $fields as $i => $tab ) {

			++$i;
			$current = 1 === $i ? ' llms-active' : '';

			$this->navigation .= '<li class="llms-nav-item tab-link ' . esc_attr( $current ) . '" data-tab="' . $this->id . '-tab-' . esc_attr( $i ) . '"><span class="llms-nav-link">' . wp_kses_post( $tab['title'] ) . '</span></li>';

			$this->content .= '<div id="' . $this->id . '-tab-' . $i . '" class="tab-content' . esc_attr( $current ) . '"><ul>';

			foreach ( $tab['fields'] as $field ) {
				$this->content .= $this->process_field( $field );
			}

			$this->content .= '</ul></div>';

		}
	}

	/**
	 * Process single field.
	 *
	 * @since 6.0.0
	 *
	 * @param array $field Metabox field.
	 * @return string
	 */
	protected function process_field( $field ) {

		$name = ucfirst(
			strtr(
				preg_replace_callback(
					'/(\w+)/',
					function ( $m ) {
						return ucfirst( $m[1] );
					},
					$field['type']
				),
				'-',
				'_'
			)
		);

		$field_class_name = str_replace( '{TOKEN}', $name, 'LLMS_Metabox_{TOKEN}_Field' );
		$field_class      = new $field_class_name( $field );
		ob_start();
		$field_class->Output();
		$field_html = ob_get_clean();
		unset( $field_class );

		return $field_html;
	}

	/**
	 * Register the Metabox using WP Functions.
	 *
	 * This is called automatically by constructor.
	 *
	 * Utilizes class properties for registration.
	 *
	 * @since 3.0.0
	 * @since 3.13.0 Unknown.
	 * @since 3.37.19 Early bail if the global `$post` is empty.
	 * @since 6.0.0 Pass callback arguments to `add_meta_box()`.
	 *
	 * @return void
	 */
	public function register() {

		global $post;

		if ( empty( $post ) ) {
			return;
		}

		$this->post = $post;

		if ( current_user_can( $this->capability, $this->post->ID ) ) {

			add_meta_box(
				$this->id,
				$this->title,
				array( $this, 'output' ),
				$this->get_screens(),
				$this->context,
				$this->priority,
				is_callable( $this->callback_args ) ? ( $this->callback_args )() : $this->callback_args
			);

		}
	}

	/**
	 * Save field data.
	 *
	 * Loops through fields and saves the data to postmeta.
	 *
	 * Called by $this->save_actions().
	 *
	 * This function is dumb. If the fields need to output error messages or do validation override
	 * this method and create a custom save method to accommodate the validations or conditions.
	 *
	 * @since 3.0.0
	 * @since 3.14.1 Unknown.
	 * @since 3.35.0 Added nonce verification before processing data; only access `$_POST` data via `llms_filter_input()`.
	 * @since 3.36.0 Allow quotes when sanitizing some special fields that store a shortcode.
	 * @since 3.36.1 Check metabox capability during saves.
	 *               Return an `int` depending on return condition.
	 *               Automatically add `FILTER_REQUIRE_ARRAY` flag when sanitizing a `multi` field.
	 * @since 3.37.12 Move field sanitization and updates to the `save_field()` method.
	 * @since 6.0.0 Allow skipping the saving of a field.
	 *
	 * @param int $post_id WP Post ID of the post being saved.
	 * @return int `-1` When no user or user is missing required capabilities or when there's no or invalid nonce.
	 *             `0` during inline saves or ajax requests or when no fields are found for the metabox.
	 *             `1` if fields were found. This doesn't mean there weren't errors during saving.
	 */
	protected function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) || ! current_user_can( $this->capability, $post_id ) ) {
			return -1;
		}

		// Return early during quick saves and ajax requests.
		if ( ( isset( $_POST['action'] ) && 'inline-save' === $_POST['action'] ) || llms_is_ajax() ) {
			return 0;
		}

		// Get all defined fields.
		$fields = $this->get_fields();

		if ( ! is_array( $fields ) ) {
			return 0;
		}

		// Loop through the fields.
		foreach ( $fields as $group => $data ) {

			// Find the fields in each tab.
			if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {

				// Loop through the fields.
				foreach ( $data['fields'] as $field ) {
					// Don't save things that don't have an ID or that are set to be skipped.
					if ( isset( $field['id'] ) && empty( $field['skip_save'] ) ) {
						$this->save_field( $post_id, $field );
					}
				}
			}
		}

		return 1;
	}

	/**
	 * Save a metabox field.
	 *
	 * @since 3.37.12
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 6.0.0 Move the DB saving in another method.
	 *
	 * @param int   $post_id WP_Post ID.
	 * @param array $field   Metabox field array.
	 * @return boolean
	 */
	protected function save_field( $post_id, $field ) {

		$val = '';

		// Get the posted value & sanitize it.
		if ( isset( $_POST[ $field['id'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in `$this->save()` which calls this method.

			$flags = array();

			if ( isset( $field['sanitize'] ) && in_array( $field['sanitize'], array( 'shortcode', 'no_encode_quotes' ), true ) ) {
				$flags[] = FILTER_FLAG_NO_ENCODE_QUOTES;
			} elseif ( ! empty( $field['multi'] ) ) {
				$flags[] = FILTER_REQUIRE_ARRAY;
			}

			$val = llms_filter_input_sanitize_string( INPUT_POST, $field['id'], $flags );

		}

		return $this->save_field_db( $post_id, $field['id'], $val );
	}

	/**
	 * Save field in the db.
	 *
	 * Expects an already sanitized value.
	 *
	 * @param int   $post_id  The WP Post ID.
	 * @param int   $field_id The field identifier.
	 * @param mixed $val      Value to save.
	 * @return bool
	 */
	protected function save_field_db( $post_id, $field_id, $val ) {
		return update_post_meta( $post_id, $field_id, $val ) ? true : false;
	}

	/**
	 * Allows extending classes to perform additional save methods before the default save.
	 *
	 * Called before `$this->save()` during `$this->save_actions()`.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id WP Post ID of the post being saved.
	 * @return void
	 */
	protected function save_before( $post_id ) {}

	/**
	 * Allows extending classes to perform additional save methods after the default save.
	 *
	 * Called after `$this->save()` during `$this->save_actions()`.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id WP Post ID of the post being saved.
	 * @return void
	 */
	protected function save_after( $post_id ) {}

	/**
	 * Perform Save Actions.
	 *
	 * Triggers actions for before and after save and calls the save method which actually saves metadata.
	 *
	 * This is called automatically on save_post_{$post_type} for all screens defined in `$this->screens`.
	 *
	 * @since 3.0.0
	 *
	 * @param int $post_id WP Post ID of the post being saved.
	 * @return void
	 */
	public function save_actions( $post_id ) {

		// Prevent save action from running multiple times on a single load.
		if ( isset( $this->_saved ) ) {
			return;
		}

		$this->post = get_post( $post_id );

		$this->_saved = true;
		do_action( 'llms_metabox_before_save_' . $this->id, $post_id, $this );
		$this->save_before( $post_id );
		$this->save( $post_id );
		$this->save_after( $post_id );
		do_action( 'llms_metabox_after_save_' . $this->id, $post_id, $this );
	}

	/**
	 * Save messages to the database.
	 *
	 * @since 3.0.0
	 * @since 3.37.12 Use `$this->error_opt_key()` in favor of hardcoded option name.
	 *                Only save errors if errors have been added.
	 *
	 * @return void
	 */
	public function save_errors() {
		if ( $this->has_errors() ) {
			update_option( $this->error_opt_key, $this->errors );
		}
	}
}
