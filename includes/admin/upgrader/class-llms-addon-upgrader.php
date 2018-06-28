<?php
defined( 'ABSPATH' ) || exit;

/**
 * Actions and LifterLMS.com API interactions related to plugin and theme updates for LifterLMS premium add-ons
 * @since    [version]
 * @version  [version]
 */
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

	/**
	 * Constructor
	 * @since    [version]
	 * @version  [version]
	 */
	private function __construct() {

		// cron to check status of license keys
		add_action( 'llms_check_license_keys', array( $this, 'check_keys' ) );

		// setup a llms add-on plugin info
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );

		// authenticate and get a real download link during add-on upgrade attempts
		add_filter( 'upgrader_package_options', array( $this, 'upgrader_package_options' ) );

		// add llms add-on info to list of available updates
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'pre_set_site_transient_update_things' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_things' ) );

		$products = $this->get_products();
		foreach ( (array) $products['items'] as $product ) {

			if ( 'plugin' === $product['type'] && $product['update_file'] ) {
				add_action( "in_plugin_update_message-{$product['update_file']}", array( $this, 'in_plugin_update_message' ), 10, 2 );
			}
		}

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
	 * Add a single license key
	 * @param    string    $activation_data   array of activation details from api call
	 * @return   boolean                      True if option value has changed, false if not or if update failed.
	 * @since    [version]
	 * @version  [version]
	 */
	public function add_license_key( $activation_data ) {

		$keys = $this->get_license_keys();
		$keys[ $activation_data['license_key'] ] = array(
			'product_id' => $activation_data['id'],
			'status' => 1,
			'license_key' => $activation_data['license_key'],
			'update_key' => $activation_data['update_key'],
			'addons' => $activation_data['addons'],
		);

		return $this->set_license_keys( $keys );

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
		}// End if().

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
	 * @param    bool     $installable_only   if true, only includes installable addons
	 *                                        if false, includes non-installable addons (like bundles)
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_available_products( $installable_only = true ) {

		$ids = array();
		foreach ( $this->get_license_keys() as $key ) {
			if ( 1 == $key['status'] ) {
				$ids = array_merge( $ids, $key['addons'] );
			}
			if ( false === $installable_only ) {
				$ids[] = $key['product_id'];
			}
		}

		return array_unique( $ids );

	}

	/**
	 * Get info about addon channel subscriptions
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_channels() {
		return $this->get_option( 'channels', array() );
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
	 * Retrieve all upgrader options array
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function get_options() {
		return get_option( 'llms_addon_upgrader', array() );
	}

	/**
	 * Retrieve add-on data by add-on array key and value
	 * @param    string     $key  key name
	 * @param    string     $val  value
	 * @return   array|false
	 * @since    [version]
	 * @version  [version]
	 */
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

		// llms_log( $data );

		return $data;

	}

	/**
	 * Install an add-on from LifterLMS.com
	 * @param    string|obj     $addon_or_id   ID for the add-on or an instance of the LLMS_Add_On
	 * @return   WP_Error|true
	 * @since    [version]
	 * @version  [version]
	 */
	public function install_addon( $addon_or_id, $action = 'install' ) {

		// setup the addon
		$addon = is_a( $addon_or_id, 'LLMS_Add_On' ) ? $addon_or_id : new LLMS_Add_On( $addon_or_id );
		if ( ! $addon ) {
			return new WP_Error( 'invalid_addon', __( 'Invalid add-on ID.', 'lifterlms' ) );
		}

		if ( ! in_array( $action, array( 'install', 'update' ) ) ) {
			return new WP_Error( 'invalid_action', __( 'Invalid action.', 'lifterlms' ) );
		}

		if ( ! $addon->is_installable() ) {
			return new WP_Error( 'not_installable', __( 'Add-on cannot be installable.', 'lifterlms' ) );
		}

		// make sure it's not already installed
		if ( 'install' === $action && $addon->is_installed() ) {
			/* Translators: %s = Add-on name */
			return new WP_Error( 'installed', sprintf( __( '%s is already installed', 'lifterlms' ), $addon->get( 'title' ) ) );
		}

		// get download info via llms.com api
		$dl_info = $addon->get_download_info();
		if ( is_wp_error( $dl_info ) ) {
			return $dl_info;
		}
		if ( ! isset( $dl_info['data']['url'] ) ) {
			return new WP_Error( 'no_url', __( 'An error occured while attempting to retrieve add-on download information. Please try again.', 'lifterlms' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		WP_Filesystem();

		$skin = new Automatic_Upgrader_Skin();

		if ( 'plugin' === $addon->get_type() ) {

			$upgrader = new Plugin_Upgrader( $skin );

		} elseif ( 'theme' === $addon->get_type() ) {

			$upgrader = new Theme_Upgrader( $skin );

		} else {

			return new WP_Error( 'inconceivable', __( 'The requested action is not possible.', 'lifterlms' ) );

		}

		if ( 'install' === $action ) {
			remove_filter( 'upgrader_package_options', array( $this, 'upgrader_package_options' ) );
			$result = $upgrader->install( $dl_info['data']['url'] );
			add_filter( 'upgrader_package_options', array( $this, 'upgrader_package_options' ) );
		} elseif ( 'update' === $action ) {
			$result = $upgrader->upgrade( $addon->get( 'update_file' ) );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		} elseif ( is_wp_error( $skin->result ) ) {
			return $skin->result;
		} elseif ( is_null( $result ) ) {
			return new WP_Error( 'filesystem', __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'lifterlms' ) );
		}

		return true;

	}

	/**
	 * Output additional information on plugins update screen when updates are available
	 * for an unlicensed addon
	 * @param    array     $plugin_data  array of plugin data
	 * @param    array     $res          response data
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function in_plugin_update_message( $plugin_data, $res ) {

		if ( empty( $plugin_data['package'] ) ) {

			echo '<style>p.llms-msg:before { content: ""; }</style>';

			echo '<p class="llms-msg"><strong>';
			_e( 'Your LifterLMS add-on is currently unlicensed and cannot be updated!', 'lifterlms' );
			echo '</strong></p>';

			echo '<p class="llms-msg">';
			/* Translators: %1$s = Opening anchor tag; %2$s = Closing anchor tag */
			printf( __( 'If you already have a license, you can activate it on the %1$sadd-ons management screen%2$s.', 'lifterlms' ), '<a href="#">', '</a>' );
			echo '</p>';

			echo '<p class="llms-msg">';
			/* Translators: %s = URI to licensing FAQ */
			printf( __( 'Learn more about LifterLMS add-on licensing at %s.', 'lifterlms' ), make_clickable( 'https://lifterlms.com/#' ) );
			echo '</p><p style="display:none;">';

		}

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

		$core = false;

		if ( 'lifterlms' === $args->slug ) {
			remove_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
			$args->slug = 'lifterlms-com-lifterlms';
			$core = true;
		}

		if ( 0 !== strpos( $args->slug, 'lifterlms-com-' ) ) {
			return $response;
		}

		$response = $this->set_plugins_api( $args->slug, true );

		if ( $core ) {
			add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
		}

		return $response;

	}

	/**
	 * Handle setting the site transient for plugin updates
	 * @param    obj     $value  transient value
	 * @return   obj
	 * @since    [version]
	 * @version  [version]
	 */
	public function pre_set_site_transient_update_things( $value ) {

		if ( empty( $value ) ) {
			return $value;
		}

		$which = current_filter();
		if ( 'pre_set_site_transient_update_plugins' === $which ) {
			$type = 'plugin';
		} elseif ( 'pre_set_site_transient_update_themes' === $which ) {
			$type = 'theme';
		} else {
			return $value;
		}

		$all_products = $this->get_products( false );

		// llms_log( $all_products );

		foreach ( $all_products['items'] as $addon_data ) {

			$addon = new LLMS_Add_On( $addon_data );

			if ( ! $addon->is_installable() || ! $addon->is_installed() ) {
				continue;
			}

			if ( $type !== $addon->get_type() ) {
				continue;
			}

			$file = $addon->get( 'update_file' );

			if ( 'plugin' === $type ) {

				if ( 'lifterlms-com-lifterlms' === $addon->get( 'id' ) ) {
					if ( 'stable' === $addon->get_channel_subscription() || ! $addon->get( 'version_beta' ) ) {
						continue;
					}
				}

				$item = (object) $this->set_plugins_api( $addon->get( 'id' ), false );

			} elseif ( 'theme' === $type ) {

				$item = array(
					'theme' => $file,
					'new_version' => $addon->get_latest_version(),
					'url' => $addon->get_permalink(),
					'package' => true,
				);
			}

			if ( $addon->has_available_update() ) {

				$value->response[ $file ] = $item;
				unset( $value->no_update[ $file ] );

			} else {

				$value->no_update[ $file ] = $item;
				unset( $value->response[ $file ] );

			}
		}

		return $value;

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

	/**
	 * Set info about addon channel subscriptions
	 * @param    array     $channels  array of channel information
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_channels( $channels ) {
		return $this->set_option( 'channels', $channels );
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
	 * Setup an object of addon data for use when requesting plugin information normally acquired from wp.org
	 * @param    string     $id  addon id
	 * @return   object
	 * @since    [version]
	 * @version  [version]
	 */
	private function set_plugins_api( $id, $include_sections = true ) {

		$addon = new LLMS_Add_On( $id );

		if ( 'lifterlms-com-lifterlms' === $id && false !== strpos( $addon->get_latest_version(), 'beta' ) ) {

			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$item = plugins_api( 'plugin_information', array(
				'slug' => 'lifterlms',
				'fields' => array(
					'banners' => true,
					'icons' => true,
				),
			) );
			$item->version = $addon->get_latest_version();
			$item->new_version = $addon->get_latest_version();
			$item->package = true;

			unset( $item->versions );

			$item->sections['changelog'] = $this->get_changelog_for_api( $addon );

			return $item;

		}

		$item = array(
			'name' => $addon->get( 'title' ),
			'slug' => $id,
			'version' => $addon->get_latest_version(),
			'new_version' => $addon->get_latest_version(),
			'author' => '<a href="https://lifterlms.com/">' . $addon->get( 'author' )['name'] . '</a>',
			'author_profile' => $addon->get( 'author' )['link'],
			'requires' => $addon->get( 'version_wp' ),
			'tested' => '',
			'requires_php' => $addon->get( 'version_php' ),
			'compatibility' => '',
			'homepage' => $addon->get( 'permalink' ),
			'download_link' => '',
			'package' => $addon->is_licensed() ? true : '',
			'banners' => array(
				'low' => $addon->get( 'image' ),
			),
		);

		if ( $include_sections ) {

			$item['sections'] = array(
				'description' => $addon->get( 'description' ),
				'changelog' => $this->get_changelog_for_api( $addon ),
			);

		}

		return (object) $item;

	}

	/**
	 * Retrieve the changelog for an addon
	 * @param    obj     $addon  LLMS_Add_On
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_changelog_for_api( $addon ) {

		$changelog = file_get_contents( $addon->get( 'changelog' ) );
		preg_match( '#<body[^>]*>(.*?)</body>#si', $changelog, $changelog );
		// css on h2 is intended for plugin title in header image but causes huge gap on changelog
		return str_replace( array( '<h2 id="', '</h2>' ), array( '<h3 id="', '</h3>' ), $changelog[1] );

	}

	/**
	 * Get a real package download url for a LifterLMS add-on
	 * This is called immediately prior to package upgrades
	 * @param    [type]     $options  [description]
	 * @return   [type]
	 * @since    [version]
	 * @version  [version]
	 */
	public function upgrader_package_options( $options ) {

		if ( ! isset( $options['hook_extra'] ) ) {
			return $options;
		}

		if ( isset( $options['hook_extra']['plugin'] ) ) {
			$file = $options['hook_extra']['plugin'];
		} elseif ( isset( $options['hook_extra']['theme'] ) ) {
			$file = $options['hook_extra']['theme'];
		} else {
			return $options;
		}

		$addon = $this->get_product_data_by( 'update_file', $file );
		if ( ! $addon ) {
			return $options;
		}

		$addon = new LLMS_Add_On( $addon );
		if ( ! $addon->is_installable() || ! $addon->is_licensed() ) {
			return $options;
		}

		$info = $addon->get_download_info();
		if ( is_wp_error( $info ) || ! isset( $info['data'] ) || ! isset( $info['data']['url'] ) ) {
			return $options;
		}

		$options['package'] = $info['data']['url'];

		return $options;

	}

}

return LLMS_AddOn_Upgrader::instance();
