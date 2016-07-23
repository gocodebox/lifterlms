<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Settings Page, General Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_General extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'register_hooks' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		$currency_code_options = get_lifterlms_currencies();

		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_lifterlms_currency_symbol( $code ) . ')';
		}

		if ( ! get_option( 'lifterlms_first_time_setup' ) ) {
			return apply_filters( 'lifterlms_general_settings', array(

				array( 'type' => 'sectionstart', 'id' => 'general_information', 'class' => 'top' ),

				array(
					'title' => __( 'Welcome to LifterLMS', 'lifterlms' ),
					'type' => 'desc',
					'desc' => '
						<h2>' . __( 'Getting Started with LifterLMS', 'lifterlms' ) . '</h2>
						<p>' . __( 'Before you start creating courses, making lots of money and building the best (insert your business here) online there are a few setup items we need to address.', 'lifterlms' ) . '</p>
						<p>' . __( 'We need to set up your pages. Ya, we know, more pages... That\'s just the way Wordpress works. We\'ve already installed them. You just need to set them.', 'lifterlms' ) . '</p>
						' . __( 'When you installed LifterLMS we created a few pages for you. You can select those pages or use different ones. Your choice.', 'lifterlms' ) . '</p>
						<p>' . __( 'The first page you need is the Student Account page. This is the page users will go to register, login and access their accounts. We installed a page called My Courses. You can use that or select a different page. If you happen to select a different page you will need to add this shortcode to the page:', 'lifterlms' ) . ' [lifterlms_my_account]</p>',
					'id' => 'welcome_options_setup',
				),

				array(
					'title' => __( 'Account Access Page', 'lifterlms' ),
					'desc' 		=> __( 'We suggest you choose "My Courses"', 'lifterlms' ),
					'id' 		=> 'lifterlms_myaccount_page_id',
					'type' 		=> 'single_select_page',
					'default'	=> '',
					'class'		=> 'chosen_select_nostd',
					'desc_tip'	=> true,
				),

				array(
					'type' => 'desc',
					'desc' => '<p>' . __( 'Next we need a checkout page so people can buy your courses and memberships. If you are a true philanthropist and don\'t plan on selling anything you can skip setting up this page. We created a page called "Purchase you can use that or select a different page.', 'lifterlms' ) . '</p>',
					'id' => 'welcome_options_setup',
				),

				array(
					'title' => __( 'Checkout Page', 'lifterlms' ),
					'desc' 		=> __( 'We suggest you choose "Purchase"', 'lifterlms' ),
					'id' 		=> 'lifterlms_checkout_page_id',
					'type' 		=> 'single_select_page',
					'default'	=> '',
					'class'		=> 'chosen_select_nostd',
					'desc_tip'	=> __( 'This sets the base page of the checkout page', 'lifterlms' ),
				),

				array(
					'type' => 'desc',
					'desc' => '
					<p>' . __( 'If you are going to sell your courses you should probably pick a currency.', 'lifterlms' ) . '</p>',
					'id' => 'welcome_options_setup',
				),

				array(
					'title' 	=> __( 'Default Currency', 'lifterlms' ),
					'desc' 		=> __( 'Default currency type.', 'lifterlms' ),
					'id' 		=> 'lifterlms_currency',
					'default'	=> 'USD',
					'type' 		=> 'select',
					'class'		=> 'chosen_select',
					'desc_tip'	=> true,
					'options'   => $currency_code_options,
				),

				array(
					'type' => 'desc',
					'desc' => '
						<p>' . __( 'There are a lot of other settings but those were the important ones to get you started. You can access all of the other settings from the big blue menu at the top of the page.', 'liftelrms' ) . '</p>
						<p>' . __( 'If you have any questions or want to request a feature head on over to our', 'lifterlms' ) . ' <a href="https://lifterlms.com/forums/">' . __( 'Support Forums.', 'lifterlms' ) . '</a></p>
						<p>' . __( 'That\'s all there is to it. Your ready to start building courses and changing the world!', 'lifterlms' ) . '</p>
						<p>' . __( 'Click "Save Changes" below to save your settings and get started.', 'lifterlms' ) . '</p>',
					'id' => 'welcome_options_setup',
				),

				/**
				 * Hidden Values
				 */

				// first time setup is complete
				array(
					'type' => 'hidden',
					'value' => 'yes',
					'id' => 'lifterlms_first_time_setup',
				),

				// don't enable legacy cert settings for a new installation!
				array(
					'type' => 'hidden',
					'value' => 'no',
					'id' => 'lifterlms_certificate_legacy_image_size',
				),

				array( 'type' => 'sectionend', 'id' => 'welcome_options_activate' ),

			) );

		} else {

			return apply_filters( 'lifterlms_general_settings', array(

				array(
						'type' => 'custom-html',
						'value' => self::get_stats_widgets(),
				),

				array(
					'type' => 'custom-html',
					'value' => self::get_big_banners(),
				),

				array(
						'type' => 'custom-html',
						'value' => self::get_small_banners(),
				),

				array( 'type' => 'sectionstart', 'id' => 'general_information', 'class' => 'top' ),

				array(
					'title' => __( 'Quick Links',
					'lifterlms' ),
						'type' => 'title',
						'desc' => '

						<div class="llms-list">
							<ul>
								<li><p>' . __( 'Version:', 'lifterlms' ) . ' ' . LLMS()->version . '</p></li>
								<li><p>' . __( 'Need help? Send us a support request at ', 'lifterlms' ) . ' <a href="https://lifterlms.com/contact/" target="_blank">' . __( 'https://lifterlms.com/contact/' ) . '</a>.</p></li>
								<li><p>' . __( 'Looking for a quickstart guide, shortcodes, or developer documentation? Visit our documentation portal at ', 'lifterlms' ) . ' <a href="https://lifterlms.readme.io/" target="_blank">' . __( 'https://lifterlms.readme.io/' ) . '</a>.</p></li>
								<li><p>' . __( 'Get LifterLMS news, updates, and more on our blog at ', 'lifterlms' ) . ' <a href="http://blog.lifterlms.com/" target="_blank">' . __( 'http://blog.lifterlms.com/' ) . '</a></p></li>
							</ul>
						</div>',
					'id' => 'activation_options',
				),

				array( 'type' => 'sectionend', 'id' => 'general_information' ),

				array( 'type' => 'sectionstart', 'id' => 'session_manager' ),

				array( 'title' => __( 'Session Management', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'Manage User Sessions. LifterLMS creates custom user sessions to manage, payment processing, quizzes and user registration. If you are experiencing issues or incorrect error messages are displaying. Clearing out all of the user session data may help.', 'lifterlms' ), 'id' => 'session_manager' ),

				array(
					'title' => '',
					'value' => __( 'Clear All User Session Data', 'lifterlms' ),
					'type' 		=> 'button',
				),

				array( 'type' => 'sectionend', 'id' => 'session_manager' ),

			 	)
			);
		}

	}

	/**
	 * register new hooks
	 * @return void
	 */
	public function register_hooks() {

		if ( isset( $_POST['save'] ) && strtolower( $_POST['save'] ) == 'clear all user session data') {
			$session_handler = new LLMS_Session_Handler();

			$session_handler->delete_all_session_data();

		}
	}

	/**
	 * save settings to the database
	 *
	 * @return LLMS_Admin_Settings::save_fields
	 */
	public function save() {

		$settings = $this->get_settings();
		LLMS_Admin_Settings::save_fields( $settings );

	}

	public static function get_stats_widgets() {

		$students_enrolled = LLMS_Analytics::get_users_enrolled_last_n_days( 7 );
		$members_registered = LLMS_Analytics::get_members_registered_last_n_days( 7 );
		$lessons_completed = LLMS_Analytics::get_lessons_completed_last_n_days( 7 );
		$total_sales = LLMS_Analytics::get_total_sales_last_n_days( 7 );

		$html = '<div class="llms-widget-row">
					<div class="llms-widget-1-4">
						<div class="llms-widget"><p class="llms-label">' . __( 'Course Enrollments This Week', 'lifterlms' ) . '</p><h1>' . $students_enrolled . '</h1></div>
					</div>
					<div class="llms-widget-1-4">
						<div class="llms-widget"><p class="llms-label">' . __( 'New Members This Week', 'lifterlms' ) . '</p><h1>' . $members_registered . '</h1></div>
					</div>
					<div class="llms-widget-1-4">
						<div class="llms-widget"><p class="llms-label">' . __( 'Lessons Completed This Week', 'lifterlms' ) . '</p><h1>' . $lessons_completed . '</h1></div>
					</div>
					<div class="llms-widget-1-4">
						<div class="llms-widget"><p class="llms-label">' . __( 'Total Sales This Week', 'lifterlms' ) . '</p><h1>' . $total_sales .'</h1></div>
					</div>
				</div>';
		return preg_replace( '~>\s+<~', '><', $html );
	}

	/**
	 * Output the set of two-column banners
	 * @return string
	 */
	public static function get_big_banners() {

		$banners = array(
			'lifterlms-pro' => array(
				'type' => 'service',
				'title' => 'Lifter LMS Pro',
				'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/lifterlms-pro.png',
				'link' => 'https://lifterlms.com/product/lifterlms-pro?ims=kujno&utm_campaign=Plugin+to+Sale&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=LifterLMS+Pro+Ad+001',
			),
			'lifterlms-launchpad' => array(
				'type' => 'theme',
				'title' => 'LifterLMS LaunchPad Theme',
				'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/lifterlms-launchpad.png',
				'link' => 'https://lifterlms.com/launchpad/?utm_source=Plugin&utm_medium=Plugin%20Ad&utm_campaign=Plugin%20to%20LaunchPad',
			),
			'lifterlms-stripe' => array(
				'type' => 'plugin',
				'title' => 'Stripe Plugin',
				'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/stripe-w-desc.png',
				'link' => 'https://lifterlms.com/product/stripe-extension/?ims=ystxm&utm_campaign=Plugin+to+Sale&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=Stripe+Ad+001',
			),
			'lifterlms-mailchimp' => array(
				'type' => 'plugin',
				'title' => 'Mailchimp Plugin',
				'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/mailchimp-w-desc.png',
				'link' => 'https://lifterlms.com/product/mailchimp-extension/?ims=ycdkk&utm_campaign=Plugin+to+Sale&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=Mailchimp+Ad+001',
			),
			'lifterlms-convertkit' => array(
				'type' => 'plugin',
				'title' => 'ConvertKit',
				'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/convertkit.png',
				'link'	=> 'https://lifterlms.com/product/lifterlms-convertkit/?utm_source=Plugin&utm_medium=Plugin%2BDashboard&utm_content=Plugin%2BAd&utm_campaign=Plugin',
			),
			'lifterlms-boost' => array(
				'type' => 'service',
				'title' => 'Boost',
				'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/boost.png',
				'link'	=> 'https://lifterlms.com/boost?utm_source=Plugin%20&utm_medium=Plugin%20Ad&utm_campaign=Plugin%20to%20Boost',
			),
			'lifterlms-turbo-boost' => array(
				'type' => 'service',
				'title' => 'Turbo Boost',
				'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/turbo-boost.png',
				'link'	=> 'https://lifterlms.com/boost?utm_source=Plugin%20&utm_medium=Plugin%20Ad&utm_campaign=Plugin%20to%20Boost',
			),
		);

		// get installed themes and plugins
		// only show banners for products that aren't installed
		$plugins = array_keys( get_plugins() );
		$themes = array_keys( wp_get_themes() );

		$html = '<div class="llms-widget-row">';

		foreach ( $banners as $slug => $banner ) {

			// if the product has been installed don't show the banner
			switch ( $banner['type'] ) {

				case 'plugin':
					if ( in_array( $slug . DIRECTORY_SEPARATOR . $slug .'.php', $plugins ) ) {
						continue 2;
					}
				break;

				// case 'service':
				// break;

				case 'theme':
					if ( in_array( $slug , $themes ) ) {
						continue 2;
					}
				break;

			}

			$html .= '<div class="llms-widget-1-2"><div class="llms-widget llms-banner-image">';

			if ( isset( $banner['link'] ) ) {
				$html .= '<a href="' . $banner['link'] . '" target="_blank">';
			}

			$html .= '<img width="100%" src="' . $banner['image'] . '" alt="' . $banner['image'] . '">';

			if ( isset( $banner['link'] ) ) {
				$html .= '</a>';
			}

			$html .= '</div></div>';

		}

		$html .= '</div>';

		return $html;
	}

	public static function get_small_banners() {
		$small_banners = array(
				array(
						'title' => 'Ultimate Course Creation Framework',
						'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/online-course.jpg',
						'link' => 'http://courseclinic.com/?utm_source=Plugin&utm_medium=Plugin+Settings&utm_campaign=Plugin+to+Course+Clinic+Opt-in',
				),
				array(
						'title' => 'LifterLMS Demo Course',
						'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/lifterlms-expert.jpg',
						'link' => 'http://demo.lifterlms.com/course/how-to-build-a-learning-management-system-with-lifterlms/?ims=phyxo&utm_campaign=Plugin+Nurture&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=Demo+Ad+001',
				),
				array(
						'title' => 'Course Blueprint',
						'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/students-engaged.jpg',
						'link' => 'https://lifterlms.com/free-lifterlms-course?ims=aympo&utm_campaign=Plugin+Nurture&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=CBP+Ad+001',
				),
				array(
						'title' => 'LifterLMS Optin',
						'image' => LLMS()->plugin_url() . '/assets/images/admin-banners/lifterlms-optin.jpg',
						'link' => 'http://lifterlms.com/fast-start?ims=pfckn&utm_campaign=Plugin+Nurture&utm_source=LifterLMS+Plugin&utm_medium=General+Settings+Screen&utm_content=FS+Ad+001',
				),
		);

		$html = '<div class="llms-widget-row">';

		foreach ($small_banners as $banner) {

			$html .= '<div class="llms-widget-1-4">
							<div class="llms-widget llms-banner-image">
								<a href="' . $banner['link'] . '" target="_blank">
									<img width="100%" src="' . $banner['image'] . '" alt="' . $banner['image'] . '">
								</a>
							</div>
						</div>';
		}

		$html .= '</div>';

		return $html;
	}

}

return new LLMS_Settings_General();
