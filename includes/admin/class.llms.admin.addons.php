<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * LifterLMS Add-On browser
 * This is where the adds are, if you don't like it that's okay but i don't want to hear your complaints!
 * @since    3.5.0
 * @version  [version]
 */
class LLMS_Admin_AddOns {

	/**
	 * Url Where addon JSON information is pulled from
	 */
	// const DATA_URL = 'http://d34dpc7391qduo.cloudfront.net/addons/addons.json';

	/**
	 * This URL is good for development since it wont be cached as hard
	 */
	// const DATA_URL = 'https://s3-us-west-2.amazonaws.com/lifterlms/addons/addons.json';

	const DATA_URL = 'https://dev.lifterlms.com/wp-json/llms/v3/products';

	public function activate( $key ) {

		$data = array(
			'activations' => array(
				'key' => $key,
				'product' => '',
				'url' => get_site_url(),
			),
		);

		// https://lifterlms.com/wp-json/llms-api/v2


	}

	public function get_addon_status( $addon, $translate = false ) {

		$type = $this->get_addon_type( $addon );
		$installed = get_plugins();

		$ret = 'none';
		if ( 'plugin' === $type ) {

			// not installed
			if ( ! in_array( $addon['update_file'], array_keys( $installed ) ) ) {
				$ret = 'none';
			} elseif ( is_plugin_active( $file ) ) {
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
	 * @version  3.5.0
	 */
	private function get_current_section() {
		return isset( $_GET['section'] ) ? $_GET['section'] : 'all';
	}

	/**
	 * Retrieve addon data for the current section (tab) based off query string variables
	 * @return   array
	 * @since    3.5.0
	 * @version  3.5.0
	 */
	private function get_current_section_content() {

		$sec = $this->get_current_section();

		if ( 'all' === $sec ) {
			$content = $this->data['items'];
		} else {

			$content = $this->data['sections'][ $sec ];

		}

		return $content;
	}

	/**
	 * Retrieve remote json data
	 * @return   null|WP_Error
	 * @since    3.5.0
	 * @version  3.5.0
	 */
	private function get_data() {

		$get = wp_remote_get( self::DATA_URL, array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( 'llms:B1ncrN2TMvMqgBHBjv0412mLOg5SCs8q' ),
			),
		) );

		if ( is_wp_error( $get ) ) {
			return $get;
		}

		$this->data = json_decode( $get['body'], true );

	}

	/**
	 * Translate the title of the current section
	 * @param    sring     $name  section name (untranslated key)
	 * @return   string
	 * @since    3.5.0
	 * @version  [version]
	 */
	private function get_section_title( $name ) {

		$titles = array(
			'advanced' => __( 'Advanced', 'lifterlms' ),
			'affiliates' => __( 'Affiliates', 'lifterlms' ),
			'all' => __( 'All', 'lifterlms' ),
			'bundles' => __( 'Bundles', 'lifterlms' ),
			'gateways' => __( 'Payment Gateways', 'lifterlms' ),
			'marketing' => __( 'E-Mail & Marketing', 'lifterlms' ),
			'themes' => __( 'Themes & Design', 'lifterlms' ),
			'tools' => __( 'Tools & Utilities', 'lifterlms' ),
			'resources' => __( 'Resources', 'lifterlms' ),
			'services' => __( 'Services', 'lifterlms' ),
			'my_addons' => __( 'My Add-ons', 'lifterlms' ),
		);

		if ( isset( $titles[ $name ] ) ) {
			return $titles[ $name ];
		}

		return $name;
	}

	public function handle_actions() {

		if ( ! llms_verify_nonce( '_llms_activate_nonce', 'llms_activate_key' ) ) {
			return;
		}

		if ( isset( $_POST['llms_key'] ) ) {

			$status = $this->activate( sanitize_text_field( $_POST['llms_key'] ) );

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
				<?php _e( 'Activate a License Key', 'lifterlms' ); ?>
				<i class="fa fa-chevron-down" aria-hidden="true"></i>
				<form action="" class="llms-key-field" method="POST">
					<input name="llms_key" type="text">
					<button class="llms-button-primary small" name="llms_activate_key" type="submit"><?php _e( 'Submit', 'lifterlms' ); ?></button>
					<?php wp_nonce_field( 'llms_activate_key', '_llms_activate_nonce' ); ?>
				</form>
			</section>
			<?php $this->output_navigation(); ?>
			<?php $this->output_content(); ?>
		</div>
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
		?>
		<nav class="llms-nav-tab-wrapper llms-nav-text">
			<ul class="llms-nav-items">
			<?php do_action( 'lifterlms_before_addons_nav' ); ?>

				<?php $active = ( 'all' === $this->get_current_section() ) ? ' llms-active' : ''; ?>
				<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=all' ) ); ?>"><?php _e( 'All', 'lifterlms' ); ?></a></li>
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
