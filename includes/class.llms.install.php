<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Classes
 * @version     0.1
 */

/**
 * Restrict direct access
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'LLMS_Install' ) ) :

/**
 * LLMS_Install Class
 */

class LLMS_Install {

	protected $min_wp_version = '3.5';
	public $current_wp_version;

	/**
	 * LLMS_Install Constructor.
	 * @access public
	 * @return LLMS_Install
	 */
	public function __construct() {
		$this->current_wp_version = get_bloginfo( 'version' );
		register_activation_hook( LLMS_PLUGIN_FILE, array( $this, 'install' ) );
		add_action( 'admin_init', array( $this, 'check_wp_version' ) );	
		}

	//custom error notice ~ this needs to be moved to it's own class / factory
	public function custom_error_notice(){
     	global $current_screen;
     	if ( $current_screen->parent_base == 'plugins' )
         	echo '<div class="error"><p>Warning - LifterLMS is only compatable with Wordpress version '
         		. $this->min_wp_version . ' or higher. Your current version of Wordpress is ' . $this->current_wp_version  .
         		'. You may experience issues with this plugin until you upgrade your version of Wordpress.</p></div>';
	}

	//testing message only. You can dump shit out in this message. 
	public function custom_dump_notice(){
     	global $current_screen;
     	if ( $current_screen->parent_base == 'plugins' )
         	echo '<div class="error"><p>ok. we got this far. '
         		. $this->min_wp_version . ' or higher. Your current version of Wordpress is ' . $this->current_wp_version  .
         		'. You may experience issues with this plugin until you upgrade your version of Wordpress.</p></div>';
	}

	/**
	 * Check if installed WP version is compatable with plugin requirements. 
	 */
	public function check_wp_version() {
		
		if ( version_compare( get_bloginfo( 'version' ), $this->min_wp_version, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'custom_error_notice' ));
		}

		$this->install();
	}

	/**
	 * Install LLMS
	 */
	public function install() {
		
		$this->create_options();
		$this->create_tables();
		$this->create_roles();
		
		
		// Register Post Types	
		include_once( 'class.llms.post-types.php' );
		LLMS_Post_Types::register_post_types();
		LLMS_Post_Types::register_taxonomies();

		flush_rewrite_rules();
	}

	/**
	 * Create lean page
	 */
	public static function create_pages() {
		$pages = apply_filters( 'lifterlms_create_pages', array(
			'shop' => array(
				'name'    => _x( 'shop', 'Page slug', 'lifterlms' ),
				'title'   => _x( 'shop', 'Page title', 'lifterlms' ),
				'content' => ''
			)
		) );

		foreach ( $pages as $key => $page ) {
			llms_create_page( exc_sql( $page['name'] ), 'lifterlms_' . $key . '_page_id', $page['title'], ! empty( $page['parent'] ) ? llms_get_page_id( $page['parent'] ) : '' );
		}
	}

	public function create_options() {
		//nothing to do yet
	}

	/**
	 * Create liftLMS tables
	 */
	public function create_tables() {

		global $wpdb, $lifterlms;

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty($wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		//lifterLMS Tables
		$lifterlms_tables = "
			CREATE TABLE {$wpdb->prefix}lifterlms_attribute_taxonomies (
			  attribute_id bigint(20) NOT NULL auto_increment,
			  attribute_name varchar(200) NOT NULL,
			  attribute_label longtext NULL,
			  attribute_type varchar(200) NOT NULL,
			  attribute_orderby varchar(200) NOT NULL,
			  PRIMARY KEY  (attribute_id),
			  KEY attribute_name (attribute_name)
			) $collate;
		";

		try
		{
		$conn = dbDelta( $lifterlms_tables );
		}
		catch (Exception $e)
		{
		 throw new Exception( 'Instalation failed. Error creating lifterLMS tables in database.', 0, $e);
		}
		

	}

	public function create_roles() {
		global $wp_roles;

		$methods = $this->get_capabilities();
	}

	public function get_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_lifterlms',
			'view_lifterlms_reports'
			);

		$capability_types = array( 'course', 'section', 'lesson' );

		foreach( $capability_types as $capability_type ) {
			$capability_types[ $capability_type  ] = array(
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms"
			);
		}
		return $capabilities;
	}

}

endif;

return new LLMS_Install();