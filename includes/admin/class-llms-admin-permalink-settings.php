<?php
/**
 * LLMS_Admin_Permalink_Settings class.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Permalink Settings
 *
 * @since [version]
 */
class LLMS_Admin_Permalink_Settings {

	/**
	 * Permalink settings.
	 *
	 * @since [version]
	 * @var array
	 */
	private static $rewrite_slugs = array();

	/**
	 * Constructor,
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
		self::$rewrite_slugs = LLMS_Post_Types::get_rewrite_slugs();

		$setting_fields = array(
			'llms_course_category_slug'     => array(
				'label'    => esc_html__( 'Course category base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course_cat',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_course_tag_slug'          => array(
				'label'    => esc_html__( 'Course tag base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course_tag',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_course_track_slug'        => array(
				'label'    => esc_html__( 'Course track base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course_track',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_course_difficulty_slug'   => array(
				'label'    => esc_html__( 'Course difficulty base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'course_difficulty',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_membership_category_slug' => array(
				'label'    => esc_html__( 'Membership category base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'membership_cat',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
			'llms_membership_tag_slug'      => array(
				'label'    => esc_html__( 'Membership tag base', 'lifterlms' ),
				'callback' => array( __CLASS__, 'render_slug_input' ),
				'args'     => array(
					'name'  => 'membership_tag',
					'type'  => 'taxonomies',
					'field' => 'slug',
				),
			),
		);

		foreach ( $setting_fields as $field_name => $config ) {
			add_settings_field(
				$field_name,
				$config['label'],
				$config['callback'],
				'permalink',
				$config['section'] ?? 'optional',
				$config['args'] ?? array(),
			);
		}
	}

	/**
	 * Render a slug input.
	 *
	 * @since [version]
	 */
	public static function render_slug_input( $args ) {
		$name  = $args['name'] ?? '';
		$value = self::$rewrite_slugs[ $args['type'] ][ $args['name'] ][ $args['field'] ] ?? '';
		?>
		<input name="<?php echo "llms_{$args['name']}_{$args['field']}"; ?>" type="text" class="regular-text code" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

	/**
	 * Save the settings.
	 */
	public static function save() {
		if ( ! is_admin() ) {
			return;
		}

		// We need to save the options ourselves; settings api does not trigger save for the permalinks page.
		if ( isset( $_POST['permalink_structure'] ) ) {

			$saved_slugs = (array) get_option( 'lifterlms_rewrite_slugs', array() );
			foreach ( self::$rewrite_slugs as $type => $slugs ) {
				foreach ( $slug as $name => $arr ) {
					foreach ( $arr as $stype => $slug ) {
						if ( isset( $_POST["llms_{$name}_{$stype}"] ) ) {
							$saved_slugs[ $type ][ $name ][ $stype ] = wp_unslash( $_POST["llms_{$name}_{$stype}"] );
						}
					}
				}
			}
			update_option( 'lifterlms_rewrite_slugs', $saved_slugs );
			self::$rewrite_slugs = $saved_slugs;
		}
	}

}

return new LLMS_Admin_Permalink_Settings();
