<?php
/**
 * LifterLMS Add-On browser
 * This is where the adds are, if you don't like it that's okay but i don't want to hear your complaints!
 *
 * @since 3.5.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_AddOns class.
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
	 * Get the current section from the query string
	 * defaults to "all"
	 *
	 * @return   string
	 * @since    3.5.0
	 * @version  3.22.0
	 */
	private function get_current_section() {

		$section = 'all';

		if ( isset( $_GET['page'] ) && 'llms-settings' === $_GET['page'] ) {
			$section = 'featured';
		} elseif ( isset( $_GET['section'] ) ) {
			$section = llms_filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
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
				$content = $this->data['items'];
			} elseif ( 'featured' === $sec ) {
				$content = $this->get_features();
			} else {
				$content = $this->get_products_for_cat( $sec );
			}
		}

		return apply_filters( 'llms_admin_add_ons_get_current_section_content', $content, $sec );
	}

	/**
	 * Retrieve remote json data
	 *
	 * @return   null|WP_Error
	 * @since    3.5.0
	 * @version  3.22.2
	 */
	private function get_data() {

		$this->data = llms_get_add_ons();

		if ( ! is_wp_error( $this->data ) ) {

			foreach ( $this->data['items'] as $key => $addon ) {
				if ( in_array( $addon['id'], array( 'lifterlms-com-lifterlms', 'lifterlms-com-lifterlms-helper' ) ) ) {
					unset( $this->data['items'][ $key ] );
				}
			}
		}

		return $this->data;

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

		// addons to exclude
		// helper will filter this based on existing activations
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
	 * Get a random product from a category that doesn't exist in the list of excluded product ids
	 *
	 * @param    string $cat       category slug
	 * @param    array  $excludes  list of product ids to exclude
	 * @return   array|false
	 * @since    3.22.0
	 * @version  3.22.0
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
	 *
	 * @param    string $cat  category slug
	 * @return   array
	 * @since    3.22.0
	 * @version  3.22.0
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
	 *
	 * @return   void
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function handle_actions() {

		// activate & deactivate addons
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
	 *
	 * @return   void
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

			foreach ( llms_filter_input( INPUT_POST, 'llms_' . $action, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY ) as $id ) {

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
	 * @return   void
	 * @since    3.5.0
	 * @version  3.28.0
	 */
	public function output() {

		if ( is_wp_error( $this->get_data() ) ) {
			_e( 'There was an error retrieving add-ons. Please try again.', 'lifterlms' );
			return;
		}
		?>
		<div class="wrap lifterlms lifterlms-settings lifterlms-addons">

			<h1 class="wp-heading-inline"><?php _e( 'LifterLMS Add-Ons, Courses, and Resources', 'lifterlms' ); ?></h1>
			<?php do_action( 'llms_addons_page_after_title' ); ?>
			<h1 class="screen-reader-text"><?php _e( 'LifterLMS Add-Ons, Courses, and Resources', 'lifterlms' ); ?></h1>

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

			_e( 'There was an error retrieving add-ons. Please try again.', 'lifterlms' );
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
		<nav class="llms-nav-tab-wrapper llms-nav-text">
			<ul class="llms-nav-items">
			<?php do_action( 'lifterlms_before_addons_nav', $curr_section ); ?>
				<li class="llms-nav-item<?php echo ( 'all' === $curr_section ) ? ' llms-active' : ''; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=all' ) ); ?>"><?php _e( 'All', 'lifterlms' ); ?></a></li>
				<?php
				foreach ( $this->data['categories'] as $name => $title ) :
					$name   = sanitize_title( $name );
					$title  = sanitize_text_field( $title );
					$active = ( $this->get_current_section() === $name ) ? ' llms-active' : '';
					?>
					<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=' . $name ) ); ?>"><?php echo $title; ?></a></li>
				<?php endforeach; ?>

			<?php do_action( 'lifterlms_after_addons_nav', $curr_section ); ?>
			</ul>
		</nav>
		<?php
	}

}
