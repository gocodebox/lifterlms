<?php
defined( 'ABSPATH' ) || exit;

class LLMS_AddOn_Upgrader {

	protected static $_instance = null;

	/**
	 * Main Instance of LifterLMS
	 * Ensures only one instance of LifterLMS is loaded or can be loaded.
	 * @return   LLMS_AddOn_Upgrader - Main instance
	 * @since    [version]
	 * @version  [version]
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {

		require_once 'functions-llms-addon-upgrader-ajax.php';

		add_action( 'llms_check_license_keys', array( $this, 'check_keys' ) );

		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
		// add_filter( 'plugins_api_result', function( $res ) {
		// 	llms_log( $res );
		// } );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_plugins_filter' ) );

	}

	/**
	 * Check all saved keys to ensure they're still active
	 * Outputs warnings if the key has expired or the status has changed remotely
	 * Runs on daily cron (`llms_check_license_keys`)
	 * only make api calls to check once / week
	 * @param    bool       $force  ignore the once/week setting and force a check
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function check_keys( $force = false ) {

		// don't trigger during AJAX Requests
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// don't proceed if we don't have any keys to check
		$keys = $this->get_license_keys();
		if ( ! $keys ) {
			return;
		}

		if ( ! $force ) {
			// only check keys once a week
			$last_send = $this->get_option( 'last_check_time', 0 );
			if ( $last_send > apply_filters( 'llms_check_license_keys_interval', strtotime( '-1 week' ) ) ) {
				return;
			}
		}

		// record check time
		$this->set_option( 'last_check_time', time() );

		$data = array(
			'keys' => array(),
			'url' => get_site_url(),
		);

		foreach ( $keys as $key ) {
			$data['keys'][ $key['license_key'] ] = $key['update_key'];
		}

		$req = new LLMS_Dot_Com_API( '/license/status', $data );
		if ( ! $req->is_error() ) {

			$res = $req->get_result();
			include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

			/* Translators: %s = License Key */
			$msg = __( 'The license "%s" is no longer valid and was deactivated. Please visit your account dashboard at https://lifterlms.com/my-account for more information.', 'lifterlms' );

			// output error responses
			if ( isset( $res['data']['errors'] ) ) {
				foreach ( array_keys( $res['data']['errors'] ) as $key ) {
					$this->remove_license_key( $key );
					LLMS_Admin_Notices::add_notice( 'key_check_' . sanitize_text_field( $key ), make_clickable( sprintf( $msg, $key ) ), array(
						'type' => 'error',
						'dismiss_for_days' => 0,
					) );
				}
			}

			// check status of keys, if the status has changed remove it locally
			if ( isset( $res['data']['keys'] ) ) {
				foreach ( $res['data']['keys'] as $key => $data ) {

					if ( $data['status'] ) {
						continue;
					}

					$this->remove_license_key( $key );
					LLMS_Admin_Notices::add_notice( 'key_check_' . sanitize_text_field( $key ), make_clickable( sprintf( $msg, $key ) ), array(
						'type' => 'error',
						'dismiss_for_days' => 0,
					) );

				}
			}

		}

	}

	/**
	 * Retrieve all upgrader options array
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_options() {
		return get_option( 'llms_addon_upgrader', array() );
	}

	/**
	 * Retrive a single option
	 * @param    string     $key      option name
	 * @param    mixed      $default  default option value if option isn't already set
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_option( $key, $default = '' ) {

		$options = $this->get_options();

		if ( isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}

		return $default;

	}

	/**
	 * Update the value of an option
	 * @param    string     $key  option name
	 * @param    mixed      $val  option value
	 * @return   boolean          True if option value has changed, false if not or if update failed.
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_option( $key, $val ) {

		$options = $this->get_options();
		$options[ $key ] = $val;
		return update_option( 'llms_addon_upgrader', $options, false );

	}

	/**
	 * Activate LifterLMS License Keys with the remote server
	 * @param    string     $keys  white-space separated list of API keys
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function activate_keys( $keys ) {

		// sanitize before sending
		$keys = explode( ' ', sanitize_text_field( $keys ) );
		$keys = array_map( 'trim', $keys );
		$keys = array_unique( $keys );

		$data = array(
			'keys' => $keys,
			'url' => get_site_url(),
		);

		$req = new LLMS_Dot_Com_API( '/license/activate', $data );
		return $req->get_result();

	}

	/**
	 * Deactivate LifterLMS API keys with remote server
	 * @param    array     $keys  array of keys
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function deactivate_keys( $keys ) {

		$keys = array_map( 'sanitize_text_field', $keys );
		$keys = array_map( 'trim', $keys );


		$data = array(
			'keys' => array(),
			'url' => get_site_url(),
		);

		$saved = $this->get_license_keys();
		foreach ( $keys as $key ) {
			if ( $saved[ $key ] && $saved[ $key ]['update_key'] ) {
				$data['keys'][ $key ] = $saved[ $key ]['update_key'];
			}
		}

		$req = new LLMS_Dot_Com_API( '/license/deactivate', $data );
		return $req->get_result();

	}

	/**
	 * Retrieve an array of addons that are available via currently active License Keys
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_available_products() {

		$files = array();
		foreach ( $this->get_license_keys() as $key ) {
			if ( 1 == $key['status'] ) {
				$files = array_merge( $files, $key['addons'] );
			}
		}

		return array_unique( $files );

	}

	/**
	 * Retrieve available products from the LifterLMS.com API
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_products( $use_cache = true ) {

		$data = false;
		if ( $use_cache ) {
			$data = get_transient( 'llms_products_api_result' );
		}

		if ( false === $data ) {

			$req = new LLMS_Dot_Com_API( '/products', array(), 'GET' );
			$data = $req->get_result();

			if ( $req->is_error() ) {
				return $data;
			}

			set_transient( 'llms_products_api_result', $data, DAY_IN_SECONDS );

		}

		return $data;

	}

	/**
	 * Retrieve saved license key data
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_license_keys() {
		return $this->get_option( 'license_keys', array() );
	}

	/**
	 * Update saved license key data
	 * @param    array     $keys  key data to save
	 * @return   boolean          True if option value has changed, false if not or if update failed.
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_license_keys( $keys ) {
		return $this->set_option( 'license_keys', $keys );
	}

	/**
	 * Add a single license key
	 * @param    string     $key         license key
	 * @param    string     $update_key  update key
	 * @param    array      $addons      array of addon files
	 * @return   boolean          True if option value has changed, false if not or if update failed.
	 * @since    [version]
	 * @version  [version]
	 */
	public function add_license_key( $key, $update_key, $addons ) {

		$keys = $this->get_license_keys();
		$keys[ $key ] = array(
			'status' => 1,
			'license_key' => $key,
			'update_key' => $update_key,
			'addons' => $addons,
		);

		return $this->set_license_keys( $keys );

	}

	/**
	 * Remove a single license key
	 * @param    string     $key  license key
	 * @return   boolean          True if option value has changed, false if not or if update failed.
	 * @since    [version]
	 * @version  [version]
	 */
	public function remove_license_key( $key ) {
		$keys = $this->get_license_keys();
		if ( isset( $keys[ $key ] ) ) {
			unset( $keys[ $key ] );
		}
		return $this->set_license_keys( $keys );
	}


	public function get_product_data_by( $key, $val ) {

		$products = $this->get_products();
		if ( empty( $products['items'] ) ) {
			return false;
		}

		foreach ( $products['items'] as $product ) {

			if ( isset( $product[ $key ] ) && $product[ $key ] === $val ) {
				return $product;
			}

		}

		return false;

	}






	public function update_plugins_filter( $value ) {

		if ( empty( $value ) ) {
			return $value;
		}

		$all_products = $this->get_products( false );
		$plugins = get_plugins();
		$available = $this->get_available_products();

		// var_dump( $value );
		// var_dump( $plugins );

		foreach ( $all_products['items'] as $addon_data ) {

			// var_dump( $addon_data );
			if ( ! $addon_data['update_file'] ) {
				continue;
			}

			$file = $addon_data['update_file'];

			$plugin_data = isset( $plugins[ $file ] ) ? $plugins[ $file ] : false;

			// plugin is not installed
			if ( ! $plugin_data ) {
				continue;
			}

			$item = array(
				'id' => $addon_data['id'],
				'slug' => $addon_data['id'],
				'plugin' => $file,
				'new_version' => $addon_data['version'],
				'url' => $addon_data['permalink'],
				'package' => '',
			);

			if ( in_array( $file, $available ) ) {
				$item['package'] = add_query_arg( array(
					'license_key' => '',
					'update_key' => '',
					'slug' => $id[0],
				), 'https://lifterlms.com/wp-json/llms/v3/download' );
			}

			// there is an avialable update
			if ( version_compare( $plugin_data['Version'], $addon_data['version'], '<' ) ) {
				$value->response[ $file ] = (object) $item;
				unset( $value->no_update[ $file ] );
			} else {
				$value->no_update[ $file ] = (object) $item;
				unset( $value->response[ $file ] );
			}

		}

		// add_action( 'shutdown', function() {

		// 	delete_option( '_site_transient_update_plugins' );

		// } );

		return $value;
	}

	/**
	 * Filter API calls to get plugin information and replace it with data from LifterLMS.com API for our addons
	 * @param    bool       $response  false (denotes API call should be made to wp.org for plugin info)
	 * @param    string     $action    name of the API action
	 * @param    obj        $args      additional API call args
	 * @return   false|obj
	 * @since    [version]
	 * @version  [version]
	 */
	public function plugins_api( $response, $action = '', $args = null ) {

		if ( 'plugin_information' !== $action ) {
			return $response;
		}

		if ( empty( $args->slug ) ) {
			return $response;
		}

		if ( 0 !== strpos( $args->slug, 'lifterlms-com-' ) ) {
			return $response;
		}

		return $this->set_plugins_api( $args->slug );

	}

	/**
	 * Setup an object of addon data for use when requesting plugin information normally acquired from wp.org
	 * @param    string     $id  addon id
	 * @return   object
	 * @since    [version]
	 * @version  [version]
	 */
	private function set_plugins_api( $id ) {

		$addon = $this->get_product_data_by( 'id', $id );

		$changelog = file_get_contents( $addon['changelog'] );
		preg_match('#<body[^>]*>(.*?)</body>#si', $changelog, $changelog );
    	$changelog = $changelog[1];

		$item = array(
			'name' => $addon['title'],
			'slug' => $id,
			'version' => $addon['version'],
			'author' => '<a href="https://lifterlms.com/">' . $addon['author']['name'] . '</a>',
			'author_profile' => $addon['author']['link'],
			// 'requires' => '',
			// 'tested' => '',
			// 'requires_php' => '',
			// 'compatibility' => '',
			'homepage' => $addon['permalink'],
			'sections' => array(
				'description' => $addon['description'],
				'changelog' => $changelog,
			),
			// 'download_link' => '',
			'banners' => array(
				'low' => $addon['image'],
			),
		);

		return (object) $item;

	}

}

return LLMS_AddOn_Upgrader::instance();
