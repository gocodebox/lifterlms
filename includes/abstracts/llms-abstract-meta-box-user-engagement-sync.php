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
abstract class LLMS_Abstract_Meta_Box_User_Engagement_Sync
	extends LLMS_Admin_Metabox
	implements LLMS_Interface_User_Engagement_Type {

	use LLMS_Trait_User_Engagement_Type;

	/**
	 * The context to register the meta box with.
	 *
	 * Accepts anything that can be passed to WP core add_meta_box() function: 'normal', 'side', 'advanced'.
	 *
	 * @var string
	 */
	public $context = 'side';

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
			/* translators: %s: plural awarded engagement type */
			__( 'Sync %s', 'lifterlms' ),
			$this->get_engagement_type_name( $plural_or_singular, LLMS_Case::NO_CHANGE, self::AWARDED )
		);
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
				/* translators: 1: singular lowercase engagement template type, 2: plural lowercase awarded engagement type, 3: singular uppercase first letter engagement template type, 4: plural uppercase first letter awarded engagement type */
				__( 'This %1$s has no %2$s to sync.', 'lifterlms' ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::TEMPLATE ),
				$this->get_engagement_type_name( self::PLURAL, LLMS_Case::LOWER, self::AWARDED ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::TEMPLATE ),
				$this->get_engagement_type_name( self::PLURAL, LLMS_Case::UPPER_FIRST, self::AWARDED )
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

			$plural_or_singular = ( $awarded_number > 1 ) ? self::PLURAL : self::SINGULAR;
			$sync_alert         = sprintf(
				/* translators: 1: number of awarded engagements, 2: plural or singular lowercase awarded engagement type, 3: singular lowercase engagement template type, 4: plural or singular uppercase first letter awarded engagement type, 5: singular uppercase first letter engagement template type */
				__( 'This action will replace the current title, content, background etc. of %1$d %2$s with the ones from this %3$s.\nAre you sure you want to proceed?', 'lifterlms' ),
				$awarded_number,
				$this->get_engagement_type_name( $plural_or_singular, LLMS_Case::LOWER, self::AWARDED ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::TEMPLATE ),
				$this->get_engagement_type_name( $plural_or_singular, LLMS_Case::UPPER_FIRST, self::AWARDED ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::TEMPLATE )
			);
			$sync_description   = sprintf(
				/* translators: 1: number of awarded engagements, 2: plural or singular lowercase awarded engagement type, 3: singular lowercase engagement template type, 4: plural or singular uppercase first letter awarded engagement type, 5: singular uppercase first letter engagement template type */
				__( 'Sync %1$d %2$s with this %3$s.', 'lifterlms' ),
				$awarded_number,
				$this->get_engagement_type_name( $plural_or_singular, LLMS_Case::LOWER, self::AWARDED ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::TEMPLATE ),
				$this->get_engagement_type_name( $plural_or_singular, LLMS_Case::UPPER_FIRST, self::AWARDED ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::TEMPLATE )
			);
		} else {
			$awarded_model = $this->get_awarded_engagement( $this->post->ID );
			$template_id   = $awarded_model ? $awarded_model->get( 'parent' ) : false;

			if ( empty( $template_id ) || ! $this->get_engagement_template( $template_id ) ) {
				return '';
			}

			$sync_alert       = sprintf(
				/* translators: 1: singular lowercase awarded engagement type, 2: singular lowercase engagement template type, 3: singular uppercase first letter awarded engagement type, 4: singular uppercase first letter engagement template type */
				__( 'This action will replace the current title, content, background etc. of this %1$s with the ones from the %2$s.\nAre you sure you want to proceed?', 'lifterlms' ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::AWARDED ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::TEMPLATE ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::AWARDED ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::TEMPLATE )
			);
			$sync_description = sprintf(
				/* translators: 1: singular lowercase awarded engagement type, 2: link to edit the engagement template, 3: singular lowercase engagement template type, 4: closing anchor tag, 5: singular uppercase first letter awarded engagement type, 6: singular uppercase first letter engagement template type */
				__( 'Sync this %1$s with its %2$s%3$s%4$s.', 'lifterlms' ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::AWARDED ),
				'<a href="' . get_edit_post_link( $template_id ) . '" target="_blank">',
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::LOWER, self::TEMPLATE ),
				'</a>',
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::AWARDED ),
				$this->get_engagement_type_name( self::SINGULAR, LLMS_Case::UPPER_FIRST, self::TEMPLATE )
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
