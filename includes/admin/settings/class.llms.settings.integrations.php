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
	 * @version  3.18.2
	 */
	private function get_table_html() {

		$integrations   = llms()->integrations()->get_integrations();
		$registered_ids = array();
		$objects        = array();
		foreach ( $integrations as $integration ) {
			if ( is_subclass_of( $integration, 'LLMS_Abstract_Integration' ) ) {
				$registered_ids[]            = $integration->id;
				$objects[ $integration->id ] = $integration;
			}
		}
		$matched_catalog_ids = LLMS_Admin_Catalog_Table::get_matched_catalog_ids( $registered_ids, 'integrations', $objects );

		ob_start();
		?>

		<table class="llms-table zebra text-left size-large llms-integrations-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Integration', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Description', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Installed', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Activated', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Enabled', 'lifterlms' ); ?></th>
					<th><?php esc_html_e( 'Documentation', 'lifterlms' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $integrations as $integration ) :
				if ( ! is_subclass_of( $integration, 'LLMS_Abstract_Integration' ) ) {
					continue;
				}
				$addon    = LLMS_Admin_Catalog_Table::get_addon_for_registered( $integration->id, 'integrations', $integration );
				$siblings = $addon ? LLMS_Admin_Catalog_Table::get_registered_sibling_count( $addon->get( 'id' ), $registered_ids, 'integrations', $objects ) : 1;
				$title    = LLMS_Admin_Catalog_Table::get_grouped_display_title( $integration->title, $integration->id, $addon, $siblings );
				$docs_url = LLMS_Admin_Catalog_Table::get_core_docs_url( $integration->id );
				if ( ! $docs_url && $addon ) {
					$docs_url = LLMS_Admin_Catalog_Table::get_addon_docs_url( $addon );
				}
				$state      = LLMS_Admin_Catalog_Table::get_row_plugin_state( $integration->id, $addon, $integration->is_installed() );
				$learn_more = ( $addon && ! $state['installed'] ) ? LLMS_Admin_Catalog_Table::get_product_url( $addon, 'Integrations Screen' ) : '';
				?>
				<tr>
					<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-settings&tab=' . $this->id . '&section=' . $integration->id ) ); ?>"><?php echo esc_html( $title ); ?></a></td>
					<?php
					LLMS_Admin_Catalog_Table::render_status_cells(
						wp_strip_all_tags( $integration->description ),
						$state['activated'],
						$docs_url,
						$state['installed'],
						$learn_more,
						$integration->is_enabled()
					);
					?>
				</tr>
			<?php endforeach; ?>
			<?php
			foreach ( LLMS_Admin_Catalog_Table::get_catalog_addons( 'integrations' ) as $addon ) :
				if ( in_array( $addon->get( 'id' ), $matched_catalog_ids, true ) ) {
					continue;
				}
				$title      = LLMS_Admin_Catalog_Table::get_display_title( $addon->get( 'title' ), $addon->get( 'id' ) );
				$url        = LLMS_Admin_Catalog_Table::get_catalog_row_url( $addon, 'Integrations Screen' );
				$learn_more = $addon->is_installed() ? '' : LLMS_Admin_Catalog_Table::get_product_url( $addon, 'Integrations Screen' );
				?>
				<tr>
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
}

return new LLMS_Settings_Integrations();
