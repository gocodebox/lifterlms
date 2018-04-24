<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Settings Page Base Class
 * @since    1.0.0
 * @version  3.17.5
 */
class LLMS_Settings_Page {

	/**
	 * Allow settings page to determine if a rewrite flush is required
	 * @var      boolean
	 * @since    3.0.4
	 * @version  3.0.4
	 */
	protected $flush = false;

	/**
	 * Add the settings page
	 * @return array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;
		return $pages;
	}

	/**
	 * Flushes rewrite rules when necessary
	 * @return   void
	 * @since    3.0.4
	 * @version  3.0.4
	 */
	public function flush_rewrite_rules() {

		// add the updated endpoints
		$q = new LLMS_Query();
		$q->add_endpoints();

		// flush rewrite rules
		flush_rewrite_rules();

	}

	/**
	 * Retrieve current section from URL var
	 * @return   string
	 * @since    3.17.5
	 * @version  3.17.5
	 */
	protected function get_current_section() {

		return isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'main';

	}

	/**
	 * Get the page sections (stub)
	 * @return   array
	 * @since    1.0.0
	 * @version  3.17.5
	 */
	public function get_sections() {

		return array();

	}

	/**
	 * Retrieve the page's settings (stub)
	 * @return   [array
	 * @since    3.17.5
	 * @version  3.17.5
	 */
	public function get_settings() {

		return array();

	}

	/**
	 * Output the settings fields
	 * @return   void
	 * @since    1.0.0
	 * @version  3.17.5
	 */
	public function output() {

		LLMS_Admin_Settings::output_fields( $this->get_settings() );

	}

	/**
	 * Output settings sections as tabs and set post href
	 * @return array
	 * @since    3.17.5
	 * @version  3.17.5
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
						<a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-settings&tab=' . $this->id . '&section=' . $key ) ); ?>"><?php echo $title; ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
	}

	/**
	 * Save the settings field values
	 * @return   void
	 * @since    1.0.0
	 * @version  3.17.5
	 */
	public function save() {

		LLMS_Admin_Settings::save_fields( $this->get_settings() );

	    if ( $this->flush ) {

	    	add_action( 'shutdown', array( $this, 'flush_rewrite_rules' ) );

	    }

	}

}
