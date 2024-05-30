<?php
/**
 * LifterLMS Add-On browser
 *
 * This is where the adds are, if you don't like it that's okay but i don't want to hear your complaints!
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 3.5.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_AddOns class
 *
 * @since 3.5.0
 * @since 3.30.3 Explicitly define undefined properties.
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Admin_AddOns {

	/**
	 * Data from `llms_get_add_ons()`.
	 *
	 * @var array
	 * @since 3.5.0
	 */
	public $data = array();

	/**
	 * Retrieves the current section from the query string.
	 *
	 * @since 3.5.0
	 * @since 3.22.0 Unknown.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return string
	 */
	private function get_current_section() {

		$section = 'all';

		if ( isset( $_GET['page'] ) && 'llms-dashboard' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$section = 'featured';
		} elseif ( isset( $_GET['section'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$section = llms_filter_input_sanitize_string( INPUT_GET, 'section' );
		}

		return apply_filters( 'llms_admin_add_ons_get_current_section', $section );
	}

	/**
	 * Retrieve addon data for the current section (tab) based off query string variables
	 *
	 * @return   array
	 * @since    3.5.0
	 * @version  3.22.0
	 */
	private function get_current_section_content() {

		$sec = $this->get_current_section();

		$content = apply_filters( 'llms_admin_add_ons_get_current_section_default_content', array(), $sec );

		if ( ! $content ) {

			if ( 'all' === $sec ) {
				$content = $this->get_all();
			} elseif ( 'featured' === $sec ) {
				$content = $this->get_features();
			} else {
				$content = $this->get_products_for_cat( $sec );
			}
		}

		return apply_filters( 'llms_admin_add_ons_get_current_section_content', $content, $sec );
	}

	/**
	 * Retrieve remote json data.
	 *
	 * @since 3.5.0
	 * @since 3.22.2 Unknown.
	 * @since 7.1.0 Use strict comparisons for `in_array()`.
	 *
	 * @return array|WP_Error
	 */
	private function get_data() {

		$this->data = llms_get_add_ons();

		if ( ! is_wp_error( $this->data ) ) {
			foreach ( $this->data['items'] as $key => $addon ) {
				// Exclude the core plugin and helper plugin.
				if ( in_array( $addon['id'], array( 'lifterlms-com-lifterlms', 'lifterlms-com-lifterlms-helper' ), true ) ) {
					unset( $this->data['items'][ $key ] );
				}

				// Exclude uncategorized Add-ons.
				if ( array_key_exists( 'uncategorized', $addon['categories'] ) ) {
					unset( $this->data['items'][ $key ] );
				}
			}
		}

		return $this->data;
	}

	/**
	 * Retrieve a list of addons for use on the All section
	 *
	 * @return   array
	 * @since    7.5.0
	 * @version  7.5.0
	 */
	private function get_all() {

		$all = array();

		$addons = $this->data['items'];

		foreach ( $addons as $addon ) {
			// Exclude third-party Addons from the All section.
			if ( in_array( 'third-party', array_keys( $addon['categories'] ), true ) ) {
					continue;
			}
			$all[] = $addon;
		}

		return $all;
	}

	/**
	 * Retrieve a list of 'featured' addons for use on the general settings screen
	 * Excludes already available products from current site's activations
	 *
	 * @return   array
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	private function get_features() {

		$features = array();

		// Addons to exclude.
		// Helper will filter this based on existing activations.
		$exclude = apply_filters( 'llms_admin_addon_features_exclude_ids', array() );

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
				$exclude[]  = $addon['id'];
			}
			if ( 3 === count( $features ) ) {
				return $features;
			}
		}

		return $features;
	}

	/**
	 * Get a random product from a category that doesn't exist in the list of excluded product ids.
	 *
	 * @since 3.22.0
	 * @since 7.1.0 Use strict comparisons for `in_array()`.
	 *
	 * @param  string $cat      Category slug.
	 * @param  array  $excludes List of product ids to exclude.
	 * @return array|false
	 */
	public function get_product_from_cat( $cat, $excludes ) {

		$addons = $this->get_products_for_cat( $cat, true );
		shuffle( $addons );

		foreach ( $addons as $addon ) {

			if ( in_array( 'third-party', array_keys( $addon['categories'] ), true ) ) {
				continue;
			}

			if ( ! in_array( $addon['id'], $excludes, true ) ) {
				return $addon;
			}
		}

		return false;
	}

	/**
	 * Retrieve products for a specific category.
	 *
	 * @since 3.22.0
	 * @since 7.1.0 Use strict comparisons for `in_array()`.
	 *
	 * @param string $cat Category slug.
	 * @return array
	 */
	private function get_products_for_cat( $cat, $include_bundles = true ) {

		$products = array();

		foreach ( $this->data['items'] as $item ) {

			$cats = array_keys( $item['categories'] );

			// Exclude bundles if bundles are not being included or requested.
			if ( 'bundles' !== $cat && ! $include_bundles && in_array( 'bundles', $cats, true ) ) {
				continue;
			}

			if ( in_array( $cat, $cats, true ) ) {
				$products[] = $item;
			}
		}

		return $products;
	}

	/**
	 * Handle form submissions for managing license keys
	 *
	 * @return   void
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function handle_actions() {

		// Activate & deactivate addons.
		if ( llms_verify_nonce( '_llms_manage_addon_nonce', 'llms_manage_addon' ) ) {

			$this->handle_manage_addons();
			LLMS_Admin_Notices::output_notices();
		}
	}

	/**
	 * Handle activation and deactivation of addons
	 *
	 * @since 3.22.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	private function handle_manage_addons() {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce is verified in $this->handle_actions() method.

		$actions = apply_filters(
			'llms_admin_add_ons_manage_actions',
			array(
				'activate',
				'deactivate',
			)
		);

		$errors  = array();
		$success = array();

		foreach ( $actions as $action ) {

			if ( empty( $_POST[ 'llms_' . $action ] ) ) {
				continue;
			}

			foreach ( llms_filter_input( INPUT_POST, 'llms_' . $action, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) as $id ) {

				$addon = llms_get_add_on( $id );
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

		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Output HTML for the current screen
	 *
	 * @since 3.5.0
	 * @since 3.28.0 Unknown.
	 * @since 4.10.1 Use `hr.wp-header-end` in favor of a second (hidden) <h1> to "catch" admin notices.
	 *
	 * @return void
	 */
	public function output() {

		if ( is_wp_error( $this->get_data() ) ) {
			esc_html_e( 'There was an error retrieving add-ons. Please try again.', 'lifterlms' );
			return;
		}
		?>
		<div class="wrap lifterlms lifterlms-settings lifterlms-addons">

			<div class="llms-subheader">

				<h1><?php esc_html_e( 'LifterLMS Add-Ons, Courses, and Resources', 'lifterlms' ); ?></h1>
				<?php do_action( 'llms_addons_page_after_title' ); ?>

			</div>

			<div class="llms-inside-wrap">

				<?php $this->output_navigation(); ?>

				<hr class="wp-header-end">

				<form action="" method="POST">

					<?php $this->output_content(); ?>

					<?php wp_nonce_field( 'llms_manage_addon', '_llms_manage_addon_nonce' ); ?>

					<div class="llms-addons-bulk-actions" id="llms-addons-bulk-actions">

						<a class="llms-bulk-close" href="#">
							<span class="screen-reader-text"><?php esc_html_e( 'Close', 'lifterlms' ); ?></span>
							<i class="fa fa-times-circle" aria-hidden="true"></i>
						</a>

						<div class="llms-bulk-desc update">
							<i class="fa fa-cloud-download" aria-hidden="true"></i>
							<?php esc_html_e( 'Update', 'lifterlms' ); ?> <span></span>
						</div>

						<div class="llms-bulk-desc install">
							<i class="fa fa-cloud-download" aria-hidden="true"></i>
							<?php esc_html_e( 'Install', 'lifterlms' ); ?> <span></span>
						</div>

						<div class="llms-bulk-desc activate">
							<i class="fa fa-plug" aria-hidden="true"></i>
							<?php esc_html_e( 'Activate', 'lifterlms' ); ?> <span></span>
						</div>

						<div class="llms-bulk-desc deactivate">
							<i class="fa fa-plug" aria-hidden="true"></i>
							<?php esc_html_e( 'Deactivate', 'lifterlms' ); ?> <span></span>
						</div>

						<button class="llms-button-primary" name="llms_bulk_actions_submit" value="" type="submit"><?php esc_html_e( 'Apply', 'lifterlms' ); ?></button>

					</div>

				</form>

			</div>

		</div>

		<?php
	}

	/**
	 * Output HTML for a single addon
	 *
	 * @param    array $addon  associative array of add-on data
	 * @return   void
	 * @since    3.5.0
	 * @version  3.22.0
	 */
	private function output_addon( $addon ) {
		$current_tab = $this->get_current_section();
		include 'views/addons/addon-item.php';
	}

	/**
	 * Output the addon list for the current section
	 *
	 * @return   void
	 * @since    3.5.0
	 * @version  3.22.0
	 */
	private function output_content() {
		?>
		<ul class="llms-addons-wrap section--<?php echo esc_attr( $this->get_current_section() ); ?>">

			<?php do_action( 'lifterlms_before_addons' ); ?>

			<?php
			foreach ( $this->get_current_section_content() as $addon ) {
				$addon = llms_get_add_on( $addon );
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
	 *
	 * @return   void
	 * @since    3.7.6
	 * @version  3.7.6
	 */
	public function output_for_settings() {

		if ( is_wp_error( $this->get_data() ) ) {

			esc_html_e( 'There was an error retrieving add-ons. Please try again.', 'lifterlms' );
			return;

		}
		$this->output_content();
	}

	/**
	 * Output the navigation bar
	 *
	 * @return   void
	 * @since    3.5.0
	 * @version  3.22.0
	 */
	private function output_navigation() {
		$curr_section = $this->get_current_section();
		?>
		<nav class="llms-nav-tab-wrapper llms-nav-secondary">
			<ul class="llms-nav-items">
			<?php do_action( 'lifterlms_before_addons_nav', $curr_section ); ?>
				<?php
				foreach ( $this->data['categories'] as $name => $title ) :
					$name   = sanitize_title( $name );
					$title  = sanitize_text_field( $title );
					$active = ( $this->get_current_section() === $name ) ? ' llms-active' : '';
					?>
					<li class="llms-nav-item<?php echo esc_attr( $active ); ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=' . $name ) ); ?>"><?php echo esc_html( $title ); ?></a></li>
				<?php endforeach; ?>
				<li class="llms-nav-item<?php echo ( 'all' === $curr_section ) ? ' llms-active' : ''; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=all' ) ); ?>"><?php esc_html_e( 'All', 'lifterlms' ); ?></a></li>

			<?php do_action( 'lifterlms_after_addons_nav', $curr_section ); ?>
			</ul>
		</nav>
		<?php
	}
}
