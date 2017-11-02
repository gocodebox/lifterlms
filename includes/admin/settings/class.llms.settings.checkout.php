<?php
/**
 * Admin Settings Page, Checkout Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Settings_Checkout extends LLMS_Settings_Page {

	/**
	 * Allow settings page to determine if a rewrite flush is required
	 * @var      boolean
	 * @since    3.0.4
	 * @version  3.10.0
	 */
	protected $flush = true;

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {

		$this->id    = 'checkout';
		$this->label = __( 'Checkout', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return   array
	 * @since    3.0.4
	 * @version  3.10.0
	 */
	public function get_settings() {

		$currency_code_options = get_lifterlms_currencies();
		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_lifterlms_currency_symbol( $code ) . ')';
		}

		$country_options = get_lifterlms_countries();
		foreach ( $country_options as $code => $name ) {
			$country_options[ $code ] = $name . ' (' . $code . ')';
		}

		$settings = array(

			array(
				'class' => 'top',
				'id' => 'course_archive_options',
				'type' => 'sectionstart',
			),

			array(
				'id' => 'course_options',
				'title' => __( 'Checkout Settings', 'lifterlms' ),
				'type' => 'title',
			),

			array(
				'title' => __( 'Checkout Page', 'lifterlms' ),
				'desc' 		=> '<br/>' . sprintf( __( 'Page used for displaying the checkout form.', 'lifterlms' ), admin_url( 'options-permalink.php' ) ),
				'id' 		=> 'lifterlms_checkout_page_id',
				'type' 		=> 'single_select_page',
				'default'	=> '',
				'class'		=> 'llms-select2',
				'desc_tip'	=> __( 'This sets the base page of the checkout page', 'lifterlms' ),
				'custom_attributes' => array(
					'data-post-type' => 'page',
				),
				'options' => llms_make_select2_post_array( get_option( 'lifterlms_checkout_page_id', '' ) ),
			),

			array(
				'title' => __( 'Confirm Payment', 'lifterlms' ),
				'desc' 		=> __( 'Payment Confirmation Page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_confirm_payment_endpoint',
				'type' 		=> 'text',
				'default'	=> 'confirm-payment',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'Force SSL', 'lifterlms' ),
				'desc' 		=> __( 'Force secure checkout via SSL (https) on the checkout page(s).', 'lifterlms' ) .
							   '<br><span class="description">' . sprintf( __( 'Requires an SSL certificate. %1$sLearn More%2$s', 'lifterlms' ), '<a href="https://lifterlms.com/docs/ssl-and-https/" target="_blank">', '</a>' ) . '</span>',
				'id' 		=> 'lifterlms_checkout_force_ssl',
				'type' 		=> 'checkbox',
				'default'	=> 'no',
			),

			array(
				'default'	=> 'yes',
				'desc' => __( 'Enable automatic retry of failed recurring payments.', 'lifterlms' ) .
							  '<br><span class="description">' . sprintf( __( 'Recover lost revenue from temporarily declined payment methods. %1$sLearn more%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/automatic-retry-failed-payments/" target="_blank">', '</a>' ) . '</span>',
				'id' => 'lifterlms_recurring_payment_retry',
				'title' => __( 'Retry Failed Payments', 'lifterlms' ),
				'type' 		=> 'checkbox',
			),

			array(
				'type' => 'sectionend',
				'id' => 'course_archive_options',
			),

			array(
				'type' => 'sectionstart',
				'id' => 'general_options',
			),

			array(
				'title' => __( 'Currency Options', 'lifterlms' ),
				'type' => 'title',
				'desc' => __( 'The following options affect how prices are displayed on the frontend.', 'lifterlms' ),
				'id' => 'pricing_options',
			),

			array(
				'class'     => 'llms-select2',
				'title' 	=> __( 'Country', 'lifterlms' ),
				'desc'      => '<br>' . __( 'Select the country LifterLMS should use as the default during transactions and registrations.', 'lifterlms' ),
				'id' 		=> 'lifterlms_country',
				'default'	=> 'US',
				'type' 		=> 'select',
				'desc_tip'	=> false,
				'options'   => $country_options,
			),

			array(
				'class'     => 'llms-select2',
				'title' 	=> __( 'Currency', 'lifterlms' ),
				'desc'      => '<br>' . __( 'Select the currency LifterLMS should use to display prices and process transactions.', 'lifterlms' ),
				'id' 		=> 'lifterlms_currency',
				'default'	=> 'USD',
				'type' 		=> 'select',
				'desc_tip'	=> false,
				'options'   => $currency_code_options,
			),

			array(
				'title' 	=> __( 'Currency Position', 'lifterlms' ),
				'desc'      => '<br>' . __( 'Customize the position and formatting of the currency symbol for displayed prices.', 'lifterlms' ),
				'id' 		=> 'lifterlms_currency_position',
				'default'	=> 'left',
				'type' 		=> 'select',
				'options'   => array(
					'left'  => 'Left (' . sprintf( '%1$s%2$s', get_lifterlms_currency_symbol(), 99.99 ) . ')',
					'right' => 'Right (' . sprintf( '%2$s%1$s', get_lifterlms_currency_symbol(), 99.99 ) . ')',
					'left_space' => 'Left with Space (' . sprintf( '%1$s&nbsp;%2$s', get_lifterlms_currency_symbol(), 99.99 ) . ')',
					'right_space' => 'Right with Space (' . sprintf( '%2$s&nbsp;%1$s', get_lifterlms_currency_symbol(), 99.99 ) . ')',
				),
			),

			array(
				'title'     => __( 'Thousand Separator', 'lifterlms' ),
				'class'     => 'tiny',
				'desc' 		=> '<br>' . __( 'Choose the character to display as the thousand\'s place separator for displayed prices.', 'lifterlms' ),
				'id' 		=> 'lifterlms_thousand_separator',
				'type' 		=> 'text',
				'default'	=> ',',
			),

			array(
				'title'     => __( 'Decimal Separator', 'lifterlms' ),
				'class'     => 'tiny',
				'desc' 		=> '<br>' . __( 'Choose the character to display as the decimal separator for displayed prices.', 'lifterlms' ),
				'id' 		=> 'lifterlms_decimal_separator',
				'type' 		=> 'text',
				'default'	=> '.',
			),

			array(
				'title'     => __( 'Decimal Places', 'lifterlms' ),
				'class'     => 'tiny',
				'desc' 		=> '<br>' . __( 'Customize the number of decimal places for prices.', 'lifterlms' ),
				'id' 		=> 'lifterlms_decimals',
				'type' 		=> 'number',
				'default'	=> '2',
			),

			array(
				'title'         => __( 'Hide Zero Decimals', 'lifterlms' ),
				'desc'          => __( 'Automatically remove zero decimals from the end of displayed prices.', 'lifterlms' ),
				'id'            => 'lifterlms_trim_zero_decimals',
				'default'       => 'no',
				'type'          => 'checkbox',
			),

			array(
				'type' => 'sectionend',
				'id' => 'general_options',
			),

		);

		$settings[] = array(
			'type' => 'sectionstart',
			'id' => 'checkout_gateway_settings',
			'class' => 'top',
		);

		$settings[] = array(
			'title' => __( 'Payment Gateways', 'lifterlms' ),
			'type' => 'title',
			'id' => 'course_options',
		);

		$gateways = LLMS()->payment_gateways()->get_payment_gateways();
		$total = count( $gateways );
		$i = 1;
		foreach ( $gateways as $id => $g ) {

			$settings = array_merge( $settings, $g->get_admin_settings_fields() );

			// output a break after each gateway
			if ( $i !== $total ) {
				$settings[] = array(
					'type'  => 'custom-html',
					'value' => '<hr>',
				);
			} else {
				$i++;
			}
		}

		$settings[] = array(
			'type' => 'sectionend',
			'id' => 'general_options',
		);

		return apply_filters( 'lifterlms_checkout_settings', $settings );
	}

	/**
	 * save settings to the database
	 *
	 * @return LLMS_Admin_Settings::save_fields
	 *
	 * @version  3.0.0
	 */
	public function save() {
		LLMS_Admin_Settings::save_fields( $this->get_settings() );
	}

	/**
	 * get settings from the database
	 *
	 * @return array
	 *
	 * @version  3.0.0
	 */
	public function output() {
		LLMS_Admin_Settings::output_fields( $this->get_settings() );
	}

}

return new LLMS_Settings_Checkout();
