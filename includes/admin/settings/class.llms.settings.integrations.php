<?php
/**
 * Admin Settings Page, Integrations Tab
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 1.0.0
 * @version 3.18.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page, Integrations Tab class
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 1.0.0
 * @since 3.18.2 Unknown.
 * @since 10.0.6 Replaced the "Integration ID" column with a "Description" column
 *              and added a "Configured" column to the integrations list.
 */
class LLMS_Settings_Integrations extends LLMS_Settings_Page {

	/**
	 * Constructor
	 * executes settings tab actions
	 *
	 * @since    1.0.0
	 * @version  3.18.2
	 */
	public function __construct() {

		$this->id    = 'integrations';
		$this->label = __( 'Integrations', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_sections_' . $this->id, array( $this, 'output_sections_nav' ) );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get default settings array for the main integrations tab
	 *
	 * @return   array
	 * @since    3.18.2
	 * @version  3.18.2
	 */
	private function get_default_settings() {

		$settings = array(
			array(
				'type' => 'sectionstart',
				'id'   => 'checkout_settings_integrations_list_start',
			),
			array(
				'title' => __( 'Integrations', 'lifterlms' ),
				'type'  => 'title',
				'id'    => 'checkout_settings_integrations_list_title',
			),
			array(
				'value' => $this->get_table_html(),
				'type'  => 'custom-html',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'checkout_settings_integrations_list_end',
			),
		);

		return apply_filters( 'llms_integrations_settings_default', $settings );
	}

	/**
	 * Get the page sections
	 *
	 * @return   array
	 * @since    3.18.2
	 * @version  3.18.2
	 */
	public function get_sections() {

		$sections = array();

		$integrations = llms()->integrations()->get_integrations();

		foreach ( $integrations as $int ) {
			$sections[ $int->id ] = trim( str_replace( 'LifterLMS', '', $int->title ) );
		}

		$sections = array_merge(
			array(
				'main' => __( 'Integrations', 'lifterlms' ),
			),
			$sections
		);

		return apply_filters( 'llms_integration_settings_sections', $sections );
	}

	/**
	 * Get settings array
	 *
	 * @return   array
	 * @since    1.0.0
	 * @version  3.18.2
	 */
	public function get_settings() {

		$curr_section = $this->get_current_section();

		if ( 'main' === $curr_section ) {

			return apply_filters( 'lifterlms_integrations_settings', $this->get_default_settings() );

		}

		return apply_filters( 'lifterlms_integrations_settings_' . $curr_section, array() );
	}

	/**
	 * Get HTML for the integrations table
	 *
	 * @return   string
	 * @since    3.18.2
	 * @version  10.0.6
	 */
	private function get_table_html() {

		$integrations = llms()->integrations()->get_integrations();
		ob_start();
		?>

		<table class="llms-table zebra text-left size-large llms-integrations-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Integration', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Description', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Installed', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Configured', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Enabled', 'lifterlms' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $integrations as $integration ) :
				if ( ! is_subclass_of( $integration, 'LLMS_Abstract_Integration' ) ) {
					continue;
				}
				?>
				<tr>
					<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-settings&tab=' . $this->id . '&section=' . $integration->id ) ); ?>"><?php echo esc_html( $integration->title ); ?></a></td>
					<td>
						<?php echo wp_kses_post( $integration->description ); ?>
						<?php if ( ! empty( $integration->learn_more_url ) ) : ?>
							<a href="<?php echo esc_url( $integration->learn_more_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn More', 'lifterlms' ); ?></a>
						<?php endif; ?>
					</td>
					<td class="status available">
						<?php if ( $integration->is_installed() ) : ?>
							<span class="tip--bottom-right" data-tip="<?php esc_attr_e( 'Installed', 'lifterlms' ); ?>">
								<span class="screen-reader-text"><?php esc_html_e( 'Installed', 'lifterlms' ); ?></span>
								<i class="fa fa-check-circle" aria-hidden="true"></i>
							</span>
						<?php else : ?>
							&ndash;
						<?php endif; ?>
					</td>
					<td class="status configured">
						<?php if ( $integration->is_configured() ) : ?>
							<span class="tip--bottom-right" data-tip="<?php esc_attr_e( 'Configured', 'lifterlms' ); ?>">
								<span class="screen-reader-text"><?php esc_html_e( 'Configured', 'lifterlms' ); ?></span>
								<i class="fa fa-check-circle" aria-hidden="true"></i>
							</span>
						<?php else : ?>
							<span class="tip--bottom-right" data-tip="<?php echo ! empty( $integration->configured_help ) ? esc_attr( $integration->configured_help ) : esc_attr__( 'Not configured', 'lifterlms' ); ?>">
								<span class="screen-reader-text"><?php esc_html_e( 'Not configured', 'lifterlms' ); ?></span>
								&ndash;
							</span>
						<?php endif; ?>
					</td>
					<td class="status enabled">
						<?php if ( $integration->is_enabled() ) : ?>
							<span class="tip--bottom-right" data-tip="<?php esc_attr_e( 'Enabled', 'lifterlms' ); ?>">
								<span class="screen-reader-text"><?php esc_html_e( 'Enabled', 'lifterlms' ); ?></span>
								<i class="fa fa-check-circle" aria-hidden="true"></i>
							</span>
						<?php else : ?>
							&ndash;
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		return ob_get_clean();
	}
}

return new LLMS_Settings_Integrations();
