<?php
/**
 * Catalog-driven rows for Integrations and Checkout settings tables.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Catalog_Table class.
 *
 * @since [version]
 */
class LLMS_Admin_Catalog_Table {

	/**
	 * Catalog product IDs that should never appear on settings tables.
	 *
	 * @since [version]
	 *
	 * @return string[]
	 */
	public static function get_excluded_ids() {

		/**
		 * Filters catalog product IDs excluded from Integrations and Checkout tables.
		 *
		 * @since [version]
		 *
		 * @param string[] $ids Product IDs.
		 */
		return apply_filters(
			'llms_settings_catalog_excluded_ids',
			array(
				'lifterlms-com-lifterlms',
				'lifterlms-com-lifterlms-helper',
				'lifterlms-com-lifterlms-pro',
				'lifterlms-com-office-hours',
				'lifterlms-com-aircraft',
			)
		);
	}

	/**
	 * Extra catalog IDs that belong on Checkout despite missing the e-commerce category.
	 *
	 * @since [version]
	 *
	 * @return string[]
	 */
	public static function get_checkout_extra_ids() {

		/**
		 * Filters extra catalog IDs shown on the Checkout gateways table.
		 *
		 * @since [version]
		 *
		 * @param string[] $ids Product IDs.
		 */
		return apply_filters(
			'llms_settings_checkout_catalog_extra_ids',
			array(
				'lifterlms-com-lifterlms-name-your-price',
			)
		);
	}

	/**
	 * Retrieve installable first-party catalog add-ons for a settings screen.
	 *
	 * @since [version]
	 *
	 * @param string $screen Either "integrations" or "checkout".
	 * @return LLMS_Add_On[]
	 */
	public static function get_catalog_addons( $screen ) {

		$data = llms_get_add_ons();
		if ( is_wp_error( $data ) || empty( $data['items'] ) ) {
			return array();
		}

		$excluded = self::get_excluded_ids();
		$extra    = self::get_checkout_extra_ids();
		$addons   = array();

		foreach ( $data['items'] as $item ) {

			$addon = llms_get_add_on( $item );
			if ( ! $addon->is_installable() || 'plugin' !== $addon->get_type() ) {
				continue;
			}

			$id = $addon->get( 'id' );
			if ( in_array( $id, $excluded, true ) ) {
				continue;
			}

			$categories = array_keys( (array) $addon->get( 'categories' ) );
			$is_ecom    = in_array( 'e-commerce', $categories, true ) || in_array( $id, $extra, true );

			if ( 'checkout' === $screen && ! $is_ecom ) {
				continue;
			}

			if ( 'integrations' === $screen && $is_ecom ) {
				continue;
			}

			$addons[] = $addon;
		}

		if ( 'integrations' === $screen ) {
			/**
			 * Filters catalog product IDs included on the Integrations table.
			 *
			 * Return an array of product IDs to limit the catalog rows to that list.
			 * Return null to include every eligible catalog add-on.
			 *
			 * @since [version]
			 *
			 * @param null|string[] $ids Product IDs, or null for all eligible items.
			 */
			$include_ids = apply_filters( 'llms_settings_integrations_catalog_ids', null );
			if ( is_array( $include_ids ) ) {
				$addons = array_values(
					array_filter(
						$addons,
						function ( $addon ) use ( $include_ids ) {
							return in_array( $addon->get( 'id' ), $include_ids, true );
						}
					)
				);
			}
		}

		/**
		 * Filters catalog add-ons included on a settings screen table.
		 *
		 * @since [version]
		 *
		 * @param LLMS_Add_On[] $addons Add-on models.
		 * @param string        $screen Screen id ("integrations" or "checkout").
		 */
		return apply_filters( 'llms_settings_catalog_table_addons', $addons, $screen );
	}

	/**
	 * Display title for a table row.
	 *
	 * @since [version]
	 *
	 * @param string $title Original title.
	 * @param string $id    Integration, gateway, or catalog id.
	 * @return string
	 */
	public static function get_display_title( $title, $id = '' ) {

		$normalized = strtolower( str_replace( array( '_', '-' ), '', $id ) );
		$title_key  = strtolower( $title );

		if ( 'bbpress' === $normalized || 'bbpress' === $title_key ) {
			return __( 'LifterLMS bbPress', 'lifterlms' );
		}

		if ( 'buddypress' === $normalized || 'buddypress' === $title_key ) {
			return __( 'LifterLMS BuddyPress', 'lifterlms' );
		}

		if ( false !== strpos( $normalized, 'twilio' ) || false !== strpos( $title_key, 'twilio' ) ) {
			return __( 'LifterLMS Twilio', 'lifterlms' );
		}

		return $title;
	}

	/**
	 * Prefix a registered title with its catalog add-on when several integrations share one plugin.
	 *
	 * @since [version]
	 *
	 * @param string           $title          Registered title.
	 * @param string           $id             Registered id.
	 * @param LLMS_Add_On|null $addon          Matching catalog add-on.
	 * @param int              $sibling_count  Number of registered objects from the same add-on.
	 * @return string
	 */
	public static function get_grouped_display_title( $title, $id, $addon = null, $sibling_count = 1 ) {

		$display = self::get_display_title( $title, $id );
		if ( ! $addon || $sibling_count < 2 ) {
			return $display;
		}

		$catalog_title = self::get_display_title( $addon->get( 'title' ), $addon->get( 'id' ) );
		if ( ! $catalog_title || false !== stripos( $display, $catalog_title ) ) {
			return $display;
		}

		$suffix = $display;
		$colon  = strrpos( $display, ':' );
		if ( false !== $colon ) {
			$suffix = trim( substr( $display, $colon + 1 ) );
		}

		return $catalog_title . ': ' . $suffix;
	}

	/**
	 * Plugin directory slug for a registered integration or gateway class.
	 *
	 * Core LifterLMS classes return empty so they only match catalog rows by id.
	 *
	 * @since [version]
	 *
	 * @param object $object Integration or gateway instance.
	 * @return string
	 */
	public static function get_object_plugin_dir( $object ) {

		if ( ! is_object( $object ) ) {
			return '';
		}

		try {
			$file = ( new ReflectionClass( $object ) )->getFileName();
		} catch ( ReflectionException $e ) {
			return '';
		}

		if ( ! $file ) {
			return '';
		}

		$file       = wp_normalize_path( $file );
		$core_dir   = wp_normalize_path( LLMS_PLUGIN_DIR );
		$plugin_dir = wp_normalize_path( WP_PLUGIN_DIR );

		if ( 0 === strpos( $file, $core_dir ) ) {
			return '';
		}

		if ( 0 !== strpos( $file, $plugin_dir ) ) {
			return '';
		}

		$relative = ltrim( substr( $file, strlen( $plugin_dir ) ), '/' );
		$parts    = explode( '/', $relative );

		return strtolower( $parts[0] );
	}

	/**
	 * Whether a catalog add-on is the plugin that registered an object.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Add_On $addon      Catalog add-on.
	 * @param string      $plugin_dir Plugin directory slug.
	 * @return bool
	 */
	public static function addon_matches_plugin_dir( $addon, $plugin_dir ) {

		$plugin_dir = strtolower( (string) $plugin_dir );
		$update     = strtolower( (string) $addon->get( 'update_file' ) );

		return $plugin_dir && $update && 0 === strpos( $update, $plugin_dir . '/' );
	}

	/**
	 * Documentation URL for a core integration or gateway.
	 *
	 * @since [version]
	 *
	 * @param string $id Registered id.
	 * @return string
	 */
	public static function get_core_docs_url( $id ) {

		$urls = array(
			'bbpress'    => 'https://lifterlms.com/docs/lifterlms-and-bbpress/',
			'buddypress' => 'https://lifterlms.com/docs/lifterlms-and-buddypress/',
			'manual'     => 'https://lifterlms.com/docs/manual-payments/',
		);

		return isset( $urls[ $id ] ) ? $urls[ $id ] : '';
	}

	/**
	 * Whether a catalog add-on represents an already-registered integration or gateway.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Add_On $addon          Catalog add-on.
	 * @param string      $registered_id  Integration or gateway id.
	 * @return bool
	 */
	public static function addon_matches_registered_id( $addon, $registered_id ) {

		$rid = str_replace( '_', '-', strtolower( $registered_id ) );

		$exact = array(
			$rid,
			'lifterlms-' . $rid,
			'lifterlms-com-' . $rid,
			'lifterlms-com-' . $rid . '-extension',
			'lifterlms-com-lifterlms-' . $rid,
			'lifterlms-integration-' . $rid,
			'lifterlms-gateway-' . $rid,
		);

		$slug = strtolower( (string) $addon->get( 'slug' ) );
		$cid  = strtolower( (string) $addon->get( 'id' ) );
		$file = strtolower( (string) $addon->get( 'update_file' ) );

		if ( in_array( $slug, $exact, true ) || in_array( $cid, $exact, true ) ) {
			return true;
		}

		$prefixes = array(
			'lifterlms-' . $rid . '/',
			'lifterlms-gateway-' . $rid . '/',
			'lifterlms-integration-' . $rid . '/',
		);

		foreach ( $prefixes as $prefix ) {
			if ( 0 === strpos( $file, $prefix ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether a catalog add-on represents a registered integration or gateway.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Add_On $addon         Catalog add-on.
	 * @param string      $registered_id Integration or gateway id.
	 * @param object|null $object        Optional registered object, used to match by plugin directory.
	 * @return bool
	 */
	public static function addon_matches_registered( $addon, $registered_id, $object = null ) {

		if ( self::addon_matches_registered_id( $addon, $registered_id ) ) {
			return true;
		}

		if ( $object ) {
			return self::addon_matches_plugin_dir( $addon, self::get_object_plugin_dir( $object ) );
		}

		return false;
	}

	/**
	 * Catalog add-on matching a registered integration or gateway, if any.
	 *
	 * @since [version]
	 *
	 * @param string      $registered_id Integration or gateway id.
	 * @param string      $screen        Screen id.
	 * @param object|null $object        Optional registered object.
	 * @return LLMS_Add_On|null
	 */
	public static function get_addon_for_registered( $registered_id, $screen, $object = null ) {

		foreach ( self::get_catalog_addons( $screen ) as $addon ) {
			if ( self::addon_matches_registered( $addon, $registered_id, $object ) ) {
				return $addon;
			}
		}

		return null;
	}

	/**
	 * Catalog IDs already represented by registered objects.
	 *
	 * @since [version]
	 *
	 * @param string[] $registered_ids Integration or gateway ids.
	 * @param string   $screen         Screen id.
	 * @param object[] $objects        Optional map of registered id => object.
	 * @return string[]
	 */
	public static function get_matched_catalog_ids( $registered_ids, $screen, $objects = array() ) {

		$matched = array();
		foreach ( self::get_catalog_addons( $screen ) as $addon ) {
			foreach ( $registered_ids as $registered_id ) {
				$object = isset( $objects[ $registered_id ] ) ? $objects[ $registered_id ] : null;
				if ( self::addon_matches_registered( $addon, $registered_id, $object ) ) {
					$matched[] = $addon->get( 'id' );
					break;
				}
			}
		}

		return $matched;
	}

	/**
	 * Count of registered objects that belong to the same catalog add-on.
	 *
	 * @since [version]
	 *
	 * @param string   $addon_id       Catalog add-on id.
	 * @param string[] $registered_ids Integration or gateway ids.
	 * @param string   $screen         Screen id.
	 * @param object[] $objects        Map of registered id => object.
	 * @return int
	 */
	public static function get_registered_sibling_count( $addon_id, $registered_ids, $screen, $objects = array() ) {

		$count = 0;
		foreach ( $registered_ids as $registered_id ) {
			$object = isset( $objects[ $registered_id ] ) ? $objects[ $registered_id ] : null;
			$addon  = self::get_addon_for_registered( $registered_id, $screen, $object );
			if ( $addon && $addon->get( 'id' ) === $addon_id ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Product permalink with a settings-screen UTM medium.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Add_On $addon  Add-on model.
	 * @param string      $medium UTM medium.
	 * @return string
	 */
	public static function get_product_url( $addon, $medium ) {
		return $addon->get_permalink( $medium );
	}

	/**
	 * Name-column URL for a catalog-only row.
	 *
	 * Licensed but uninstalled add-ons link to My Add-Ons for install.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Add_On $addon  Add-on model.
	 * @param string      $medium UTM medium.
	 * @return string
	 */
	public static function get_catalog_row_url( $addon, $medium ) {

		if ( ! $addon->is_installed() && $addon->is_licensed() ) {
			return admin_url( 'admin.php?page=llms-add-ons&section=mine' );
		}

		return self::get_product_url( $addon, $medium );
	}

	/**
	 * Documentation URL for a catalog add-on.
	 *
	 * Prefers a getting-started URL when the products feed provides one.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Add_On $addon Add-on model.
	 * @return string
	 */
	public static function get_addon_docs_url( $addon ) {

		foreach ( array( 'getting_started', 'getting-started' ) as $key ) {
			$url = $addon->get( $key );
			if ( $url ) {
				return $url;
			}
		}

		return $addon->get( 'documentation' );
	}

	/**
	 * WP plugin basename for a core integration's third-party dependency.
	 *
	 * @since [version]
	 *
	 * @param string $id Integration id.
	 * @return string
	 */
	public static function get_core_dependency_file( $id ) {

		$files = array(
			'bbpress'    => 'bbpress/bbpress.php',
			'buddypress' => 'buddypress/bp-loader.php',
		);

		return isset( $files[ $id ] ) ? $files[ $id ] : '';
	}

	/**
	 * Installed / activated state for a table row.
	 *
	 * @since [version]
	 *
	 * @param string           $registered_id Integration or gateway id.
	 * @param LLMS_Add_On|null $addon         Matching catalog add-on.
	 * @param bool             $fallback      Installed fallback when there is no catalog add-on or WP plugin file.
	 * @return array{installed:bool,activated:bool}
	 */
	public static function get_row_plugin_state( $registered_id, $addon = null, $fallback = false ) {

		if ( $addon ) {
			return array(
				'installed' => $addon->is_installed(),
				'activated' => $addon->is_active(),
			);
		}

		$file = self::get_core_dependency_file( $registered_id );
		if ( $file ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			return array(
				'installed' => array_key_exists( $file, get_plugins() ),
				'activated' => is_plugin_active( $file ),
			);
		}

		return array(
			'installed' => (bool) $fallback,
			'activated' => (bool) $fallback,
		);
	}

	/**
	 * Render a status checkmark or an em dash.
	 *
	 * @since [version]
	 *
	 * @param bool   $is_yes Whether the status is affirmative.
	 * @param string $label  Screen-reader / tooltip label.
	 * @return void
	 */
	public static function render_status_icon( $is_yes, $label ) {
		?>
		<td class="status enabled">
			<?php if ( $is_yes ) : ?>
				<span class="tip--bottom-right" data-tip="<?php echo esc_attr( $label ); ?>">
					<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
					<i class="fa fa-check-circle" aria-hidden="true"></i>
				</span>
			<?php else : ?>
				&ndash;
			<?php endif; ?>
		</td>
		<?php
	}

	/**
	 * Render shared table cells for description, install, activation, enabled, and documentation.
	 *
	 * @since [version]
	 *
	 * @param string    $description    Description text (plain).
	 * @param bool      $activated      Whether the plugin is activated.
	 * @param string    $docs_url       Documentation URL.
	 * @param bool      $installed      Whether the plugin is present on disk.
	 * @param string    $learn_more_url Product URL used when the plugin is not installed.
	 * @param bool|null $enabled        Whether the integration/gateway is enabled. Null shows an em dash.
	 * @return void
	 */
	public static function render_status_cells( $description, $activated, $docs_url, $installed = false, $learn_more_url = '', $enabled = null ) {
		?>
		<td><?php echo esc_html( $description ); ?></td>
		<td class="status installed">
			<?php if ( $installed ) : ?>
				<span class="tip--bottom-right" data-tip="<?php esc_attr_e( 'Installed', 'lifterlms' ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Installed', 'lifterlms' ); ?></span>
					<i class="fa fa-check-circle" aria-hidden="true"></i>
				</span>
			<?php elseif ( $learn_more_url ) : ?>
				<a href="<?php echo esc_url( $learn_more_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Learn More', 'lifterlms' ); ?>
				</a>
			<?php else : ?>
				&ndash;
			<?php endif; ?>
		</td>
		<?php
		self::render_status_icon( $activated, __( 'Activated', 'lifterlms' ) );
		self::render_status_icon( (bool) $enabled, __( 'Enabled', 'lifterlms' ) );
		?>
		<td>
			<?php if ( $docs_url ) : ?>
				<a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View Docs', 'lifterlms' ); ?>
				</a>
			<?php else : ?>
				&ndash;
			<?php endif; ?>
		</td>
		<?php
	}
}
