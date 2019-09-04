<?php
/**
 * Display a Setup Wizard
 *
 * @since 3.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Display a Setup Wizard
 *
 * @since 3.0.0
 * @since 3.30.3 Fixed spelling error.
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Admin_Setup_Wizard {

	/**
	 * Instance of WP_Error
	 *
	 * @var WP_Error
	 */
	private $error;

	/**
	 * Constructor
	 *
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct() {

		if ( apply_filters( 'llms_enable_setup_wizard', true ) ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_init', array( $this, 'save' ) );
			add_action( 'admin_print_footer_scripts', array( $this, 'scripts' ) );

		}

	}

	/**
	 * Register wizard setup page
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function admin_menu() {

		add_dashboard_page( '', '', apply_filters( 'llms_setup_wizard_access', 'install_plugins' ), 'llms-setup', array( $this, 'output' ) );

		update_option( 'lifterlms_first_time_setup', 'yes' );

	}

	/**
	 * Enqueue static assets for the setup wizard screens
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.17.8
	 */
	public function enqueue() {
		wp_register_style( 'llms-admin-setup', LLMS_PLUGIN_URL . '/assets/css/admin-setup' . LLMS_ASSETS_SUFFIX . '.css', array(), LLMS()->version, 'all' );
		wp_enqueue_style( 'llms-admin-setup' );
		wp_style_add_data( 'llms-admin-setup', 'rtl', 'replace' );
		wp_style_add_data( 'llms-admin-setup', 'suffix', LLMS_ASSETS_SUFFIX );
	}

	/**
	 * Allow the Sample Content installed during the final step to be published rather than drafted
	 *
	 * @param    string $status  post status
	 * @return   string
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function generator_course_status( $status ) {
		return 'publish';
	}

	/**
	 * Retrieve the current step and default to the intro
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return   string
	 */
	public function get_current_step() {
		return empty( $_GET['step'] ) ? 'intro' : llms_filter_input( INPUT_GET, 'step', FILTER_SANITIZE_STRING );
	}

	/**
	 * Get slug if next step
	 *
	 * @param    string $step   step to use as current
	 * @return   string|false
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_next_step( $step = '' ) {
		if ( ! $step ) {
			$step = $this->get_current_step();
		}
		$steps = $this->get_steps();
		$keys  = array_keys( $steps );
		$i     = array_search( $step, $keys );
		if ( false === $i ) {
			return false;
		} elseif ( $i++ >= count( $keys ) - 1 ) {
			return false;
		} else {
			return $keys[ $i++ ];
		}
	}

	/**
	 * Get slug if prev step
	 *
	 * @param    string $step   step to use as current
	 * @return   string|false
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_prev_step( $step = '' ) {
		if ( ! $step ) {
			$step = $this->get_current_step();
		}
		$steps = $this->get_steps();
		$keys  = array_keys( $steps );
		$i     = array_search( $step, $keys );
		if ( false === $i ) {
			return false;
		} elseif ( $i - 1 < 0 ) {
			return false;
		} else {
			return $keys[ $i - 1 ];
		}
	}

	/**
	 * Get the text to display on the "save" buttons
	 *
	 * @param    string $step  step to get text for
	 * @return   string            translated text
	 * @since    3.0.0
	 * @version  3.3.0
	 */
	private function get_save_text( $step = '' ) {
		if ( 'coupon' === $step ) {
			return __( 'Allow', 'lifterlms' );
		} elseif ( 'finish' === $step ) {
			return __( 'Install a Sample Course', 'lifterlms' );
		} else {
			return __( 'Save & Continue', 'lifterlms' );
		}
	}

	/**
	 * Get the text to display on the "save" buttons
	 *
	 * @param    string $step  step to get text for
	 * @return   string            translated text
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private function get_skip_text( $step = '' ) {
		if ( 'coupon' === $step ) {
			return __( 'No thanks', 'lifterlms' );
		} else {
			return __( 'Skip this step', 'lifterlms' );
		}
	}

	/**
	 * Get the URL to a step
	 *
	 * @param    string $step  step slug
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	private function get_step_url( $step ) {
		return add_query_arg(
			array(
				'page' => 'llms-setup',
				'step' => $step,
			),
			admin_url()
		);
	}

	/**
	 * Get an array of step slugs => titles
	 *
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_steps() {

		return array(

			'intro'    => __( 'Welcome!', 'lifterlms' ),
			'pages'    => __( 'Page Setup', 'lifterlms' ),
			'payments' => __( 'Payments', 'lifterlms' ),
			'coupon'   => __( 'Coupon', 'lifterlms' ),
			'finish'   => __( 'Finish!', 'lifterlms' ),

		);

	}

	/**
	 * Output the HTML content of the setup page
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.16.14
	 */
	public function output() {

		$current = $this->get_current_step();
		$steps   = $this->get_steps();
		?>

		<div id="llms-setup-wizard">

			<div class="llms-setup-wrapper">

				<h1 id="llms-logo">
					<a href="https://lifterlms.com/" target="_blank">
						<img src="<?php echo LLMS()->plugin_url(); ?>/assets/images/lifterlms-logo.png" alt="LifterLMS">
					</a>
				</h1>

				<ul class="llms-setup-progress">
					<?php foreach ( $steps as $slug => $name ) : ?>
						<li<?php echo ( $slug === $current ) ? ' class="current"' : ''; ?>><?php echo $name; ?></li>
					<?php endforeach; ?>
				</ul>

				<div class="llms-setup-content">
					<form action="" method="POST">

						<?php echo $this->output_step_html( $current ); ?>

						<?php if ( is_wp_error( $this->error ) ) : ?>
							<p class="error"><?php echo $this->error->get_error_message(); ?></p>
						<?php endif; ?>

						<p class="llms-setup-actions">
							<?php if ( 'intro' === $current ) : ?>
								<a href="<?php echo esc_url( admin_url() ); ?>" class="llms-button-secondary large"><?php _e( 'Skip setup', 'lifterlms' ); ?></a>
								<a href="<?php echo esc_url( admin_url() . '?page=llms-setup&step=' . $this->get_next_step() ); ?>" class="llms-button-primary large"><?php _e( 'Get Started Now', 'lifterlms' ); ?></a>
							<?php else : ?>
								<?php
								$prev = $this->get_prev_step();
								if ( $prev ) :
									?>
									<a class="back-link" href="<?php echo $this->get_step_url( $prev ); ?>"><?php _e( 'Go back', 'lifterlms' ); ?></a>
								<?php endif; ?>
								<?php
								$next = $this->get_next_step();
								if ( $next ) :
									?>
									<a href="<?php echo $this->get_step_url( $next ); ?>" class="llms-button-secondary large"><?php echo $this->get_skip_text( $current ); ?></a>
								<?php endif; ?>

								<?php if ( 'finish' === $current ) : ?>
									<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=course' ) ); ?>" class="llms-button-secondary large"><?php _e( 'Start from Scratch', 'lifterlms' ); ?></a>
								<?php endif; ?>

								<button class="llms-button-primary large" type="submit"><?php echo $this->get_save_text( $current ); ?></button>
								<input type="hidden" name="llms_setup_save" value="<?php echo $current; ?>">
								<?php wp_nonce_field( 'llms_setup_save', 'llms_setup_nonce' ); ?>
							<?php endif; ?>
						</p>

					</form>
				</div>

				<?php if ( 'finish' === $current ) : ?>
					<a class="dashboard-return" href="<?php echo admin_url(); ?>"><?php _e( 'Return to the WordPress Dashboard', 'lifterlms' ); ?></a>
				<?php endif; ?>

			</div>

		</div>

		<?php
	}

	/**
	 * Outputs the HTML "body" for the requested step
	 *
	 * @since 3.0.0
	 * @since 3.30.3 Fixed spelling error.
	 *
	 * @param string $step Step slug.
	 * @return void
	 */
	public function output_step_html( $step ) {

		switch ( $step ) {

			case 'coupon':
				?>
				<h1><?php _e( 'Help Improve LifterLMS & Get a Coupon', 'lifterlms' ); ?></h1>
				<p><?php _e( 'By allowing us to collect non-sensitive usage information and diagnostic data, you\'ll be providing us with information we can use to make the future of LifterLMS stronger and more powerful with every update!', 'lifterlms' ); ?></p>
				<p><?php _e( 'Click "Allow" to and we\'ll send you a coupon immediately.', 'lifterlms' ); ?></p>
				<p><a href="https://lifterlms.com/usage-tracking/" target="_blank"><?php _e( 'Find out more information', 'lifterlms' ); ?></a>.</p>
				<?php
				break;

			case 'finish':
				?>
				<h1><?php _e( 'Setup Complete!', 'lifterlms' ); ?></h1>
				<p><?php _e( 'Here\'s some resources to help you get familiar with LifterLMS:', 'lifterlms' ); ?></p>
				<ul>
					<li><span class="dashicons dashicons-format-video"></span> <a href="https://demo.lifterlms.com/course/how-to-build-a-learning-management-system-with-lifterlms/" target="_blank"><?php _e( 'Watch the LifterLMS video tutorials', 'lifterlms' ); ?></a></li>
					<li><span class="dashicons dashicons-admin-page"></span> <a href="https://lifterlms.com/docs/getting-started-with-lifterlms/" target="_blank"><?php _e( 'Read the LifterLMS Getting Started Guide', 'lifterlms' ); ?></a></li>
				</ul>
				<br>
				<h1 style="text-align: center;"><?php _e( 'Get started with your first course', 'lifterlms' ); ?></h1>
				<?php
				break;

			case 'intro':
				?>
				<h1><?php _e( 'Welcome to LifterLMS!', 'lifterlms' ); ?></h1>

				<p><?php _e( 'Thanks for choosing LifterLMS to power your online courses! This short setup wizard will guide you through the basic settings and configure LifterLMS so you can get started creating courses faster!', 'lifterlms' ); ?></p>
				<p><?php _e( 'It will only take a few minutes and it is completely optional. If you don\'t have the time now, come back later.', 'lifterlms' ); ?></p>
				<?php
				break;

			case 'pages':
				?>
				<h1><?php _e( 'Page Setup', 'lifterlms' ); ?></h1>

				<p><?php _e( 'LifterLMS has a few essential pages. The following will be created automatically if they don\'t already exist.', 'lifterlms' ); ?>

				<table>
					<tr>
						<td><a href="https://lifterlms.com/docs/course-catalog/" target="_blank"><?php _e( 'Course Catalog', 'lifterlms' ); ?></a></td>
						<td><p><?php _e( 'This page is where your visitors will find a list of all your available courses.', 'lifterlms' ); ?></p></td>
					</tr>
					<tr>
						<td><a href="https://lifterlms.com/docs/membership-catalog/" target="_blank"><?php _e( 'Membership Catalog', 'lifterlms' ); ?></a></td>
						<td><p><?php _e( 'This page is where your visitors will find a list of all your available memberships.', 'lifterlms' ); ?></p></td>
					</tr>
					<tr>
						<td><a href=" https://lifterlms.com/docs/checkout-page/" target="_blank"><?php _e( 'Checkout', 'lifterlms' ); ?></a></td>
						<td><p><?php _e( 'This is the page where visitors will be directed in order to pay for courses and memberships.', 'lifterlms' ); ?></p></td>
					</tr>
					<tr>
						<td><a href="https://lifterlms.com/docs/student-dashboard/" target="_blank"><?php _e( 'Student Dashboard', 'lifterlms' ); ?></a></td>
						<td><p><?php _e( 'Page where students can view and manage their current enrollments, earned certificates and achievements, account information, and purchase history.', 'lifterlms' ); ?></p></td>
					</tr>
				</table>

				<p><?php printf( __( 'After setup, you can manage these pages from the admin dashboard on the %1$sPages screen%2$s and you can control which pages display on your menu(s) via %3$sAppearance > Menus%4$s.', 'lifterlms' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">', '</a>', '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">', '</a>' ); ?></p>
				<?php
				break;

			case 'payments':
				$country  = get_lifterlms_country();
				$currency = get_lifterlms_currency();
				$payments = get_option( 'llms_gateway_manual_enabled', 'no' );

				?>
				<h1><?php _e( 'Payments', 'lifterlms' ); ?></h1>

				<table>
					<tr>
						<td colspan="2">
							<p><label for="llms_country"><?php _e( 'Which country should be used as the default for student registrations?', 'lifterlms' ); ?></label></p>
							<p>
								<select id="llms_country" name="country" class="llms-select2">
								<?php foreach ( get_lifterlms_countries() as $code => $name ) : ?>
									<option value="<?php echo $code; ?>"<?php selected( $code, $country ); ?>><?php echo $name; ?> (<?php echo $code; ?>)</option>
								<?php endforeach; ?>
								</select>
							</p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p><label for="llms_currency"><?php _e( 'Which currency should be used for payment processing?', 'lifterlms' ); ?></label></p>
							<p>
								<select id="llms_currency" name="currency" class="llms-select2">
								<?php foreach ( get_lifterlms_currencies() as $code => $name ) : ?>
									<option value="<?php echo $code; ?>"<?php selected( $code, $currency ); ?>><?php echo $name; ?> (<?php echo get_lifterlms_currency_symbol( $code ); ?>)</option>
								<?php endforeach; ?>
								</select>
								<i><?php printf( __( 'If you currency is not listed you can %1$sadd it later%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/how-can-i-add-my-currency-to-lifterlms" target="_blank">', '</a>' ); ?></i>
							</p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p><?php printf( __( 'With LifterLMS you can accept both online and offline payments. Be sure to install a %1$spayment gateway%2$s to accept online payments.', 'lifterlms' ), '<a href="https://lifterlms.com/product-category/plugins/payment-gateways/" target="_blank">', '</a>' ); ?></p>
							<p><label for="llms_manual"><input id="llms_manual" name="manual_payments" type="checkbox" value="yes"<?php checked( 'yes', $payments ); ?>> <?php _e( 'Enable Offline Payments', 'lifterlms' ); ?></label></p>
						</td>
					</tr>
				</table>

				<?php
				break;

		}// End switch().

	}

	/**
	 * Handle saving data during setup
	 *
	 * @since 3.0.0
	 * @since 3.3.0 Unknown.
	 * @since 3.35.0 Sanitize input data; load sample data from `sample-data` directory.
	 *
	 * @return   void
	 */
	public function save() {

		if ( ! isset( $_POST['llms_setup_nonce'] ) || ! llms_verify_nonce( 'llms_setup_nonce', 'llms_setup_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_lifterlms' ) ) {
			return;
		}

		switch ( llms_filter_input( INPUT_POST, 'llms_setup_save', FILTER_SANITIZE_STRING ) ) {

			case 'coupon':
				update_option( 'llms_allow_tracking', 'yes' );
				$req = LLMS_Tracker::send_data( true );

				if ( is_wp_error( $req ) ) {
					$r = false;
				} elseif ( isset( $req['success'] ) ) {
					$r = $req['success'];

					if ( ! $req['success'] ) {

						$this->error = new WP_Error( 'error', $r['message'] );
						return;

					}
				}

				break;

			case 'finish':
				add_filter( 'llms_generator_course_status', array( $this, 'generator_course_status' ) );
				$json = file_get_contents( LLMS_PLUGIN_DIR . 'sample-data/sample-course.json' );
				$gen  = new LLMS_Generator( $json );
				$gen->set_generator();
				$gen->generate();
				if ( $gen->is_error() ) {
					wp_die( $gen->get_results() );
				} else {
					$courses = wp_get_recent_posts(
						array(
							'numberposts'      => 1,
							'orderby'          => 'post_date',
							'order'            => 'DESC',
							'post_type'        => 'course',
							'post_status'      => 'publish',
							'suppress_filters' => true,
						)
					);
					if ( $courses ) {
						wp_safe_redirect( get_edit_post_link( $courses[0]['ID'], 'not-display' ) );
						die;
					}
				}

				break;

			case 'pages':
				$r = LLMS_Install::create_pages();
				break;

			case 'payments':
				$country = isset( $_POST['country'] ) ? llms_filter_input( INPUT_POST, 'country', FILTER_SANITIZE_STRING ) : get_lifterlms_country();
				update_option( 'lifterlms_country', $country );

				$currency = isset( $_POST['currency'] ) ? llms_filter_input( INPUT_POST, 'currency', FILTER_SANITIZE_STRING ) : get_lifterlms_currency();
				update_option( 'lifterlms_currency', $currency );

				$manual = isset( $_POST['manual_payments'] ) ? llms_filter_input( INPUT_POST, 'manual_payments', FILTER_SANITIZE_STRING ) : 'no';
				update_option( 'llms_gateway_manual_enabled', $manual );

				$r = true;

				break;

			default:
				$r = false;

				break;
		}// End switch().

		if ( false === $r ) {

			$this->error = new WP_Error( 'error', __( 'There was an error saving your data, please try again.', 'lifterlms' ) );
			return;

		} else {

			wp_safe_redirect( $this->get_step_url( $this->get_next_step() ) );
			exit;

		}

	}

	/**
	 * Quick and dirty JS "file"...
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function scripts() {
		?>
		jQuery( '.llms-select2' ).llmsSelect2();
		<?php
	}

}

return new LLMS_Admin_Setup_Wizard();
