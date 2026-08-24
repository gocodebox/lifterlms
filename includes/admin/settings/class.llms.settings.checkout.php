<?php
/**
 * Admin Settings Page, Checkout Tab
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 3.0.0
 * @version 3.35.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page, Checkout Tab class
 *
 * @since 3.0.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 3.35.1 Verify nonce when saving.
 */
class LLMS_Settings_Checkout extends LLMS_Settings_Page {

	/**
	 * Allow settings page to determine if a rewrite flush is required
	 *
	 * @var      boolean
	 * @since    3.0.4
	 * @version  3.10.0
	 */
	protected $flush = true;

	/**
	 * Constructor
	 * executes settings tab actions
	 *
	 * @since    3.0.4
	 * @version  3.17.5
	 */
	public function __construct() {

		$this->id    = 'checkout';
		$this->label = __( 'Checkout', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_sections_' . $this->id, array( $this, 'output_sections_nav' ) );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get HTML for the payment gateways table
	 *
	 * @return   string
	 * @since    3.17.5
	 * @version  3.17.5
	 */
	public function get_gateway_table_html() {

		$gateways = llms()->payment_gateways()->get_payment_gateways();

		usort( $gateways, array( $this, 'sort_gateways' ) );

		$registered_ids = array();
		$objects        = array();
		foreach ( $gateways as $gateway ) {
			$registered_ids[]              = $gateway->get_id();
			$objects[ $gateway->get_id() ] = $gateway;
		}
		$matched_catalog_ids = LLMS_Admin_Catalog_Table::get_matched_catalog_ids( $registered_ids, 'checkout', $objects );

		ob_start();
		?>

		<table class="llms-table zebra text-left size-large llms-gateway-table">
			<thead>
				<tr>
					<th class="sort"></th>
					<th><?php esc_html_e( 'Gateway', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Description', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Installed', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Activated', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Enabled', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Documentation', 'lifterlms' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $gateways as $gateway ) : ?>
				<?php
				$addon    = LLMS_Admin_Catalog_Table::get_addon_for_registered( $gateway->get_id(), 'checkout', $gateway );
				$siblings = $addon ? LLMS_Admin_Catalog_Table::get_registered_sibling_count( $addon->get( 'id' ), $registered_ids, 'checkout', $objects ) : 1;
				$title    = LLMS_Admin_Catalog_Table::get_grouped_display_title( $gateway->get_admin_title(), $gateway->get_id(), $addon, $siblings );
				$docs_url = LLMS_Admin_Catalog_Table::get_core_docs_url( $gateway->get_id() );
				if ( ! $docs_url && $addon ) {
					$docs_url = LLMS_Admin_Catalog_Table::get_addon_docs_url( $addon );
				}
				$state      = LLMS_Admin_Catalog_Table::get_row_plugin_state( $gateway->get_id(), $addon, true );
				$learn_more = ( $addon && ! $state['installed'] ) ? LLMS_Admin_Catalog_Table::get_product_url( $addon, 'Checkout Screen' ) : '';
				?>
				<tr>
					<td class="sort">
						<i class="fa fa-bars llms-action-icon" aria-hidden="true"></i>
						<input type="hidden" name="<?php echo esc_attr( $gateway->get_option_name( 'display_order' ) ); ?>" value="<?php echo esc_attr( $gateway->get_display_order() ); ?>">
					</td>
					<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-settings&tab=' . $this->id . '&section=' . $gateway->get_id() ) ); ?>"><?php echo esc_html( $title ); ?></a></td>
					<?php
					LLMS_Admin_Catalog_Table::render_status_cells(
						wp_strip_all_tags( $gateway->get_admin_description() ),
						$state['activated'],
						$docs_url,
						$state['installed'],
						$learn_more,
						$gateway->is_enabled()
					);
					?>
				</tr>
			<?php endforeach; ?>
			<?php
			foreach ( LLMS_Admin_Catalog_Table::get_catalog_addons( 'checkout' ) as $addon ) :
				if ( in_array( $addon->get( 'id' ), $matched_catalog_ids, true ) ) {
					continue;
				}
				$title      = LLMS_Admin_Catalog_Table::get_display_title( $addon->get( 'title' ), $addon->get( 'id' ) );
				$url        = LLMS_Admin_Catalog_Table::get_catalog_row_url( $addon, 'Checkout Screen' );
				$learn_more = $addon->is_installed() ? '' : LLMS_Admin_Catalog_Table::get_product_url( $addon, 'Checkout Screen' );
				?>
				<tr>
					<td class="sort"></td>
					<td><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $title ); ?></a></td>
					<?php
					LLMS_Admin_Catalog_Table::render_status_cells(
						wp_strip_all_tags( $addon->get( 'description' ) ),
						$addon->is_active(),
						LLMS_Admin_Catalog_Table::get_addon_docs_url( $addon ),
						$addon->is_installed(),
						$learn_more,
						null
					);
					?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get the page sections
	 *
	 * @return   array
	 * @since    3.17.5
	 * @version  3.17.5
	 */
	public function get_sections() {

		$sections = array();

		$gateways = llms()->payment_gateways()->get_payment_gateways();

		foreach ( $gateways as $id => $gateway ) {
			$sections[ $id ] = $gateway->get_admin_title();
		}

		asort( $sections );

		$sections = array_merge(
			array(
				'main' => __( 'Checkout Settings', 'lifterlms' ),
			),
			$sections
		);

		return apply_filters( 'llms_checkout_settings_sections', $sections );
	}

	/**
	 * Get settings array
	 *
	 * @return   array
	 * @since    3.0.4
	 * @version  3.17.5
	 */
	public function get_settings() {

		$curr_section = $this->get_current_section();

		if ( 'main' === $curr_section ) {

			return apply_filters( 'lifterlms_checkout_settings', $this->get_settings_default() );

		}

		return apply_filters( 'lifterlms_gateway_settings_' . $curr_section, $this->get_settings_gateway( $curr_section ) );
	}

	/**
	 * Retrieve the default checkout settings for the main section
	 *
	 * @since 3.17.5
	 * @since 3.30.3 Fixed spelling errors.
	 *
	 * @return array
	 */
	private function get_settings_default() {

		$currency_code_options = get_lifterlms_currencies();
		foreach ( $currency_code_options as $code => $name ) {
			$currency_code_options[ $code ] = $name . ' (' . get_lifterlms_currency_symbol( $code ) . ')';
		}

		$country_options = get_lifterlms_countries();
		foreach ( $country_options as $code => $name ) {
			$country_options[ $code ] = $name . ' (' . $code . ')';
		}

		return array(

			array(
				'type' => 'sectionstart',
				'id'   => 'checkout_settings_gateways_list_start',
			),

			array(
				'title' => __( 'Payment Gateways', 'lifterlms' ),
				'desc'  => sprintf( __( 'Gateways allow you to accept payments on your site. %1$1sLearn More%2$2s', 'lifterlms' ), '<a href="https://lifterlms.com/docs/what-payment-gateways-can-i-use-with-lifterlms/?utm_source=LifterLMS%20Plugin&utm_medium=Resource%20Screen&utm_campaign=Backend%20Help%20Page">', '</a>' ),
				'type'  => 'title',
				'id'    => 'checkout_settings_gateways_list_title',
			),

			array(
				'value' => $this->get_gateway_table_html(),
				'type'  => 'custom-html',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'checkout_settings_gateways_list_end',
			),

			array(
				'class' => 'top',
				'id'    => 'course_archive_options',
				'type'  => 'sectionstart',
			),

			array(
				'id'    => 'course_options',
				'title' => __( 'Checkout Settings', 'lifterlms' ),
				'type'  => 'title',
			),

			array(
				'title'             => __( 'Checkout Page', 'lifterlms' ),
				'desc'              => __( 'Page used for displaying the checkout form.', 'lifterlms' ) . ' ' . sprintf( __( 'Requires the %1$s[lifterlms_checkout]%2$s shortcode or the "Checkout" block.', 'lifterlms' ), '<code>', '</code>' ),
				'id'                => 'lifterlms_checkout_page_id',
				'type'              => 'select',
				'default'           => '',
				'class'             => 'llms-select2-post',
				'custom_attributes' => array(
					'data-post-type' => 'page',
				),
				'options'           => llms_make_select2_post_array( get_option( 'lifterlms_checkout_page_id', '' ) ),
			),

			array(
				'title'    => __( 'Confirm Payment', 'lifterlms' ),
				'desc'     => __( 'Payment confirmation endpoint slug', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_confirm_payment_endpoint',
				'type'     => 'text',
				'default'  => 'confirm-payment',
				'desc_tip' => true,
				'sanitize' => 'slug',
			),

			array(
				'title'   => __( 'Force SSL', 'lifterlms' ),
				'desc'    => __( 'Force secure checkout via SSL (https) on the checkout page(s).', 'lifterlms' ) .
								'<p class="description">' . sprintf( __( 'Requires an SSL certificate. %1$sLearn More%2$s', 'lifterlms' ), '<a href="https://lifterlms.com/docs/ssl-and-https/" target="_blank">', '</a>' ) . '</p>',
				'id'      => 'lifterlms_checkout_force_ssl',
				'type'    => 'checkbox',
				'default' => 'no',
			),

			array(
				'default' => 'yes',
				'desc'    => __( 'Enable automatic retry of failed recurring payments.', 'lifterlms' ) .
								'<p class="description">' . sprintf( esc_html__( 'Recover lost revenue from temporarily declined payment methods. %1$sLearn More%2$s', 'lifterlms' ), '<a href="https://lifterlms.com/docs/automatic-retry-failed-payments/" target="_blank">', '</a>' ) . '</p>',
				'id'      => 'lifterlms_recurring_payment_retry',
				'title'   => __( 'Retry Failed Payments', 'lifterlms' ),
				'type'    => 'checkbox',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'course_archive_options',
			),

			array(
				'type' => 'sectionstart',
				'id'   => 'general_options',
			),

			array(
				'title' => __( 'Currency Options', 'lifterlms' ),
				'type'  => 'title',
				'desc'  => __( 'The following options affect how prices are displayed on the frontend.', 'lifterlms' ),
				'id'    => 'pricing_options',
			),

			array(
				'class'    => 'llms-select2',
				'title'    => __( 'Country', 'lifterlms' ),
				'desc'     => __( 'Select the country LifterLMS should use as the default during transactions and registrations.', 'lifterlms' ),
				'id'       => 'lifterlms_country',
				'default'  => 'US',
				'type'     => 'select',
				'desc_tip' => false,
				'options'  => $country_options,
			),

			array(
				'class'    => 'llms-select2',
				'title'    => __( 'Currency', 'lifterlms' ),
				'desc'     => __( 'Select the currency LifterLMS should use to display prices and process transactions.', 'lifterlms' ),
				'id'       => 'lifterlms_currency',
				'default'  => 'USD',
				'type'     => 'select',
				'desc_tip' => false,
				'options'  => $currency_code_options,
			),

			array(
				'title'   => __( 'Currency Position', 'lifterlms' ),
				'desc'    => __( 'Customize the position and formatting of the currency symbol for displayed prices.', 'lifterlms' ),
				'id'      => 'lifterlms_currency_position',
				'default' => 'left',
				'type'    => 'select',
				'options' => array(
					'left'        => 'Left (' . sprintf( '%1$s%2$s', get_lifterlms_currency_symbol(), 99.99 ) . ')',
					'right'       => 'Right (' . sprintf( '%2$s%1$s', get_lifterlms_currency_symbol(), 99.99 ) . ')',
					'left_space'  => 'Left with Space (' . sprintf( '%1$s&nbsp;%2$s', get_lifterlms_currency_symbol(), 99.99 ) . ')',
					'right_space' => 'Right with Space (' . sprintf( '%2$s&nbsp;%1$s', get_lifterlms_currency_symbol(), 99.99 ) . ')',
				),
			),

			array(
				'title'   => __( 'Thousand Separator', 'lifterlms' ),
				'class'   => 'tiny',
				'desc'    => __( 'Choose the character to display as the thousand\'s place separator for displayed prices.', 'lifterlms' ),
				'id'      => 'lifterlms_thousand_separator',
				'type'    => 'text',
				'default' => ',',
			),

			array(
				'title'   => __( 'Decimal Separator', 'lifterlms' ),
				'class'   => 'tiny',
				'desc'    => __( 'Choose the character to display as the decimal separator for displayed prices.', 'lifterlms' ),
				'id'      => 'lifterlms_decimal_separator',
				'type'    => 'text',
				'default' => '.',
			),

			array(
				'title'   => __( 'Decimal Places', 'lifterlms' ),
				'class'   => 'tiny',
				'desc'    => __( 'Customize the number of decimal places for prices.', 'lifterlms' ),
				'id'      => 'lifterlms_decimals',
				'type'    => 'number',
				'default' => '2',
			),

			array(
				'title'   => __( 'Hide Zero Decimals', 'lifterlms' ),
				'desc'    => __( 'Automatically remove zero decimals from the end of displayed prices.', 'lifterlms' ),
				'id'      => 'lifterlms_trim_zero_decimals',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'general_options',
			),

		);
	}

	/**
	 * Retrieve settings for a gateway section
	 *
	 * @param    string $curr_section  gateway ID string
	 * @return   array
	 * @since    3.17.5
	 * @version  3.17.5
	 */
	private function get_settings_gateway( $curr_section ) {

		$settings = array();

		$settings[] = array(
			'type'  => 'sectionstart',
			'id'    => 'start_gateway_settings_' . $curr_section,
			'class' => 'top',
		);

		$gateway = llms()->payment_gateways()->get_gateway_by_id( $curr_section );
		if ( ! $gateway ) {

			$settings[] = array(
				'title' => __( 'Payment Gateway Settings', 'lifterlms' ),
				'type'  => 'title',
				'id'    => 'title_gateway_settings_' . $curr_section,
			);

			$settings[] = array(
				'title' => sprintf( __( 'Error: "%s" is not a valid payment gateway', 'lifterlms' ), $curr_section ),
				'type'  => 'subtitle',
				'id'    => 'title_gateway_settings_' . $curr_section,
			);

		} else {

			$settings[] = array(
				'title' => sprintf( __( '%s Payment Gateway Settings', 'lifterlms' ), $gateway->get_admin_title() ),
				'type'  => 'title',
				'id'    => 'title_gateway_settings_' . $curr_section,
			);

			$settings = array_merge( $settings, $gateway->get_admin_settings_fields() );

		}

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'end_gateway_settings_' . $curr_section,
		);

		return $settings;
	}

	/**
	 * Override default save method to save the display order of payment gateways
	 *
	 * @since 3.17.5
	 * @since 3.35.1 Verify nonce.
	 *
	 * @return   void
	 */
	public function save() {

		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'lifterlms-settings' ) ) {
			return;
		}

		// Save all custom fields.
		parent::save();

		// Save display order of gateways.
		foreach ( llms()->payment_gateways()->get_payment_gateways() as $id => $gateway ) {
			$option = $gateway->get_option_name( 'display_order' );
			if ( isset( $_POST[ $option ] ) ) {
				update_option( $option, absint( $_POST[ $option ] ) );
			}
		}
	}

	/**
	 * usort function used to ensure gateways are sorted by display order on the gateways table
	 *
	 * @param    obj $gateway_a  Payment Gateway instance
	 * @param    obj $gateway_b  Payment Gateway instance
	 * @return   int
	 * @since    3.17.5
	 * @version  3.17.5
	 */
	public function sort_gateways( $gateway_a, $gateway_b ) {

		$a_order = $gateway_a->get_display_order();
		$b_order = $gateway_b->get_display_order();

		if ( $a_order == $b_order ) {
			return 0;
		}

		return $a_order < $b_order ? -1 : 1;
	}
}

return new LLMS_Settings_Checkout();
