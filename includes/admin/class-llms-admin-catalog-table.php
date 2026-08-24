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
	 * Catalog IDs already represented by registered objects.
	 *
	 * @since [version]
	 *
	 * @param string[] $registered_ids Integration or gateway ids.
	 * @param string   $screen         Screen id.
	 * @return string[]
	 */
	public static function get_matched_catalog_ids( $registered_ids, $screen ) {

		$matched = array();
		foreach ( self::get_catalog_addons( $screen ) as $addon ) {
			foreach ( $registered_ids as $registered_id ) {
				if ( self::addon_matches_registered_id( $addon, $registered_id ) ) {
					$matched[] = $addon->get( 'id' );
					break;
				}
			}
		}

		return $matched;
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
	 * Render shared table cells for description, activated, and documentation.
	 *
	 * @since [version]
	 *
	 * @param string $description Description text (plain).
	 * @param bool   $activated   Whether the row is activated.
	 * @param string $docs_url    Documentation URL.
	 * @return void
	 */
	public static function render_status_cells( $description, $activated, $docs_url ) {
		?>
		<td><?php echo esc_html( $description ); ?></td>
		<td class="status enabled">
			<?php if ( $activated ) : ?>
				<span class="tip--bottom-right" data-tip="<?php esc_attr_e( 'Activated', 'lifterlms' ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Activated', 'lifterlms' ); ?></span>
					<i class="fa fa-check-circle" aria-hidden="true"></i>
				</span>
			<?php else : ?>
				&ndash;
			<?php endif; ?>
		</td>
		<td>
			<?php if ( $docs_url ) : ?>
				<a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Docs', 'lifterlms' ); ?>
				</a>
			<?php else : ?>
				&ndash;
			<?php endif; ?>
		</td>
		<?php
	}
}
