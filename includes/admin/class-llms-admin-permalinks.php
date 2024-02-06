<?php
/**
 * LLMS_Admin_Header class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Permalink settings class
 */
class LLMS_Admin_Permalinks {

	/**
	 * Permalink settings.
	 *
	 * @var array
	 */
	private $permalinks = array();

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		switch_to_locale( get_locale() );

		// Filter on plugin_locale so load_plugin_textdomain loads the correct locale.
		add_filter( 'plugin_locale', 'get_locale' );

		llms_load_textdomain( 'lifterlms' );

		$this->settings_init();

		restore_previous_locale();

		// Remove filter.
		remove_filter( 'plugin_locale', 'get_locale' );

		llms_load_textdomain( 'lifterlms' );

		// TODO: Save settings
	}

	/**
	 * Show the available permalink settings
	 */
	public function settings_init() {
		add_settings_section( 'lifterlms-permalink', __( 'LifterLMS Permalinks', 'lifterlms' ), array( $this, 'settings' ), 'permalink' );

		$this->permalinks = llms_get_permalink_structure();
	}

	public function settings() {
		// TODO: Conditionally show message for Course Catalog and Membership Catalog if they are enabled
		?>
		<p><?php _e( 'LifterLMS uses custom post types and taxonomies to organize your courses and memberships. You can customize the URLs for these items here.', 'lifterlms' ); ?></p>

		<table class="form-table" role="presentation">
			<tbody>
			<tr>
				<th>
					<label for="course_base">
						<?php esc_html_e( 'Course Post Type' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_course_base" id="course_base" type="text" value="<?php echo esc_attr( $this->permalinks['course_base'] ); ?>" class="regular-text code">
				</td>
			</tr>
			</tbody>
		</table>

		<?php wp_nonce_field( 'llms-permalinks', 'llms-permalinks-nonce' ); ?>
		<?php
	}
}

return new LLMS_Admin_Permalinks();
