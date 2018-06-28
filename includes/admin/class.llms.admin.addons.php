<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Add-On browser
 * This is where the adds are, if you don't like it that's okay but i don't want to hear your complaints!
 * @since    3.5.0
 * @version  [version]
 */
class LLMS_Admin_AddOns {

	/**
	 * Get the current section from the query string
	 * defaults to "all"
	 * @return   string
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function get_current_section() {
		if ( isset( $_GET['page'] ) && 'llms-settings' === $_GET['page'] ) {
			return 'featured';
		} elseif ( isset( $_GET['section'] ) ) {
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
				if ( in_array( $item['id'], $mine ) ) {
					$content[] = $item;
				}
			}

		} elseif ( 'featured' === $sec ) {
			$content = $this->get_features();
		} else {
			$content = $this->get_products_for_cat( $sec );
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

		$this->upgrader = LLMS_AddOn_Upgrader::instance();
		$this->data = $this->upgrader->get_products();
		foreach ( $this->data['items'] as $key => $addon ) {
			if ( 'lifterlms-com-lifterlms' === $addon['id'] ) {
				unset( $this->data['items'][ $key ] );
			}
		}

	}

	/**
	 * Retrieve a list of 'featured' addons for use on the general settings screen
	 * Excludes already available products from current site's activations
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_features() {

		$features = array();

		// exclude products already installed
		$exclude = $this->upgrader->get_available_products( false );

		$cats = array(
			'e-commerce',
			'bundles',
			'resources',
			'courses',
			'courses',
		);

		foreach ( $cats as $cat ) {
			$addon = $this->get_product_from_cat( $cat, $exclude );
			if ( $addon ) {
				$features[] = $addon;
				$exclude[] = $addon['id'];
			}
			if ( 3 === count( $features ) ) {
				return $features;
			}
		}

		return $features;

	}

	/**
	 * Get a random product from a category that doensn't exist in the list of excluded product ids
	 * @param    string     $cat       category slug
	 * @param    array      $excludes  list of product ids to exclude
	 * @return   array|false
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_product_from_cat( $cat, $excludes ) {

		$addons = $this->get_products_for_cat( $cat, true );
		shuffle( $addons );

		foreach ( $addons as $addon ) {

			if ( in_array( 'third-party', array_keys( $addon['categories'] ) ) ) {
				continue;
			}

			if ( ! in_array( $addon['id'], $excludes ) ) {
				return $addon;
			}

		}

		return false;

	}

	/**
	 * Retrieve products for a specific category
	 * @param    string     $cat  category slug
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_products_for_cat( $cat, $include_bundles = true ) {

		$products = array();

		foreach ( $this->data['items'] as $item ) {

			$cats = array_keys( $item['categories'] );

			// exclude bundles if bundles are not being included or requested
			if ( 'bundles' !== $cat && ! $include_bundles && in_array( 'bundles', $cats ) ) {
				continue;
			}

			if ( in_array( $cat, $cats ) ) {
				$products[] = $item;
			}
		}

		return $products;

	}

	/**
	 * Handle form submissions for managing license keys
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function handle_actions() {

		$this->upgrader = LLMS_AddOn_Upgrader::instance();

		// manage addons
		// activate & deactivate, install, update
		if ( llms_verify_nonce( '_llms_manage_addon_nonce', 'llms_manage_addon' ) ) {

			$this->handle_manage_addons();
			LLMS_Admin_Notices::output_notices();
		}

		// license key addition & removal
		if ( llms_verify_nonce( '_llms_manage_keys_nonce', 'llms_manage_keys' ) ) {

			if ( isset( $_POST['llms_activate_keys'] ) && ! empty( $_POST['llms_add_keys'] ) ) {

				$this->handle_activations();

			} elseif ( isset( $_POST['llms_deactivate_keys'] ) && ! empty( $_POST['llms_remove_keys'] ) ) {

				$this->handle_deactivations();

			}

			delete_site_transient( 'update_plugins' );
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
				$this->upgrader->add_license_key( $activation );
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

	/**
	 * Handle activation, deactivation, and cloud installation of addons
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	private function handle_manage_addons() {

		$actions = array(
			'update',
			'install',
			'activate',
			'deactivate',
		);

		$errors = array();
		$success = array();

		foreach ( $actions as $action ) {

			if ( empty( $_POST[ 'llms_' . $action ] ) ) {
				continue;
			}

			foreach ( $_POST[ 'llms_' . $action ] as $id ) {

				$addon = new LLMS_Add_On( $id );
				if ( ! method_exists( $addon, $action ) ) {
					continue;
				}

				$ret = call_user_func( array( $addon, $action ) );
				if ( is_wp_error( $ret ) ) {
					LLMS_Admin_Notices::flash_notice( $ret->get_error_message(), 'error' );
				} else {
					LLMS_Admin_Notices::flash_notice( $ret );
				}
			}
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
		if ( $my_keys ) {
			wp_enqueue_style( 'plugin-install' );
			wp_enqueue_script( 'plugin-install' );
			add_thickbox();
		}
		?>
		<div class="wrap lifterlms lifterlms-settings lifterlms-addons">
			<h1 style="display:none;"></h1><!-- error holder -->
			<h1><?php _e( 'LifterLMS Add-Ons, Courses, and Resources', 'lifterlms' ); ?></h1>
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
			<form action="" method="POST">

				<?php $this->output_content(); ?>

				<?php wp_nonce_field( 'llms_manage_addon', '_llms_manage_addon_nonce' ); ?>

				<div class="llms-addons-bulk-actions" id="llms-addons-bulk-actions">

					<a class="llms-bulk-close" href="#">
						<span class="screen-reader-text"><?php _e( 'Close', 'lifterlms' ); ?></span>
						<i class="fa fa-times-circle" aria-hidden="true"></i>
					</a>

					<div class="llms-bulk-desc update">
						<i class="fa fa-cloud-download" aria-hidden="true"></i>
						<?php _e( 'Update', 'lifterlms' ); ?> <span></span>
					</div>

					<div class="llms-bulk-desc install">
						<i class="fa fa-cloud-download" aria-hidden="true"></i>
						<?php _e( 'Install', 'lifterlms' ); ?> <span></span>
					</div>

					<div class="llms-bulk-desc activate">
						<i class="fa fa-plug" aria-hidden="true"></i>
						<?php _e( 'Activate', 'lifterlms' ); ?> <span></span>
					</div>

					<div class="llms-bulk-desc deactivate">
						<i class="fa fa-plug" aria-hidden="true"></i>
						<?php _e( 'Deactivate', 'lifterlms' ); ?> <span></span>
					</div>

					<button class="llms-button-primary" name="llms_bulk_actions_submit" value="" type="submit"><?php _e( 'Apply', 'lifterlms' ); ?></button>
				</div>

			</form>
		</div>
		<?php
	}

	/**
	 * Output HTML for a single addon
	 * @param    array   $addon  associative array of add-on data
	 * @return   void
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function output_addon( $addon ) {
		$current_tab = $this->get_current_section();
		include 'views/addons/addon-item.php';
	}

	/**
	 * Output the addon list for the current section
	 * @return   void
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function output_content() {
		?>
		<ul class="llms-addons-wrap section--<?php echo esc_attr( $this->get_current_section() ); ?>">

			<?php do_action( 'lifterlms_before_addons' ); ?>

			<?php
			foreach ( $this->get_current_section_content() as $addon ) {
				$addon = new LLMS_Add_On( $addon );
				$this->output_addon( $addon );
			}
			?>

			<?php do_action( 'lifterlms_after_addons' ); ?>

		</ul>
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
		$this->output_content();

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
