<?php
/**
 * Admin Settings Page Base
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 1.0.0
 * @version 3.37.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page Base class
 *
 * @since 1.0.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 3.35.0 Unslash input data.
 * @since 3.37.3 Add a constructor which registers the settings page and automatically saves and outputs settings content.
 *               Add public method stub `get_label()` which is used to automatically set the `$label` property on class initialization.
 *               Add utility method to generate a group of settings.
 */
class LLMS_Settings_Page {

	/**
	 * Allow settings page to determine if a rewrite flush is required
	 *
	 * @var boolean
	 */
	protected $flush = false;

	/**
	 * Settings identifier
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Settings page label / title.
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Tab priority
	 *
	 * Determines the order of the page when registered with the core settings array.
	 *
	 * @var int
	 */
	public $tab_priority = 20;

	/**
	 * Constructor.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	public function __construct() {

		$this->label = $this->set_label();

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), $this->tab_priority );

		if ( $this->id ) {

			add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );

		}
	}

	/**
	 * Add the settings page
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;
		return $pages;
	}

	/**
	 * Flushes rewrite rules when necessary
	 *
	 * @since 3.0.4
	 *
	 * @return void
	 */
	public function flush_rewrite_rules() {

		// Add the updated endpoints.
		$query = new LLMS_Query();
		$query->add_endpoints();

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Retrieve current section from URL var
	 *
	 * @since 3.17.5
	 * @since 3.35.0 Unslash input data.
	 *
	 * @return string
	 */
	protected function get_current_section() {
		return isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'main';
	}

	/**
	 * Retrieve the page label.
	 *
	 * Extending classes should override this to return a translated string used as the page's title.
	 *
	 * @since 3.37.3
	 *
	 * @return string
	 */
	protected function set_label() {
		return $this->id;
	}

	/**
	 * Generates a group of settings.
	 *
	 * @since 3.37.3
	 *
	 * @param string  $id Group ID. Used to create IDs for the start, end, and title fields.
	 * @param string  $title Title of the group (should be translatable).
	 * @param string  $title_desc (Optional) title field description text.
	 * @param array[] $settings Array of settings field arrays.
	 * @return array[]
	 */
	protected function generate_settings_group( $id, $title, $title_desc = '', $settings = array() ) {

		$start = array(
			array(
				'type' => 'sectionstart',
				'id'   => $id,
			),
			array(
				'type'  => 'title',
				'id'    => sprintf( '%s_title', $id ),
				'title' => $title,
				'desc'  => $title_desc,
			),
		);

		$end = array(
			array(
				'type' => 'sectionend',
				'id'   => sprintf( '%s_end', $id ),
			),
		);

		return array_merge( $start, $settings, $end );
	}

	/**
	 * Get the page sections (stub)
	 *
	 * When overriding, this should return an associative array where the key is the
	 * section id and the value is the (translated) section title. The "default" tab
	 * should always use the id "main".
	 *
	 * @since 1.0.0
	 * @since 3.17.5 Return an array instead of void.
	 *
	 * @return array
	 */
	public function get_sections() {
		return array();
	}

	/**
	 * Retrieve the page's settings (stub)
	 *
	 * @since 3.17.5
	 *
	 * @return array
	 */
	public function get_settings() {
		return array();
	}

	/**
	 * Output the settings fields
	 *
	 * @since 1.0.0
	 * @since 3.17.5 Unknown.
	 *
	 * @return void
	 */
	public function output() {
		LLMS_Admin_Settings::output_fields( $this->get_settings() );
	}

	/**
	 * Output settings sections as tabs and set post href
	 *
	 * @since 3.17.5
	 *
	 * @return void
	 */
	public function output_sections_nav() {

		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		$curr = $this->get_current_section();
		?>
		<nav class="llms-nav-tab-wrapper llms-nav-text">
			<ul class="llms-nav-items">
				<?php foreach ( $sections as $key => $title ) : ?>
					<li class="llms-nav-item<?php echo ( $key === $curr ) ? ' llms-active' : ''; ?>">
						<a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-settings&tab=' . $this->id . '&section=' . $key ) ); ?>"><?php echo esc_html( $title ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
	}

	/**
	 * Save the settings field values
	 *
	 * @since 1.0.0
	 * @since 3.17.5 Unknown.
	 *
	 * @return void
	 */
	public function save() {

		LLMS_Admin_Settings::save_fields( $this->get_settings() );
		if ( $this->flush ) {
			add_action( 'shutdown', array( $this, 'flush_rewrite_rules' ) );
		}
	}
}
