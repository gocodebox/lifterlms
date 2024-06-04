<?php
/**
 * LLMS_Abstract_Meta_Box_User_Engagement_Sync class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base meta box class for syncing between awarded engagements (certificates or achievements) and templates.
 *
 * @since 6.0.0
 */
abstract class LLMS_Abstract_Meta_Box_User_Engagement_Sync extends LLMS_Admin_Metabox {

	use LLMS_Trait_User_Engagement_Type;

	/**
	 * A text type for a sync alert about many awarded engagements being synced to the current engagement template.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_ALERT_MANY_AWARDED_ENGAGEMENTS = 0;

	/**
	 * A text type for a sync alert about one awarded engagement being synced to the current engagement template.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_ALERT_ONE_AWARDED_ENGAGEMENT = 1;

	/**
	 * A text type for a sync alert about this awarded engagement being synced to its engagement template.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_ALERT_THIS_AWARDED_ENGAGEMENT = 2;

	/**
	 * A text type for a sync description about many awarded engagements being synced to the current engagement template.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_DESCRIPTION_MANY_AWARDED_ENGAGEMENTS = 3;

	/**
	 * A text type for a sync description about one awarded engagement being synced to the current engagement template.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_DESCRIPTION_ONE_AWARDED_ENGAGEMENT = 4;

	/**
	 * A text type for a sync description about this awarded engagement being synced to its engagement template.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_DESCRIPTION_THIS_AWARDED_ENGAGEMENT = 5;

	/**
	 * A text type for the content of a "sync awarded engagements" meta box when there are no awarded engagements to sync with.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_ENGAGEMENT_TEMPLATE_NO_AWARDED_ENGAGEMENTS = 6;

	/**
	 * A text type for the title of a "sync awarded engagement" meta box.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_TITLE_AWARDED_ENGAGEMENT = 7;

	/**
	 * A text type for the title of a "sync awarded engagements" meta box.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected const TEXT_SYNC_TITLE_AWARDED_ENGAGEMENTS = 8;

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
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	protected $is_current_post_a_template;

	/**
	 * The post type of an awarded engagement, e.g. 'llms_my_achievement' or 'llms_my_certificate'.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	protected $post_type_awarded;

	/**
	 * The post type of an engagement template, e.g. 'llms_achievement' or 'llms_certificate'.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	protected $post_type_template;

	/**
	 * Configure the meta box settings.
	 *
	 * @since 6.0.0 Refactored from LLMS_Meta_Box_Award_Certificate_Sync::configure() and
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
			! in_array( $this->post->post_type, array( $this->post_type_awarded, $this->post_type_template ), true )
		) {
			return;
		}

		$this->id = "{$this->engagement_type}_sync";

		if ( $this->post->post_type === $this->post_type_template ) {
			$this->is_current_post_a_template = true;
			$this->title                      = $this->get_text( self::TEXT_SYNC_TITLE_AWARDED_ENGAGEMENTS );
		} else {
			$this->is_current_post_a_template = false;
			$this->title                      = $this->get_text( self::TEXT_SYNC_TITLE_AWARDED_ENGAGEMENT );
		}
	}

	/**
	 * Not used because our meta box doesn't use the standard fields API.
	 *
	 * @since 6.0.0 Refactored from LLMS_Meta_Box_Award_Certificate_Sync::get_fields() and
	 *              LLMS_Meta_Box_Certificate_Template_Sync::get_fields().
	 *
	 * @return array
	 */
	public function get_fields() {

		return array();
	}

	/**
	 * Returns the sync alert and sync description texts for the sync action button, or an empty array if the sync
	 * button should not be displayed.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	private function get_sync_action_texts() {

		if ( $this->is_current_post_a_template ) {
			$awarded_number = $this->count_awarded_engagements( $this->post->ID );

			if ( ! $awarded_number ) {
				return array();
			}

			$variables = compact( 'awarded_number' );
			if ( $awarded_number > 1 ) {
				$sync_alert       = $this->get_text( self::TEXT_SYNC_ALERT_MANY_AWARDED_ENGAGEMENTS, $variables );
				$sync_description = $this->get_text( self::TEXT_SYNC_DESCRIPTION_MANY_AWARDED_ENGAGEMENTS, $variables );
			} else {
				$sync_alert       = $this->get_text( self::TEXT_SYNC_ALERT_ONE_AWARDED_ENGAGEMENT, $variables );
				$sync_description = $this->get_text( self::TEXT_SYNC_DESCRIPTION_ONE_AWARDED_ENGAGEMENT, $variables );
			}
		} else {
			$awarded_model = $this->get_user_engagement( $this->post->ID, true );
			$template_id   = $awarded_model ? $awarded_model->get( 'parent' ) : false;

			if ( empty( $template_id ) || ! $this->get_user_engagement( $template_id, false ) ) {
				return array();
			}

			$sync_alert       = $this->get_text( self::TEXT_SYNC_ALERT_THIS_AWARDED_ENGAGEMENT );
			$sync_description = $this->get_text(
				self::TEXT_SYNC_DESCRIPTION_THIS_AWARDED_ENGAGEMENT,
				array( 'template_id' => $template_id )
			);
		}

		return compact( 'sync_alert', 'sync_description' );
	}

	/**
	 * Returns a translated text of the given type.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $text_type One of the LLMS_Abstract_Meta_Box_User_Engagement_Sync::TEXT_ constants.
	 * @param array $variables Optional variables that are used in sprintf().
	 * @return string
	 */
	protected function get_text( $text_type, $variables = array() ) {

		return __( 'Invalid text type.', 'lifterlms' );
	}

	/**
	 * Function to field WP::output() method call.
	 *
	 * @see LLMS_Admin_Metabox::register()
	 * @see do_meta_boxes()
	 *
	 * @since 6.0.0 Refactored from LLMS_Meta_Box_Award_Certificate_Sync::output() and
	 *              LLMS_Meta_Box_Certificate_Template_Sync::output().
	 *
	 * @return void
	 */
	public function output() {

		$sync_action = $this->sync_action();

		// Output the HTML.
		echo '<div class="llms-mb-container">';
		do_action( 'llms_metabox_before_content', $this->id );
		echo wp_kses_post( $sync_action );
		do_action( 'llms_metabox_after_content', $this->id );
		echo '</div>';
	}

	/**
	 * Returns the sync action description and button HTML for a meta box on an engagement template or an awarded engagement.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	private function sync_action() {

		$texts = $this->get_sync_action_texts();
		if ( empty( $texts ) ) {
			return $this->get_text( self::TEXT_SYNC_ENGAGEMENT_TEMPLATE_NO_AWARDED_ENGAGEMENTS );
		}

		$base_url = remove_query_arg( 'action' ); // Current URL without 'action' arg.
		$sync_url = add_query_arg(
			'action',
			"sync_awarded_{$this->engagement_type}" . ( $this->is_current_post_a_template ? 's' : '' ),
			wp_nonce_url(
				$base_url,
				"llms-{$this->engagement_type}-sync-actions",
				"_llms_{$this->engagement_type}_sync_actions_nonce"
			)
		);

		$sync_alert   = str_replace( "'", "\'", $texts['sync_alert'] );
		$on_click     = "return confirm('$sync_alert')";
		$button_label = __( 'Sync', 'lifterlms' );

		return <<<HEREDOC

<p>{$texts['sync_description']}</p>
<p style="text-align: right; margin: 1em 0;">
<a href="$sync_url" class="llms-button-primary sync-action full small" onclick="$on_click" style="box-sizing:border-box;">$button_label</a>
</p>

HEREDOC;
	}
}
