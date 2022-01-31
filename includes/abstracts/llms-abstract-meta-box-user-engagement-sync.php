<?php
/**
 * LLMS_Abstract_Meta_Box_User_Engagement_Sync class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base meta box class for syncing between awarded engagements (certificates or achievements) and templates.
 *
 * @since [version]
 */
abstract class LLMS_Abstract_Meta_Box_User_Engagement_Sync extends LLMS_Admin_Metabox {

	/**
	 * An awarded user engagement.
	 *
	 * @var int
	 */
	const AWARDED = 0;

	/**
	 * Change all characters in the string to lowercase.
	 *
	 * @var int
	 */
	const CASE_LOWER = 0;

	/**
	 * Do not change the case of the string.
	 *
	 * @var int
	 */
	const CASE_NO_CHANGE = 1;

	/**
	 * Change all characters in the string to uppercase.
	 *
	 * @var int
	 */
	const CASE_UPPER = 2;

	/**
	 * Change the first character in the string to uppercase.
	 *
	 * @var int
	 */
	const CASE_UPPER_FIRST = 3;

	/**
	 * Change the first character of each word in the string to uppercase.
	 *
	 * @var int
	 */
	const CASE_UPPER_WORDS = 4;

	/**
	 * The plural version of the user engagement name.
	 *
	 * @var int
	 */
	const PLURAL = 1;

	/**
	 * The singular version of the user engagement name.
	 *
	 * @var int
	 */
	const SINGULAR = 2;

	/**
	 * A user engagement template.
	 *
	 * @var int
	 */
	const TEMPLATE = 3;

	/**
	 * The context to register the meta box with.
	 *
	 * Accepts anything that can be passed to WP core add_meta_box() function: 'normal', 'side', 'advanced'.
	 *
	 * @var string
	 */
	public $context = 'side';

	/**
	 * The type of the user engagement, e.g. 'achievement' or 'certificate'.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type;

	/**
	 * If true, we are syncing all awarded engagements with their template,
	 * else we are syncing a single awarded engagement with its template.
	 *
	 * @since [version]
	 *
	 * @var bool
	 */
	protected $is_syncing_all_awarded_engagements;

	/**
	 * The post type of an awarded engagement, e.g. 'llms_my_achievement' or 'llms_my_certificate'.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_awarded;

	/**
	 * The post type of an engagement template, e.g. 'llms_achievement' or 'llms_certificate'.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $post_type_template;

	/**
	 * Changes the case of a string.
	 *
	 * @since [version]
	 *
	 * @param string $string The string to transform the case of.
	 * @param int    $case   One of the CASE_ constants from LLMS_Abstract_Meta_Box_User_Engagement_Sync.
	 * @return string
	 */
	private function change_case( $string, $case ) {

		switch ( $case ) {
			case self::CASE_LOWER:
				$string = strtolower( $string );
				break;
			case self::CASE_UPPER:
				$string = strtoupper( $string );
				break;
			case self::CASE_UPPER_FIRST:
				$string = ucfirst( $string );
				break;
			case self::CASE_UPPER_WORDS:
				$string = ucwords( $string );
				break;
			case self::CASE_NO_CHANGE:
			default:
		}

		return $string;
	}

	/**
	 * Configure the meta box settings.
	 *
	 * @since [version] Refactored from LLMS_Meta_Box_Award_Certificate_Sync::configure() and
	 *              LLMS_Meta_Box_Certificate_Template_Sync::configure().
	 *
	 * @return void
	 */
	public function configure() {

		// Try to load the post being edited.
		if ( is_null( $this->post ) ) {
			$this->post = get_post( llms_filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) );
		}

		// There is no need to configure this meta box if we're not editing an engagement template or awarded engagement.
		if (
			is_null( $this->post ) ||
			! in_array( $this->post->post_type, array( $this->post_type_awarded, $this->post_type_template ) )
		) {
			return;
		}

		$this->id = "{$this->engagement_type}_sync";

		if ( $this->post->post_type === $this->post_type_template ) {
			$this->is_syncing_all_awarded_engagements = true;
			$plural_or_singular                       = self::PLURAL;
		} else {
			$this->is_syncing_all_awarded_engagements = false;
			$plural_or_singular                       = self::SINGULAR;
		}

		$this->title   = sprintf(
			// translators: %1$s: Awarded engagement post type plural label.
			__( 'Sync %1$s', 'lifterlms' ),
			$this->get_engagement_type_name( self::AWARDED, $plural_or_singular, self::CASE_NO_CHANGE )
		);
	}

	/**
	 * Returns an awarded child LLMS_Abstract_User_Engagement instance for the given post or false if not found.
	 *
	 * @since [version]
	 *
	 * @param WP_Post|int|null $post A WP_Post object or a WP_Post ID. A falsy value will use
	 *                               the current global `$post` object (if one exists).
	 * @return LLMS_Abstract_User_Engagement|false
	 */
	protected function get_awarded_engagement( $post ) {

		$post = get_post( $post );
		if ( ! $post || "llms_my_$this->engagement_type" !== $post->post_type ) {
			return false;
		}

		$class = 'LLMS_User_' . ucfirst( $this->engagement_type );

		return new $class( $post );
	}

	/**
	 * Returns the number of user engagements that have been awarded from the template.
	 *
	 * @since [version] Refactored from LLMS_Meta_Box_Certificate_Template_Sync::sync_action().
	 *
	 * @return int
	 */
	private function get_awarded_engagements_number() {

		$awarded_engagements_number = ( new LLMS_Awards_Query(
			array(
				'fields'    => 'ids',
				'templates' => $this->post->ID,
				'per_page'  => 1,
				'status'    => array(
					'publish',
					'future',
				),
				'type'      => $this->engagement_type,
			)
		) )->get_found_results();

		return $awarded_engagements_number;
	}

	/**
	 * Returns the label name for the given post type.
	 *
	 * @since [version]
	 *
	 * @param WP_Post_Type|null $post_type_object   WP_Post_Type object if it exists, null otherwise.
	 * @param int               $plural_or_singular Either the PLURAL or SINGULAR constant from
	 *                                              LLMS_Abstract_Meta_Box_User_Engagement_Sync.
	 * @return string
	 */
	private function get_engagement_label_name( $post_type_object, $plural_or_singular ) {

		if ( is_null( $post_type_object ) ) {
			$name = __( 'Unknown Engagement Type', 'lifterlms' );
		} elseif ( self::PLURAL === $plural_or_singular ) {
			$name = $post_type_object->labels->name;
		} else {
			$name = $post_type_object->labels->singular_name;
		}

		return $name;
	}

	/**
	 * Retrieves a child LLMS_Abstract_User_Engagement template instance for a given post or false if not found.
	 *
	 * @since [version]
	 *
	 * @param WP_Post|int|null $post A WP_Post object or a WP_Post ID. A falsy value will use
	 *                               the current global `$post` object (if one exists).
	 * @return LLMS_Abstract_User_Engagement|false
	 */
	protected function get_engagement_template( $post ) {

		$post = get_post( $post );
		if ( ! $post || "llms_$this->engagement_type" !== $post->post_type ) {
			return false;
		}

		$class = 'LLMS_User_' . ucfirst( $this->engagement_type );

		return new $class( $post );
	}

	/**
	 * Returns the translated name of this user engagement type.
	 *
	 * @since [version]
	 *
	 * @param int $awarded_or_template Either the AWARDED or TEMPLATE constant from LLMS_Abstract_Meta_Box_User_Engagement_Sync.
	 * @param int $plural_or_singular  Either the PLURAL or SINGULAR constant from LLMS_Abstract_Meta_Box_User_Engagement_Sync.
	 * @param int $case                One of the CASE_ constants from LLMS_Abstract_Meta_Box_User_Engagement_Sync.
	 * @return string
	 */
	protected function get_engagement_type_name( $awarded_or_template, $plural_or_singular, $case ) {

		$post_type_object = $this->get_engagement_type_object( $awarded_or_template );
		$name             = $this->get_engagement_label_name( $post_type_object, $plural_or_singular );
		$name             = $this->change_case( $name, $case );

		return $name;
	}

	/**
	 * Returns the post type object for this awarded engagement or engagement template.
	 *
	 * LifterLMS post types are defined in {@see LLMS_Post_Types::register_post_types()}.
	 *
	 * @since [version]
	 *
	 * @param int $awarded_or_template Either the AWARDED or TEMPLATE constant from LLMS_Abstract_Meta_Box_User_Engagement_Sync.
	 * @return WP_Post_Type|null
	 */
	private function get_engagement_type_object( $awarded_or_template ) {

		$post_type_name   = self::AWARDED === $awarded_or_template ? $this->post_type_awarded : $this->post_type_template;
		$post_type_object = get_post_type_object( $post_type_name );

		return $post_type_object;
	}

	/**
	 * Not used because our meta box doesn't use the standard fields API.
	 *
	 * @since [version] Refactored from LLMS_Meta_Box_Award_Certificate_Sync::get_fields() and
	 *              LLMS_Meta_Box_Certificate_Template_Sync::get_fields().
	 *
	 * @return array
	 */
	public function get_fields() {

		return array();
	}

	/**
	 * Function to field WP::output() method call.
	 *
	 * @see LLMS_Admin_Metabox::register()
	 * @see do_meta_boxes()
	 *
	 * @since [version] Refactored from LLMS_Meta_Box_Award_Certificate_Sync::output() and
	 *              LLMS_Meta_Box_Certificate_Template_Sync::output().
	 *
	 * @return void
	 */
	public function output() {

		$sync_action = $this->sync_action();

		if ( ! $sync_action ) {
			$sync_action = sprintf(
				// translators: %1$s: User engagement template post type singular label, %2$s: Awarded engagement post type plural label.
				__( 'This %1$s has no %2$s to sync.', 'lifterlms' ),
				$this->get_engagement_type_name( self::TEMPLATE, self::SINGULAR, self::CASE_LOWER ),
				$this->get_engagement_type_name( self::AWARDED, self::PLURAL, self::CASE_LOWER )
			);
		}

		// Output the HTML.
		echo '<div class="llms-mb-container">';
		do_action( 'llms_metabox_before_content', $this->id );
		echo $sync_action;
		do_action( 'llms_metabox_after_content', $this->id );
		echo '</div>';
	}

	/**
	 * Returns the sync action description and button HTML for a meta box on an engagement template or an awarded engagement.
	 *
	 * @since [version] Refactored from LLMS_Meta_Box_Certificate_Template_Sync::sync_action().
	 *
	 * @return string
	 */
	private function sync_action() {

		if ( $this->is_syncing_all_awarded_engagements ) {
			$awarded_number = $this->get_awarded_engagements_number();

			if ( ! $awarded_number ) {
				return '';
			}

			$awarded_label    = $this->get_engagement_type_name(
				self::AWARDED,
				( $awarded_number > 1 ) ? self::PLURAL : self::SINGULAR,
				self::CASE_LOWER
			);
			$sync_alert       = sprintf(
				/* translators: 1: number of awarded engagements, 2: awarded engagement post type label (singular or plural), 3: engagement template post type singular label */
				__( 'This action will replace the current title, content, background etc. of %1$d %2$s with the ones from this %3$s.\nAre you sure you want to proceed?', 'lifterlms' ),
				$awarded_number,
				$awarded_label,
				$this->get_engagement_type_name( self::TEMPLATE, self::SINGULAR, self::CASE_LOWER )
			);
			$sync_description = sprintf(
				/* translators: 1: number of awarded engagements, 2: awarded engagement post type label (singular or plural), 3: engagement template post type singular label */
				__( 'Sync %1$d %2$s with this %3$s.', 'lifterlms' ),
				$awarded_number,
				$awarded_label,
				$this->get_engagement_type_name( self::TEMPLATE, self::SINGULAR, self::CASE_LOWER )
			);
		} else {
			$awarded_model = $this->get_awarded_engagement( $this->post->ID );
			$template_id   = $awarded_model ? $awarded_model->get( 'parent' ) : false;

			if ( empty( $template_id ) || ! $this->get_engagement_template( $template_id ) ) {
				return '';
			}

			$awarded_label    = $this->get_engagement_type_name( self::AWARDED, self::SINGULAR, self::CASE_LOWER );
			$sync_alert       = sprintf(
				/* translators: 1: awarded engagement post type singular label, 2: engagement template post type singular label */
				__( 'This action will replace the current title, content, background etc. of this %1$s with the ones from the %2$s.\nAre you sure you want to proceed?', 'lifterlms' ),
				$awarded_label,
				$this->get_engagement_type_name( self::TEMPLATE, self::SINGULAR, self::CASE_LOWER )
			);
			$sync_description = sprintf(
				/* translators: 1: awarded engagement post type singular label, 2: link to edit the engagement template, 3: engagement template post type singular label, 4: closing anchor tag */
				__( 'Sync this %1$s with its %2$s%3$s%4$s.', 'lifterlms' ),
				$awarded_label,
				'<a href="' . get_edit_post_link( $template_id ) . '" target="_blank">',
				$this->get_engagement_type_name( self::TEMPLATE, self::SINGULAR, self::CASE_LOWER ),
				'</a>'
			);
		}

		$base_url = remove_query_arg( 'action' ); // Current URL without 'action' arg.
		$sync_url = add_query_arg(
			'action',
			"sync_awarded_$this->engagement_type" . ( $this->is_syncing_all_awarded_engagements ? 's' : '' ),
			wp_nonce_url(
				$base_url,
				"llms-$this->engagement_type-sync-actions",
				"_llms_{$this->engagement_type}_sync_actions_nonce"
			)
		);

		$sync_alert   = str_replace( "'", "\'", $sync_alert );
		$on_click     = "return confirm('$sync_alert')";
		$button_label = __( 'Sync', 'lifterlms' );

		return <<<HEREDOC

<p>$sync_description</p>
<p style="text-align: right; margin: 1em 0;">
<a href="$sync_url" class="llms-button-primary sync-action full small" onclick="$on_click" style="box-sizing:border-box;">$button_label</a>
</p>

HEREDOC;
	}
}
