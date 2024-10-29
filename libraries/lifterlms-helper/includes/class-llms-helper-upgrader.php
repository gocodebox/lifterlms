<?php
/**
 * Actions and LifterLMS.com API interactions related to plugin and theme updates for LifterLMS premium add-ons
 *
 * @package LifterLMS_Helper/Classes
 *
 * @since 3.0.0
 * @version 3.4.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Helper_Upgrader
 *
 * @since 3.0.0
 * @since 3.0.2 Unknown.
 * @since 3.1.0 Load changelogs from the make blog in favor of static html changelogs.
 */
class LLMS_Helper_Upgrader {

	/**
	 * Singleton instance
	 *
	 * @var null|LLMS_Helper_Upgrader
	 */
	protected static $instance = null;

	/**
	 * Main Instance of LLMS_Helper_Upgrader
	 *
	 * @since 3.0.0
	 * @since version] Use `self::$instance` in favor of `self::$_instance`.
	 *
	 * @return LLMS_Helper_Upgrader
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 * @since 3.0.2 Unknown.
	 *
	 * @return void
	 */
	private function __construct() {

		// Setup a llms add-on plugin info.
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );

		// Authenticate and get a real download link during add-on upgrade attempts.
		add_filter( 'upgrader_package_options', array( $this, 'upgrader_package_options' ) );

		// Add llms add-on info to list of available updates.
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'pre_set_site_transient_update_things' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_things' ) );

		add_action( 'admin_init', array( $this, 'register_addon_translation_updates' ) );

		$products = llms_get_add_ons();
		if ( ! is_wp_error( $products ) && isset( $products['items'] ) ) {
			foreach ( (array) $products['items'] as $product ) {

				if ( 'plugin' === $product['type'] && $product['update_file'] ) {
					add_action( "in_plugin_update_message-{$product['update_file']}", array( $this, 'in_plugin_update_message' ), 10, 2 );
				}
			}
		}
	}

	/**
	 * Check for translation updates.
	 *
	 * @since 3.5.3
	 *
	 * @return void
	 */
	public function register_addon_translation_updates() {
		$products = llms_get_add_ons();
		if ( is_wp_error( $products ) || ! isset( $products['items'] ) ) {
			return;
		}
		foreach ( (array) $products['items'] as $product ) {
			if ( isset( $product['slug'] ) && $product['slug'] ) {
				$addon = llms_get_add_on( $product );

				if ( ! $addon->is_installable() || ! $addon->is_installed() ) {
					continue;
				}

				Lifterlms\Lifterlms_Helper\Required\Traduttore_Registry\add_project(
					$product['type'],
					$product['slug'],
					'https://translate.lifterlms.com/translate/api/translations/' . $product['slug']
				);
			}
		}
	}

	/**
	 * Install an add-on from LifterLMS.com
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Use strict comparison for `in_array()`.
	 * @since 3.4.0 Use core textdomain.
	 *
	 * @param string|obj $addon_or_id ID for the add-on or an instance of the LLMS_Add_On.
	 * @param string     $action      Installation type [install|update].
	 * @return WP_Error|true
	 */
	public function install_addon( $addon_or_id, $action = 'install' ) {

		// Setup the addon.
		$addon = is_a( $addon_or_id, 'LLMS_Add_On' ) ? $addon_or_id : llms_get_add_on( $addon_or_id );
		if ( ! $addon ) {
			return new WP_Error( 'invalid_addon', __( 'Invalid add-on ID.', 'lifterlms' ) );
		}

		if ( ! in_array( $action, array( 'install', 'update' ), true ) ) {
			return new WP_Error( 'invalid_action', __( 'Invalid action.', 'lifterlms' ) );
		}

		if ( ! $addon->is_installable() ) {
			return new WP_Error( 'not_installable', __( 'Add-on cannot be installable.', 'lifterlms' ) );
		}

		// Make sure it's not already installed.
		if ( 'install' === $action && $addon->is_installed() ) {
			// Translators: %s = Add-on name.
			return new WP_Error( 'installed', sprintf( __( '%s is already installed', 'lifterlms' ), $addon->get( 'title' ) ) );
		}

		// Get download info via llms.com api.
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
	 * Output additional information on plugins update screen when updates are available for an unlicensed addon
	 *
	 * @since 3.0.0
	 * @since 3.0.2 Unknown.
	 * @since 3.4.0 Use core textdomain.
	 *
	 * @param array $plugin_data Array of plugin data.
	 * @param array $res         Response data.
	 * @return void
	 */
	public function in_plugin_update_message( $plugin_data, $res ) {

		if ( empty( $plugin_data['package'] ) ) {

			echo '<style>p.llms-msg:before { content: ""; }</style>';

			echo '<p class="llms-msg"><strong>';
			esc_html_e( 'Your LifterLMS add-on is currently unlicensed and cannot be updated!', 'lifterlms' );
			echo '</strong></p>';

			echo '<p class="llms-msg">';
			// Translators: %1$s = Opening anchor tag; %2$s = Closing anchor tag.
			printf( esc_html__( 'If you already have a license, you can activate it on the %1$sadd-ons management screen%2$s.', 'lifterlms' ), '<a href="' . esc_url( admin_url( 'admin.php?page=llms-add-ons' ) ) . '">', '</a>' );
			echo '</p>';

			echo '<p class="llms-msg">';
			// Translators: %s = URI to licensing FAQ.
			printf( esc_html__( 'Learn more about LifterLMS add-on licensing at %s.', 'lifterlms' ), wp_kses_post( make_clickable( 'https://lifterlms.com/docs/lifterlms-helper/' ) ) );
			echo '</p><p style="display:none;">';

		}
	}

	/**
	 * Filter API calls to get plugin information and replace it with data from LifterLMS.com API for our addons
	 *
	 * @since 3.0.0
	 *
	 * @param bool   $response False (denotes API call should be made to wp.org for plugin info).
	 * @param string $action   Name of the API action.
	 * @param obj    $args     Additional API call args.
	 * @return false|obj
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
			$addon = llms_get_add_on( 'lifterlms-com-lifterlms' );
			if ( false !== strpos( $addon->get_channel_subscription(), 'beta' ) ) {
				remove_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
				$args->slug = 'lifterlms-com-lifterlms';
				$core       = true;
			}
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
	 *
	 * @since 3.0.0
	 * @since 3.0.2 Unknown.
	 *
	 * @param obj $value Transient value.
	 * @return obj
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

		$all_products = llms_get_add_ons( false );
		if ( is_wp_error( $all_products ) || ! isset( $all_products['items'] ) ) {
			return $value;
		}

		foreach ( $all_products['items'] as $addon_data ) {

			$addon = llms_get_add_on( $addon_data );

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
					'theme'       => $file,
					'new_version' => $addon->get_latest_version(),
					'url'         => $addon->get_permalink(),
					'package'     => true,
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
	 * Setup an object of addon data for use when requesting plugin information normally acquired from wp.org.
	 *
	 * @since 3.0.0
	 * @since 3.2.1 Set package to `true` for add-ons which don't require a license.
	 * @since 3.4.2 Added a `plugin` property to the returned plugin object,
	 *              which is required by `WP_Plugin_Install_List_Table::prepare_items()`.
	 *
	 * @param string $id               Addon id.
	 * @param bool   $include_sections Whether or not to include additional sections like the description and changelog.
	 * @return object
	 */
	private function set_plugins_api( $id, $include_sections = true ) {

		$addon = llms_get_add_on( $id );

		if ( 'lifterlms-com-lifterlms' === $id && false !== strpos( $addon->get_latest_version(), 'beta' ) ) {

			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$item              = plugins_api(
				'plugin_information',
				array(
					'slug'   => 'lifterlms',
					'fields' => array(
						'banners' => true,
						'icons'   => true,
					),
				)
			);
			$item->version     = $addon->get_latest_version();
			$item->new_version = $addon->get_latest_version();
			$item->package     = true;

			unset( $item->versions );

			$item->sections['changelog'] = $this->get_changelog_for_api( $addon );

			return $item;

		}

		$item = array(
			'name'           => $addon->get( 'title' ),
			'slug'           => $id,
			'plugin'         => $addon->get( 'update_file' ),
			'version'        => $addon->get_latest_version(),
			'new_version'    => $addon->get_latest_version(),
			'author'         => '<a href="https://lifterlms.com/">' . $addon->get( 'author' )['name'] . '</a>',
			'author_profile' => $addon->get( 'author' )['link'],
			'requires'       => $addon->get( 'version_wp' ),
			'tested'         => '',
			'requires_php'   => $addon->get( 'version_php' ),
			'compatibility'  => '',
			'homepage'       => $addon->get( 'permalink' ),
			'download_link'  => '',
			'package'        => ( $addon->is_licensed() || ! $addon->requires_license() ),
			'banners'        => array(
				'low' => $addon->get( 'image' ),
			),
		);

		if ( $include_sections ) {

			$item['sections'] = array(
				'description' => $addon->get( 'description' ),
				'changelog'   => $this->get_changelog_for_api( $addon ),
			);

		}

		return (object) $item;
	}

	/**
	 * Retrieve the changelog for an addon
	 *
	 * Attempts to retrieve changelog HTML from the make blog.
	 *
	 * If the add-on's changelog is empty or a static html file, returns an error
	 * with a link to the release notes category on the make blog.
	 *
	 * @since 3.0.0
	 * @since 3.1.0 Retrieve changelog from the make blog in favor of legacy static html changelogs.
	 * @since 3.2.0 Fix usage of incorrect textdomain.
	 *
	 * @param LLMS_Add_On $addon Add-on object.
	 * @return string
	 */
	private function get_changelog_for_api( $addon ) {

		$src   = $addon->get( 'changelog' );
		$split = array_filter( explode( '/', $src ) );
		$tag   = end( $split );

		$logs = false;
		if ( ! empty( $tag ) && false === strpos( $tag, '.html' ) ) {
			$logs = $this->get_changelog_html( $tag, $src );
		}

		// Translators: %s = URL for the changelog website.
		return $logs ? $logs : make_clickable( sprintf( __( 'There was an error retrieving the changelog.<br>Try visiting %s for recent changelogs.', 'lifterlms' ), 'https://make.lifterlms.com/category/release-notes/' ) );
	}

	/**
	 * Retrieve changelog information from the make blog
	 *
	 * Retrieves the most recent 10 changelog entries for the add-on, formats the returned information
	 * into a format suitable to display within the thickbox, adds a link to the full changelog,
	 * and returns the html string.
	 *
	 * If an error is encountered, returns an empty string.
	 *
	 * @since 3.1.0
	 * @since 3.2.0 Fix usage of incorrect textdomain.
	 *
	 * @param string $tag Tag slug for the add-on on the blog.
	 * @param string $url Full URL to the changelog entries for the add-on.
	 * @return string
	 */
	private function get_changelog_html( $tag, $url ) {

		$ret  = '';
		$req  = wp_remote_get( add_query_arg( 'slug', $tag, 'https://make.lifterlms.com/wp-json/wp/v2/tags' ) );
		$body = json_decode( wp_remote_retrieve_body( $req ), true );

		if ( ! empty( $body ) && ! empty( $body[0]['_links']['wp:post_type'][0]['href'] ) ) {

			$logs_url = $body[0]['_links']['wp:post_type'][0]['href'];
			$logs_req = wp_remote_get( $logs_url );
			$logs     = json_decode( wp_remote_retrieve_body( $logs_req ), true );

			if ( ! empty( $logs ) && is_array( $logs ) ) {
				foreach ( $logs as $log ) {
					$ts    = strtotime( $log['date_gmt'] );
					$date  = function_exists( 'wp_date' ) ? wp_date( 'Y-m-d', $ts ) : gmdate( 'Y-m-d', $ts );
					$split = array_filter( explode( ' ', $log['title']['rendered'] ) );
					$ver   = end( $split );
					// Translators: %1$s - Version number; %2$s - Release date.
					$ret .= '<h4>' . sprintf( __( 'Version %1$s - %2$s', 'lifterlms' ), sanitize_text_field( wp_strip_all_tags( trim( $ver ) ) ), $date ) . '</h4>';
					$ret .= strip_tags( $log['content']['rendered'], '<ul><li><p><a><b><strong><em><i>' );
				}
			}

			$ret .= '<br>';
			// Translators: %s = URL to the full changelog.
			$ret .= '<p>' . make_clickable( sprintf( __( 'View the full changelog at %s.', 'lifterlms' ), $url ) ) . '</p>';

		}

		return $ret;
	}

	/**
	 * Get a real package download url for a LifterLMS add-on
	 *
	 * This is called immediately prior to package upgrades.
	 *
	 * @since 3.0.0
	 * @since 3.0.2 Unknown.
	 * @since 3.2.1 Correctly process addons which do not require a license (e.g. free products).
	 *
	 * @param array $options Package option data.
	 * @return array
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

		$addon = llms_get_add_on( $file, 'update_file' );
		if ( ! $addon || ! $addon->is_installable() || ( $addon->requires_license() && ! $addon->is_licensed() ) ) {
			return $options;
		}

		$info = $addon->get_download_info();
		if ( is_wp_error( $info ) || ! isset( $info['data'] ) || ! isset( $info['data']['url'] ) ) {
			return $options;
		}

		if ( true === $options['package'] ) {
			$options['package'] = $info['data']['url'];
		}

		return $options;
	}
}
