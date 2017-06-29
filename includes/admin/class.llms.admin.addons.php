<?php
/**
 * LifterLMS Add-On browser
 * This is where the adds are, if you don't like it that's okay but i don't want to hear your complaints!
 * @since    3.5.0
 * @version  3.10.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_AddOns {

	/**
	 * Url Where addon JSON information is pulled from
	 */
	const DATA_URL = 'http://d34dpc7391qduo.cloudfront.net/addons/addons.json';

	/**
	 * This URL is good for development since it wont be cached as hard
	 */
	// const DATA_URL = 'https://s3-us-west-2.amazonaws.com/lifterlms/addons/addons.json';

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

	}

	/**
	 * Translate the title of the current section
	 * @param    sring     $name  section name (untranslated key)
	 * @return   string
	 * @since    3.5.0
	 * @version  3.10.0
	 */
	private function get_section_title( $name ) {
		switch ( $name ) {

			case 'advanced':
				return __( 'Advanced', 'lifterlms' );
			break;

			case 'affiliates':
				return __( 'Affiliates', 'lifterlms' );
			break;

			case 'all':
				return __( 'All', 'lifterlms' );
			break;

			case 'bundles':
				return __( 'Bundles', 'lifterlms' );
			break;

			case 'gateways':
				return __( 'Payment Gateways', 'lifterlms' );
			break;

			case 'marketing':
				return __( 'E-Mail & Marketing', 'lifterlms' );
			break;

			case 'themes':
				return __( 'Themes & Design', 'lifterlms' );
			break;

			case 'tools':
				return __( 'Tools & Utilities', 'lifterlms' );
			break;

			case 'resources':
				return __( 'Resources', 'lifterlms' );
			break;

			case 'services':
				return __( 'Services', 'lifterlms' );
			break;
		}// End switch().
		return $name;
	}


	/**
	 * Output HTML for the current screen
	 * @return   void
	 * @since    3.5.0
	 * @version  3.5.0
	 */
	public function output() {

		if ( is_wp_error( $this->get_data() ) ) {

			_e( 'There was an error retrieving add-ons. Please try again.', 'lifterlms' );
			return;

		}
		?>
		<div class="wrap lifterlms lifterlms-settings">
			<h1><?php _e( 'LifterLMS Add-Ons, Services, and Resources', 'lifterlms' ); ?></h1>
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
		$featured = $addon['featured'] ? ' featured' : '';
		?>
		<li class="llms-add-on-item<?php echo $featured; ?>">
			<a href="<?php echo esc_url( $addon['url'] ); ?>" class="llms-add-on">
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
	 * @version  3.7.5
	 */
	private function output_navigation() {
		?>
		<nav class="llms-nav-tab-wrapper">
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
