<?php
/**
 * LLMS_Processor_Achievement_Sync class
 *
 * @package LifterLMS/Processors/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Processor: Sync awarded achievements to their achievement template.
 *
 * @since 6.0.0
 */
class LLMS_Processor_Achievement_Sync extends LLMS_Abstract_Processor_User_Engagement_Sync {

	/**
	 * The type of the user engagement.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	protected $engagement_type = 'achievement';

	/**
	 * Unique identifier for the processor.
	 *
	 * @var string
	 */
	protected $id = 'awarded_achievements_bulk_sync';

	/**
	 * WP Cron Hook for scheduling the background process.
	 *
	 * @var string
	 */
	protected $schedule_hook = 'llms_awarded_achievements_bulk_sync';

	/**
	 * Returns a translated text of the given type.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $text_type One of the LLMS_Abstract_Processor_User_Engagement_Sync::TEXT_ constants.
	 * @param array $variables Optional variables that are used in sprintf().
	 * @return string
	 */
	protected function get_text( $text_type, $variables = array() ) {

		$engagement_template_id = $variables['engagement_template_id'] ?? 0;

		switch ( $text_type ) {
			case self::TEXT_SYNC_NOTICE_ALREADY_SCHEDULED:
				return sprintf(
					/* translators: %1$s: opening anchor tag that links to the achievement template, %2$s: achievement template name, #%3$d: achievement template ID, %4$s: closing anchor tag */
					__( 'Awarded achievements sync already scheduled for the template %1$s%2$s (#%3$d)%4$s.', 'lifterlms' ),
					'<a href="' . get_edit_post_link( $engagement_template_id ) . '" target="_blank">',
					get_the_title( $engagement_template_id ),
					$engagement_template_id,
					'</a>'
				);
			case self::TEXT_SYNC_NOTICE_AWARDED_ENGAGEMENTS_COMPLETE:
				return sprintf(
					/* translators: %1$s: opening anchor tag that links to the achievement template, %2$s: achievement template name, %3$d: achievement template ID, %4$s: closing anchor tag */
					__( 'Awarded achievements sync completed for the template %1$s%2$s (#%3$d)%4$s.', 'lifterlms' ),
					'<a href="' . $this->get_edit_post_link( $engagement_template_id ) . '" target="_blank">',
					get_the_title( $engagement_template_id ),
					$engagement_template_id,
					'</a>'
				);
			case self::TEXT_SYNC_NOTICE_NO_AWARDED_ENGAGEMENTS:
				return sprintf(
					/* translators: %1$s: opening anchor tag that links to the achievement template, %2$s: achievement template name, #%3$d: achievement template ID, %4$s: closing anchor tag */
					__( 'There are no awarded achievements to sync with the template %1$s%2$s (#%3$d)%4$s.', 'lifterlms' ),
					'<a href="' . get_edit_post_link( $engagement_template_id ) . '" target="_blank">',
					get_the_title( $engagement_template_id ),
					$engagement_template_id,
					'</a>'
				);
			case self::TEXT_SYNC_NOTICE_SCHEDULED:
				return sprintf(
					/* translators: %1$s: opening anchor tag that links to the achievement template, %2$s: achievement template name, #%3$d: achievement template ID, %4$s: closing anchor tag */
					__( 'Awarded achievements sync scheduled for the template %1$s%2$s (#%3$d)%4$s.', 'lifterlms' ),
					'<a href="' . get_edit_post_link( $engagement_template_id ) . '" target="_blank">',
					get_the_title( $engagement_template_id ),
					$engagement_template_id,
					'</a>'
				);
			default:
				return parent::get_text( $text_type );
		}
	}
}

return new LLMS_Processor_Achievement_Sync();
