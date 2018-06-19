<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Add-On browser
 * This is where the adds are, if you don't like it that's okay but i don't want to hear your complaints!
 * @since    3.5.0
 * @version  [version]
 */
class LLMS_Admin_AddOns {

	public function get_addon_status( $addon, $translate = false ) {

		$type = $this->get_addon_type( $addon );
		$installed = get_plugins();

		$ret = 'none';
		if ( 'plugin' === $type ) {

			$ret = 'inactive';
			// not installed
			if ( ! in_array( $addon['update_file'], array_keys( $installed ) ) ) {
				$ret = 'none';
			} elseif ( is_plugin_active( $addon['update_file'] ) ) {
				$ret = 'active';
			}

		}

		if ( $translate ) {
			$ret = $this->get_l10n( $ret );
		}

		return $ret;

	}

	public function get_addon_type( $addon ) {

		if ( llms_parse_bool( $addon['has_license'] ) ) {

			$cats = array_keys( $addon['categories'] );
			$type = 'plugin';
			if ( in_array( 'bundles', $cats ) ) {
				$type = 'bundle';
			} elseif( in_array( 'third-party', $cats ) ) {
				$type = 'external';
			} elseif ( in_array( 'themes', $cats ) ) {
				$type = 'theme';
			}

			return $type;

		}

		return false;

	}

	public function get_l10n( $status ) {

		$statuses = array(
			'activate' => __( 'Activate', 'lifterlms' ),
			'active' => __( 'Active', 'lifterlms' ),
			'deactivate' => __( 'Deactivate', 'lifterlms' ),
			'inactive' => __( 'Inactive', 'lifterlms' ),
			'install' => __( 'Install', 'lifterlms' ),
			'none' => __( 'Not Installed', 'lifterlms' ),
		);

		return $statuses[ $status ];

	}

	private function get_addon_status_action( $addon ) {

		$status = $this->get_addon_status( $addon );

		$actions = array(
			'active' => 'deactivate',
			'inactive' => 'activate',
			'none' => 'install',
		);

		return $actions[ $status ];

	}

	/**
	 * Get the current section from the query string
	 * defaults to "all"
	 * @return   string
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function get_current_section() {
		if ( isset( $_GET['section'] ) ) {
			return $_GET['section'];
		} elseif ( count( $this->upgrader->get_license_keys() ) ) {
			return 'mine';
		}
		return 'all';
	}

	/**
	 * Retrieve addon data for the current section (tab) based off query string variables
	 * @return   array
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function get_current_section_content() {

		$sec = $this->get_current_section();

		if ( 'all' === $sec ) {

			$content = $this->data['items'];

		} elseif ( 'mine' === $sec ) {

			$content = array();
			$mine = $this->upgrader->get_available_products();
			foreach ( $this->data['items'] as $item ) {
				if ( in_array( $item['update_file'], $mine ) ) {
					$content[] = $item;
				}
			}

		} else {

			$content = array();
			foreach ( $this->data['items'] as $item ) {

				if ( in_array( $sec, array_keys( $item['categories'] ) ) ) {
					$content[] = $item;
				}

			}

		}

		return $content;
	}

	/**
	 * Retrieve remote json data
	 * @return   null|WP_Error
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function get_data() {

		$this->data = $this->upgrader->get_products();

	}

	/**
	 * Handle form submissions for managing license keys
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function handle_actions() {

		$this->upgrader = LLMS_AddOn_Upgrader::instance();

		if ( ! llms_verify_nonce( '_llms_manage_keys_nonce', 'llms_manage_keys' ) && ! llms_verify_nonce( '_llms_manage_addon_nonce', 'llms_manage_addon' ) ) {
			return;
		}

		if ( isset( $_POST['llms_addon'] ) ) {

			$this->handle_manage_addon( $_POST['llms_addon'] );

		} elseif ( isset( $_POST['llms_activate_keys'] ) && ! empty( $_POST['llms_add_keys'] ) ) {

			$this->handle_activations();
			LLMS_Admin_Notices::output_notices();

		} elseif ( isset( $_POST['llms_deactivate_keys'] ) && ! empty( $_POST['llms_remove_keys'] ) ) {

			$this->handle_deactivations();
			LLMS_Admin_Notices::output_notices();

		}

	}

	/**
	 * Activate license keys with LifterLMS.com api
	 * Output errors / successes & saves successful keys to the db
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	private function handle_activations() {

		$res = $this->upgrader->activate_keys( $_POST['llms_add_keys'] );

		if ( is_wp_error( $res ) ) {
			LLMS_Admin_Notices::flash_notice( $res->get_error_message(), 'error' );
			return;
		}

		$data = $res['data'];
		if ( isset( $data['errors'] ) ) {
			foreach ( $data['errors'] as $error ) {
				LLMS_Admin_Notices::flash_notice( make_clickable( $error ), 'error' );
			}
		}

		if ( isset( $data['activations'] ) ) {
			foreach ( $data['activations'] as $activation ) {
				$this->upgrader->add_license_key( $activation['license_key'], $activation['update_key'], $activation['addons'] );
				LLMS_Admin_Notices::flash_notice( sprintf( '"%s" has been saved!', $activation['license_key'] ), 'success' );
			}
		}

	}

	/**
	 * Deactivate license keys with LifterLMS.com api
	 * Output errors / successes & removes keys from the db
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	private function handle_deactivations() {

		$res = $this->upgrader->deactivate_keys( $_POST['llms_remove_keys'] );

		if ( is_wp_error( $res ) ) {
			LLMS_Admin_Notices::flash_notice( $res->get_error_message(), 'error' );
			return;
		}

		foreach ( $_POST['llms_remove_keys'] as $key ) {
			$this->upgrader->remove_license_key( $key );
			/* Translators: %s = License Key */
			LLMS_Admin_Notices::flash_notice( sprintf( __( 'License key "%s" was removed from this site.', 'lifterlms' ), $key ), 'info' );
		}

		if ( isset( $data['errors'] ) ) {
			foreach ( $data['errors'] as $error ) {
				LLMS_Admin_Notices::flash_notice( make_clickable( $error ), 'error' );
			}
		}

	}

	private function handle_manage_addon( $addon_id ) {

		$addon = $this->upgrader->get_product_data_by( 'id', $addon_id );
		$action = $this->get_addon_status_action( $addon );
		if ( 'activate' === $action ) {
			activate_plugins( $addon['update_file'] );
		} elseif ( 'deactivate' === $action ) {
			deactivate_plugins( $addon['update_file'] );
		} elseif ( 'install' === $action ) {

			// if creds are required to access file system show an error
			ob_start();
			$creds = request_filesystem_credentials( '', '', false, false, null );
			ob_get_clean();
			if ( false === $creds ) {
				LLMS_Admin_Notices::flash_notice( __( 'Unable to install plugin automatically. Please install the addon via FTP or via the Plugins installation screen.', 'lifterlms' ), 'error' );
				return;
			}

			// install the plugin
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			$installer = $installer = new Plugin_Upgrader();
			$installer->install( $download_url );

		}

	}

	/**
	 * Output HTML for the current screen
	 * @return   void
	 * @since    3.5.0
	 * @version  [version]
	 */
	public function output() {

		if ( is_wp_error( $this->get_data() ) ) {
			_e( 'There was an error retrieving add-ons. Please try again.', 'lifterlms' );
			return;
		}

		$my_keys = $this->upgrader->get_license_keys();

		?>
		<div class="wrap lifterlms lifterlms-settings lifterlms-addons">
			<h1 style="display:none;"></h1><!-- error holder -->
			<h1><?php _e( 'LifterLMS Add-Ons', 'lifterlms' ); ?></h1>
			<section class="llms-licenses">
				<button class="llms-button-primary" id="llms-active-keys-toggle">
					<?php _e( 'My License Keys', 'lifterlms' ); ?>
					<i class="fa fa-chevron-down" aria-hidden="true"></i>
				</button>
				<form action="" class="llms-key-field" id="llms-key-field-form" method="POST">

					<?php if ( $my_keys ) : ?>
						<h4 class="llms-license-header"><?php _e( 'Manage Saved License Keys', 'lifterlms' ); ?></h4>
						<ul class="llms-active-keys">
						<?php foreach ( $my_keys as $key ) : ?>
							<li>
								<label for="llms_key_<?php echo esc_attr( $key['license_key'] ); ?>">
									<input id="llms_key_<?php echo esc_attr( $key['license_key'] ); ?>" name="llms_remove_keys[]" type="checkbox" value="<?php echo esc_attr( $key['license_key'] ); ?>">
									<span><?php echo $key['license_key']; ?></span>
								</label>
							</li>

						<?php endforeach; ?>
						</ul>
						<button class="llms-button-danger small" name="llms_deactivate_keys" type="submit"><?php _e( 'Remove Selected', 'lifterlms' ); ?></button>
					<?php endif; ?>

					<label for="llms_keys_field">
						<h4 class="llms-license-header"><?php _e( 'Add New License Keys', 'lifterlms' ); ?></h4>
						<textarea name="llms_add_keys" id="llms_keys_field" placeholder="<?php esc_attr_e( 'Enter each license on a new line', 'lifterlms' ); ?>"></textarea>
					</label>
					<button class="llms-button-primary small" name="llms_activate_keys" type="submit"><?php _e( 'Add New', 'lifterlms' ); ?></button>
					<?php wp_nonce_field( 'llms_manage_keys', '_llms_manage_keys_nonce' ); ?>
				</form>
			</section>

			<?php $this->output_navigation(); ?>
			<?php $this->output_content(); ?>
		</div>
		<script>
			( function( $ ) {
				$( '#llms-active-keys-toggle' ).on( 'click', function() {
					$( '#llms-key-field-form' ).toggle();
				} );
			} )( jQuery );
		</script>
		<?php
	}

	/**
	 * Output HTML for a single addon
	 * @param    array   $addon  associative array of add-on data
	 * @return   void
	 * @since    3.5.0
	 * @version  3.7.6
	 */
	private function output_addon( $addon ) {
		$AddOns = $this;
		include 'views/addons/addon-item.php';
	}

	/**
	 * Output the addon list for the current section
	 * @return   void
	 * @since    3.5.0
	 * @version  3.7.6
	 */
	private function output_content() {

		$addons = $this->get_current_section_content();
		?>
		<form action="" method="POST">
			<ul class="llms-addons-wrap">

				<?php do_action( 'lifterlms_before_addons' ); ?>

				<?php foreach ( $addons as $addon ) {
					$this->output_addon( $addon );
				} ?>

				<?php do_action( 'lifterlms_after_addons' ); ?>

			</ul>
			<?php wp_nonce_field( 'llms_manage_addon', '_llms_manage_addon_nonce' ); ?>
		</form>
		<?php
	}

	/**
	 * Outputs most popular resources
	 * used on general settings screen
	 * @return   void
	 * @since    3.7.6
	 * @version  3.7.6
	 */
	public function output_for_settings() {

		if ( is_wp_error( $this->get_data() ) ) {

			_e( 'There was an error retrieving add-ons. Please try again.', 'lifterlms' );
			return;

		}
		$this->output_content( true );

	}

	/**
	 * Output the navigation bar
	 * @return   void
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function output_navigation() {

		$mine = count( $this->upgrader->get_license_keys() ) ? true : false;
		?>
		<nav class="llms-nav-tab-wrapper llms-nav-text">
			<ul class="llms-nav-items">
			<?php do_action( 'lifterlms_before_addons_nav' ); ?>

				<?php if ( $mine ) : ?>
					<li class="llms-nav-item<?php echo ( 'mine' === $this->get_current_section() ) ? ' llms-active' : ''; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=mine' ) ); ?>"><?php _e( 'My Add-Ons', 'lifterlms' ); ?></a></li>
				<?php endif; ?>
				<li class="llms-nav-item<?php echo ( 'all' === $this->get_current_section() ) ? ' llms-active' : ''; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=all' ) ); ?>"><?php _e( 'All', 'lifterlms' ); ?></a></li>
				<?php foreach ( $this->data['categories'] as $name => $title ) :
					$name = sanitize_title( $name );
					$title = sanitize_text_field( $title );
					$active = ( $this->get_current_section() === $name ) ? ' llms-active' : ''; ?>
					<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=' . $name ) ); ?>"><?php echo $title; ?></a></li>
				<?php endforeach; ?>

			<?php do_action( 'lifterlms_after_addons_nav' ); ?>
			</ul>
		</nav>
		<?php
	}

}
