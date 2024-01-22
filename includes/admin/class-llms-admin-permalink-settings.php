<?php
/**
 * LLMS_Admin_Permalink_Settings class
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Permalink Settings.
 *
 * @since [version]
 */
class LLMS_Admin_Permalink_Settings {

	/**
	 * Rewrite slugs.
	 *
	 * @since [version]
	 * @var array
	 */
	private static $rewrite_slugs = array();

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'init' ) );
		add_action( 'admin_init', array( __CLASS__, 'save' ) );
	}

	/**
	 * Init settings.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function init() {

		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms-functions-l10n.php';

		self::$rewrite_slugs = LLMS_Post_Types::get_rewrite_slugs( true, false );

		$setting_fields = array(
			'llms_course_slug'              => array(
				'label'    => esc_html__( 'Course Post Type', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course',
					'type'  => 'post_types',
					'field' => 'slug',
				),
			),
			'llms_membership_slug'          => array(
				'label'    => esc_html__( 'Membership Post Type', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'llms_membership',
					'type'  => 'post_types',
					'field' => 'slug',
				),
			),
			'llms_lesson_slug'              => array(
				'label'    => esc_html__( 'Lesson Post Type', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'lesson',
					'type'  => 'post_types',
					'field' => 'slug',
				),
			),
			'llms_quiz_slug'                => array(
				'label'    => esc_html__( 'Quiz Post Type', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'llms_quiz',
					'type'  => 'post_types',
					'field' => 'slug',
				),
			),
			'llms_certificate_slug'         => array(
				'label'    => esc_html__( 'Certificate Template Post Type', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'llms_certificate',
					'type'  => 'post_types',
					'field' => 'slug',
				),
			),
			'llms_mycertificate_slug'       => array(
				'label'    => esc_html__( 'Earned Certificate Post Type', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'llms_my_certificate',
					'type'  => 'post_types',
					'field' => 'slug',
				),
			),
			'llms_course_archive_slug'      => array(
				'label'    => esc_html__( 'Course Archive base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course',
					'type'  => 'post_types',
					'field' => 'archive_slug',
				),
			),
			'llms_membership_archive_slug'  => array(
				'label'    => esc_html__( 'Membership Archive base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'llms_membership',
					'type'  => 'post_types',
					'field' => 'archive_slug',
				),
			),
			'llms_course_category_slug'     => array(
				'label'    => esc_html__( 'Course Category base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course_cat',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_course_tag_slug'          => array(
				'label'    => esc_html__( 'Course Tag base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course_tag',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_course_track_slug'        => array(
				'label'    => esc_html__( 'Course Track base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course_track',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_course_difficulty_slug'   => array(
				'label'    => esc_html__( 'Course Difficulty base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course_difficulty',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_membership_category_slug' => array(
				'label'    => esc_html__( 'Membership Category base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'membership_cat',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_membership_tag_slug'      => array(
				'label'    => esc_html__( 'Membership Tag base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'membership_tag',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
		);

		// If the courses catalog is a static page, remove the related archive settings.
		$course_catalog_id = llms_get_page_id( 'courses' );
		if ( $course_catalog_id && get_post( $course_catalog_id ) ) {
			unset( $setting_fields['llms_course_archive_slug'] );
		}

		// If the memberships catalog is a static page, remove the related archive settings.
		$memberships_catalog_id = llms_get_page_id( 'memberships' );
		if ( $memberships_catalog_id && get_post( $memberships_catalog_id ) ) {
			unset( $setting_fields['llms_membership_archive_slug'] );
		}

		/**
		 * Filters the permalink settings fields.
		 *
		 * @since [version]
		 *
		 * @param array $settings_fields Configuration used to generate the LifterLMS related permalink fields.
		 */
		$setting_fields = apply_filters( 'llms_permalink_settings_fields', $setting_fields );

		// Add LifterLMS section to the permalink settings page.
		add_settings_section(
			'llms_permalinks',
			__( 'LifterLMS', 'lifterlms' ),
			array( __CLASS__, 'section_description' ),
			'permalink'
		);

		// Add the fields to the LifterLMS section.
		foreach ( $setting_fields as $field_name => $config ) {
			add_settings_field(
				$field_name,
				$config['label'],
				$config['callback'],
				'permalink',
				$config['section'] ?? 'llms_permalinks',
				$config['args'] ?? array()
			);
		}
	}

	/**
	* Echo the LifterLMS Permalinks section description.
	*
	* @since [version]
	*/
    public static function section_description() {
		echo '<p>' . esc_html__( 'LifterLMS uses custom post types and taxonomies to organize your courses and memberships. You can customize the URLs for these items here.', 'lifterlms' ) . '</p>';
		$course_catalog_id = llms_get_page_id( 'courses' );
		if ( $course_catalog_id && get_post( $course_catalog_id ) ) {
			?>
			<p>
				<?php echo esc_html__( 'Note: The Courses Catalog is currently set to a static page.', 'lifterlms' ) ?>
				<a href="<?php echo esc_url( get_edit_post_link( $course_catalog_id ) ) ?>">
				<?php echo esc_html__( 'You can edit the page slug to change its location.', 'lifterlms' ) ?>
				</a>
			</p>
			<?php
		}

		$memberships_catalog_id = llms_get_page_id( 'memberships' );
		if ( $memberships_catalog_id && get_post( $memberships_catalog_id ) ) {
		?>
		<p>
			<?php echo esc_html__( 'Note: The Memberships Catalog is currently set to a static page.', 'lifterlms' ) ?>
			<a href="<?php echo esc_url( get_edit_post_link( $memberships_catalog_id ) ) ?>">
				<?php echo esc_html__( 'You can edit the page slug to change its location.', 'lifterlms' ) ?>
			</a>
		</p>
		<?php
		}
	}

	/**
	 * Render a slug input.
	 *
	 * @since [version]
	 *
	 * @param array $args Array of arguments.
	 */
	public static function render_slug_input( $args ) {

		$name  = $args['name'] ?? '';
		$value = self::$rewrite_slugs[ $args['type'] ][ $args['name'] ][ $args['field'] ] ?? '';
		?>
		<input
			name="<?php echo esc_attr( "llms_{$args['name']}_{$args['field']}" ); ?>"
			type="text"
			class="regular-text code"
			value="<?php echo esc_attr( $value ); ?>" />
		<?php

	}

	/**
	 * Save the settings.
	 */
	public static function save() {

		// We need to save the options ourselves; settings api does not trigger save for the permalinks page.
		if ( isset( $_POST['permalink_structure'] ) ) {

			check_admin_referer( 'update-permalink' );

			foreach ( self::$rewrite_slugs as $type => $slugs ) {
				foreach ( $slugs as $name => $arr ) {
					foreach ( $arr as $stype => $slug ) {
						if ( ! empty( $_POST[ "llms_{$name}_{$stype}" ] ) ) {

							$to_save = sanitize_text_field( wp_unslash( $_POST[ "llms_{$name}_{$stype}" ] ) );

							self::$rewrite_slugs[ $type ][ $name ][ $stype ] =
								rtrim(
									ltrim(
										str_replace( '#', '', $to_save ),
										'/\\'
									),
									'/\\'
								);
						}
					}
				}
			}
			update_option( 'lifterlms_rewrite_slugs', self::$rewrite_slugs );

		}

	}

}

return new LLMS_Admin_Permalink_Settings();
