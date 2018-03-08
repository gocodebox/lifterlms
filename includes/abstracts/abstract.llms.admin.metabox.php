<?php
/**
* Admin Metabox Class
* @since    3.0.0
* @version  3.16.14
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

// include all classes for each of the metabox types
foreach ( glob( LLMS_PLUGIN_DIR . '/includes/admin/post-types/meta-boxes/fields/*.php' ) as $filename ) {
	require_once $filename;
}

abstract class LLMS_Admin_Metabox {

	/**
	 * Metabox ID
	 * Define this in extending class's $this->configure() method
	 * @var string
	 * @since 3.0.0
	 */
	public $id;

	/**
	 * Post Types this metabox should be added to
	 * Can be a string of a single post type or an indexed array of multiple post types
	 * Define this in extending class's $this->configure() method
	 * @var array
	 * @since 3.0.0
	 */
	public $screens = array();

	/**
	 * Title of the metabox
	 * This should be a translatable, use __()
	 * Define this in extending class's $this->configure() method
	 * @var string
	 * @since 3.0.0
	 */
	public $title;

	/**
	 * Capability to check in order to display the metabox to the user
	 * @var    string
	 * @since  3.13.0
	 */
	public $capability = 'edit_post';

	/**
	 * Optional context to register the metabox with
	 * Accepts anything that can be passed to WP core add_meta_box() function
	 * Options are: 'normal', 'side', 'advanced'
	 * Define this in extending class's $this->configure() method
	 * @var string
	 * @since 3.0.0
	 */
	public $context = 'normal';

	/**
	 * Optional priority for the metabox
	 * Accepts anything that can be passed to WP core add_meta_box() function
	 * Options are: 'default', 'high', 'low'
	 * Define this in extending class's $this->configure() method
	 * @var string
	 * @since 3.0.0
	 */
	public $priority = 'default';

	/**
	 * Instance of WP_Post for the current post
	 * @var obj
	 * @since  3.0.0
	 */
	public $post;

	/**
	 * Meta Key Prefix for all elements in the metabox
	 * @var string
	 * @since 3.0.0
	 */
	public $prefix = '_llms_';

	/**
	 * Array of error message strings to be displayed after an update attempt
	 * @var array
	 * @since 3.0.0
	 */
	private $errors = array();

	/**
	 * HTML for the Metabox Content
	 * Content handled by $this->process_fields()
	 * @var string
	 * @since 3.0.0
	 */
	private $content = '';
	/**
	 * HTML for the Metabox Navigation
	 * Content handled by $this->process_fields()
	 * @var string
	 * @since 3.0.0
	 */
	private $navigation = '';

	/**
	 * The number of tabs registered to the metabox
	 * This will be calculated automatically
	 * Navigation will not display unless there's 2 or more tabs
	 * @var integer
	 * @since  3.0.0
	 */
	private $total_tabs = 0;

	/**
	 * Metabox Version Numbers
	 * @var  integer
	 */
	private $version = 1;

	/**
	 * Constructor
	 *
	 * Configure the metabox and automatically add required actions
	 *
	 * @since  3.0.0
	 */
	public function __construct() {

		// allow child classes to configure variables
		$this->configure();

		// register the metabox
		add_action( 'add_meta_boxes', array( $this, 'register' ) );

		// register save actions for applicable screens (post types)
		foreach ( $this->get_screens() as $screen ) {
			add_action( 'save_post_' . $screen, array( $this, 'save_actions' ), 10, 1 );
		}

		// display errors
		add_action( 'admin_notices', array( $this, 'output_errors' ) );

		// save errors
		add_action( 'shutdown', array( $this, 'save_errors' ) );

	}

	/**
	 * Add an Error Message
	 * @param string $text
	 * @return   void
	 * @since    3.0.0
	 * @version  3.8.0
	 */
	public function add_error( $text ) {
		$this->errors[] = $text;
	}

	/**
	 * This function allows extending classes to configure required class properties
	 * $this->id, $this->title, and $this->screens should be configured in this function
	 *
	 * @return void
	 * @since  3.0.0
	 */
	abstract public function configure();

	/**
	 * This function is where extending classes can configure all the fields within the metabox
	 * The function must return an array which can be consumed by the "output" function
	 *
	 * @return array
	 */
	abstract public function get_fields();

	/**
	 * Normalizes $this->screens to ensure it's an array
	 * @return array
	 * @since  3.0.0
	 */
	private function get_screens() {
		if ( is_string( $this->screens ) ) {
			return array( $this->screens );
		} else {
			return $this->screens;
		}
	}

	/**
	 * Determine if any errors have been added
	 * @return boolean
	 */
	public function has_errors() {
		return ( count( $this->errors ) ) ? true : false;
	}

	/**
	 * Generate and output the HTML for the metabox
	 * @return void
	 * @version  3.0.0
	 */
	public function output() {

		// setup html for nav and content
		$this->process_fields();

		// output the html
		echo '<div class="llms-mb-container">';
		// only show tabbed navigation when there's more than 1 tab
		if ( $this->total_tabs > 1 ) {
			echo '<nav class="llms-nav-tab-wrapper"><ul class="tabs llms-nav-items">' . $this->navigation . '</ul></nav>';
		}
		do_action( 'llms_metabox_before_content', $this->id );
		echo $this->content;
		do_action( 'llms_metabox_after_content', $this->id );
		echo '</div>';
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

	}

	/**
	 * Display the messages as a WP Admin Notice
	 * @return  void
	 * @since  3.0.0
	 */
	public function output_errors() {

		$errors = get_option( 'lifterlms_metabox_errors' . $this->id );

		if ( empty( $errors ) ) {
			return;
		}

		foreach ( $errors as $error ) {
			echo '<div id="lifterlms_errors" class="error"><p>' . $error . '</p></div>';
		}

		delete_option( 'lifterlms_metabox_errors' . $this->id );

	}

	/**
	 * Process fields to setup navigation and content with minimal PHP loops
	 * called by $this->output before actually outputting html
	 * @return   void
	 * @since    3.0.0
	 * @version  3.16.14
	 */
	private function process_fields() {

		/**
		 * Add a filter so extending classes don't have to
		 * so we don't have too many filters running
		 */
		$fields = apply_filters( 'llms_metabox_fields_' . str_replace( '-', '_', $this->id ), $this->get_fields() );

		$this->total_tabs = count( $fields );

		foreach ( $fields as $i => $tab ) {

			$i++;
			$current = 1 === $i ? ' llms-active' : '';

			$this->navigation .= '<li class="llms-nav-item tab-link ' . $current . '" data-tab="' . $this->id . '-tab-' . $i . '"><span class="llms-nav-link">' . $tab['title'] . '</span></li>';

			$this->content .= '<div id="' . $this->id . '-tab-' . $i . '" class="tab-content' . $current . '"><ul>';

			foreach ( $tab['fields'] as $field ) {

				$name = ucfirst( strtr( preg_replace_callback( '/(\w+)/', function( $m ) {
					return ucfirst( $m[1] );
				}, $field['type'] ),'-','_' ) );

				$field_class_name = str_replace( '{TOKEN}', $name, 'LLMS_Metabox_{TOKEN}_Field' );
				$field_class = new $field_class_name($field);
				ob_start();
				$field_class->Output();
				$this->content .= ob_get_clean();
				unset( $field_class );
			}

			$this->content .= '</ul></div>';

		}

	}

	/**
	 * Register the Metabox using WP Functions
	 * This is called automatically by constructor
	 * Utilizes class properties for registration
	 * @return   void
	 * @since    3.0.0
	 * @version  3.13.0
	 */
	public function register() {

		global $post;
		$this->post = $post;

		if ( current_user_can( $this->capability, $this->post->ID ) ) {

			add_meta_box( $this->id, $this->title, array( $this, 'output' ), $this->get_screens(), $this->context, $this->priority );

		}

	}

	/**
	 * Save field data
	 * Loops through fields and saves the data to postmeta
	 * Called by $this->save_actions()
	 *
	 * This function is dumb. If the fields need to output error messages or do validation
	 * Override this method and create a custom save method to accommodate the validations or conditions
	 *
	 * @param    int   $post_id   WP Post ID of the post being saved
	 * @return   void
	 * @since    3.0.0
	 * @version  3.14.1
	 */
	protected function save( $post_id ) {

		// dont save metabox during a quick save action
		if ( isset( $_POST['action'] ) && 'inline-save' === $_POST['action'] ) {
			return;
			// don't save during ajax calls
		} elseif ( llms_is_ajax() ) {
			return;
		}

		// get all defined fields
		$fields = $this->get_fields();

		if ( ! is_array( $fields ) ) {
			return;
		}

		// loop thorugh the fields
		foreach ( $fields as $group => $data ) {

			// find the fields in each tab
			if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {

				// loop through the fields
				foreach ( $data['fields'] as $field ) {

					// don't save things that don't have an ID
					if ( isset( $field['id'] ) ) {

						// get the posted value
						if ( isset( $_POST[ $field['id'] ] ) ) {

							$val = $_POST[ $field['id'] ];

						} // End if().
						elseif ( ! isset( $_POST[ $field['id'] ] ) ) {

							$val = '';

						}

						// update the value if we have one
						if ( isset( $val ) ) {

							update_post_meta( $post_id, $field['id'], $val );

						}

						unset( $val );

					}
				}
			}
		}// End foreach().

	}


	/**
	 * Allows extending classes to perform additional save methods before the default save
	 * when the default save is not being overridden
	 * Called before $this->save() during $this->save_actions()
	 *
	 * @param  int   $post_id   WP Post ID of the post being saved
	 * @return void
	 * @since  3.0.0
	 */
	protected function save_before( $post_id ) {}

	/**
	 * Allows extending classes to perform additional save methods after the default save
	 * when the default save is not being overridden
	 * Called before $this->save() during $this->save_actions()
	 *
	 * @param  int   $post_id   WP Post ID of the post being saved
	 * @return void
	 * @since  3.0.0
	 */
	protected function save_after( $post_id ) {}

	/**
	 * Perform Save Actions
	 * Triggers actions for before and after save
	 * And calls the save method which actually saves metadata
	 *
	 * This is called automatically on save_post_{$post_type} for screens the metabox is registered to
	 * @param  int   $post_id   WP Post ID of the post being saved
	 * @return void
	 * @since  3.0.0
	 */
	public function save_actions( $post_id ) {
		// prevent save action from running multiple times on a single load
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
	 * Save messages to the database
	 * @return  void
	 * @since  3.0.0
	 */
	public function save_errors() {
		update_option( 'lifterlms_metabox_errors' . $this->id, $this->errors );
	}

}
