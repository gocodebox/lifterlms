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
	const DATA_URL = 'https://s3-us-west-2.amazonaws.com/lifterlms/addons/addons.json';

	private function get_addon_status( $file ) {

		if ( is_plugin_active( $file ) ) {
			return 'active';
		} elseif ( is_plugin_inactive( $file ) ) {
			return 'inactive';
		}

		return 'none';

	}

	private function get_addon_status_l10n( $status ) {

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

			$content = array();
			foreach ( $this->data['sections'] as $section ) {
				$content = array_merge( $content, $section );
			}
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

		$get = wp_remote_get( self::DATA_URL );
		if ( is_wp_error( $get ) ) {
			return $get;
		}

		$this->data = json_decode( $get['body'], true );

		// var_dump( $this->data );

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
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'browse';
		?>
		<div class="wrap lifterlms lifterlms-settings">
			<nav class="llms-nav-tab-wrapper">
				<ul class="llms-nav-items">
					<li class="llms-nav-item<?php echo 'browse' === $tab ? ' llms-active' : ''; ?>">
						<a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&tab=browse' ) ); ?>">
							<?php _e( 'Browse Add-ons', 'lifterlms' ); ?></a></li>
					<li class="llms-nav-item<?php echo 'my' === $tab ? ' llms-active' : ''; ?>">
						<a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&tab=my' ) ); ?>">
							<?php _e( 'My Add-ons', 'lifterlms' ); ?></a></li>
				</ul>
			</nav>
			<?php if ( 'browse' === $tab ) : ?>
				<h1><?php _e( 'LifterLMS Add-Ons, Services, and Resources', 'lifterlms' ); ?></h1>
				<?php $this->output_navigation(); ?>
			<?php else : ?>
				<h1><?php _e( 'My Add-Ons', 'lifterlms' ); ?></h1>
			<?php endif; ?>
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
		$featured = $addon['featured'] ? ' featured' : '';
		$status = false;
		if ( isset( $addon['file'] ) ) {
			$status = $this->get_addon_status( $addon['file'] );
			$action = $this->get_addon_status_action( $status );
		}
		?>
		<li class="llms-add-on-item<?php echo $featured; ?>">
			<div class="llms-add-on">
				<a href="<?php echo esc_url( $addon['url'] ); ?>" class="llms-add-on-link">
					<header>
						<img alt="<?php echo $addon['title']; ?> Banner" src="<?php echo esc_url( $addon['image'] ); ?>">
						<h4><?php echo $addon['title']; ?></h4>
					</header>
					<section>
						<p><?php echo $addon['description']; ?></p>
					</section>
					<footer>
						<span><?php _e( 'Created by:', 'lifterlms' ); ?></span>
						<span><?php echo $addon['developer']; ?></span>
						<?php if ( $addon['developer_image'] ) : ?>
							<img alt="<?php echo $addon['developer']; ?> logo" src="<?php echo esc_url( $addon['developer_image'] ); ?>">
						<?php endif; ?>
					</footer>
				</a>
				<?php if ( $status ) : ?>
					<footer class="llms-status">
						<span><?php printf( __( 'Status: %s', 'lifterlms' ), $this->get_addon_status_l10n( $status ) ); ?></span>
						<button class="llms-add-on-button" name="llms-add-on-<?php echo $action; ?>"><?php echo $this->get_addon_status_l10n( $action ); ?></button>
					</footer>
				<?php endif; ?>
			</div>
		</li>
		<?php
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
		<nav class="llms-nav-text-wrapper">
			<ul class="llms-nav-items">
			<?php do_action( 'lifterlms_before_addons_nav' ); ?>

				<?php $active = ( 'all' === $this->get_current_section() ) ? ' llms-active' : ''; ?>
				<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=all' ) ); ?>"><?php echo $this->get_section_title( 'all' ); ?></a></li>
				<?php foreach ( array_keys( $this->data['sections'] ) as $name ) :
					$active = ( $this->get_current_section() === $name ) ? ' llms-active' : ''; ?>
					<li class="llms-nav-item<?php echo $active; ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-add-ons&section=' . $name ) ); ?>"><?php echo $this->get_section_title( $name ); ?></a></li>
				<?php endforeach; ?>

			<?php do_action( 'lifterlms_after_addons_nav' ); ?>
			</ul>
		</nav>
		<?php
	}

}
