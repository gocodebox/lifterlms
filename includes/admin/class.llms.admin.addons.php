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

			// not installed
			if ( ! in_array( $addon['update_file'], array_keys( $installed ) ) ) {
				$ret = 'none';
			} elseif ( is_plugin_active( $addon['update_file'] ) ) {
				$ret = 'active';
			}
			$ret = 'inactive';

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
			} elseif ( in_array( 'themes', $cats ) ) {
				$type = 'theme';
			}

			return $type;

		}

		return false;

	}

	private function get_l10n( $status ) {

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

	private function get_addon_status_action( $status ) {

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
		} elseif ( count( $this->upgrader->get_available_products() ) ) {
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
			$mine = wp_list_pluck( $this->upgrader->get_available_products(), 'title' );
			$content = array();
			foreach ( $this->data['items'] as $item ) {
				if ( in_array( $item['title'], $mine ) ) {
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

	public function handle_actions() {

		$this->upgrader = new LLMS_AddOn_Upgrader();

		if ( ! llms_verify_nonce( '_llms_activate_nonce', 'llms_activate_key' ) ) {
			return;
		}

		if ( isset( $_POST['llms_keys'] ) ) {

			$body = $this->upgrader->get_products_for_keys( $_POST['llms_keys'] );

			$data = $body['data'];

			if ( isset( $data['errors'] ) ) {

				?>
				<div class="notice notice-error is-dismissible">
					<?php foreach ( $data['errors'] as $error ) : ?>
						<p><?php echo make_clickable( $error ); ?></p>
					<?php endforeach; ?>
				</div>
				<?php

			}

			if ( isset( $data['available_products'] ) ) {

				$this->upgrader->set_available_products( $data['available_products'] );

			}

			if ( isset( $data['valid_keys'] ) ) {

				$this->upgrader->set_keys( $data['valid_keys'] );

			}

			// var_dump( $body );

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

		?>
		<div class="wrap lifterlms lifterlms-settings lifterlms-addons">
			<h1><?php _e( 'LifterLMS Add-Ons', 'lifterlms' ); ?></h1>
			<section class="llms-licenses">
				<button class="llms-button-primary" id="llms-active-keys-toggle">
					<?php _e( 'Activate Licenses', 'lifterlms' ); ?>
					<i class="fa fa-chevron-down" aria-hidden="true"></i>
				</button>
				<form action="" class="llms-key-field" id="llms-key-field-form" method="POST">
					<textarea name="llms_keys" placeholder="<?php esc_attr_e( 'Enter each license on a new line', 'lifterlms' ); ?>"></textarea>
					<button class="llms-button-primary small" name="llms_activate_key" type="submit"><?php _e( 'Submit', 'lifterlms' ); ?></button>
					<?php wp_nonce_field( 'llms_activate_key', '_llms_activate_nonce' ); ?>
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
	 * @param    bool   $featured   if true, only outputs featured addons
	 * @return   void
	 * @since    3.5.0
	 * @version  3.7.6
	 */
	private function output_content( $featured = false ) {

		// if ( 'mine' === $this->get_current_section() ) {
		// 	var_dump( $this->upgrader->get_available_products() );
		// }

		$addons = $this->get_current_section_content();
		?>
		<ul class="llms-addons-wrap">

			<?php do_action( 'lifterlms_before_addons' ); ?>

			<?php foreach ( $addons as $addon ) {

				if ( $featured && ! $addon['featured'] ) {
					continue;
				}

				$this->output_addon( $addon );

			} ?>

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
		$this->output_content( true );

	}

	/**
	 * Output the navigation bar
	 * @return   void
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function output_navigation() {

		$mine = count( $this->upgrader->get_available_products() ) ? true : false;
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
