<?php
/**
 * LLMS_Processor_Certificate_Sync class
 *
 * @package LifterLMS/Processors/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Processor: Sync awarded certificates to their certificate template.
 *
 * @since [version]
 */
class LLMS_Processor_Certificate_Sync extends LLMS_Abstract_Processor_User_Engagement_Sync {

	/**
	 * The type of the user engagement.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type = 'certificate';

	/**
	 * Unique identifier for the processor.
	 *
	 * @var string
	 */
	protected $id = 'awarded_certificates_bulk_sync';

	/**
	 * WP Cron Hook for scheduling the background process.
	 *
	 * @var string
	 */
	protected $schedule_hook = 'llms_awarded_certificates_bulk_sync';

	/**
	 * Returns a translated text of the given type.
	 *
	 * @since [version]
	 *
	 * @param int   $text_type One of the LLMS_Abstract_Processor_User_Engagement_Sync::TEXT_ constants.
	 * @param array $variables Optional variables that are used in sprintf().
	 * @return string
	 */
	protected function get_text( $text_type, $variables = array() ) {

		switch ( $text_type ) {
			case self::TEXT_SYNC_NOTICE_AWARDED_ENGAGEMENTS_COMPLETE:
				$template_id = $variables['template_id'] ?? '';
				return sprintf(
					/* translators: 1: opening anchor tag that links to the certificate template, 2: certificate template name, 3: certificate template ID, 4: closing anchor tag */
					__( 'Awarded Certificates sync completed for the template %1$s%2$s (#%3$d)%4$s.', 'lifterlms' ),
					sprintf( '<a href="%1$s" target="_blank">', get_edit_post_link( $template_id ) ),
					get_the_title( $template_id ),
					$template_id,
					'</a>'
				);
			default:
				return parent::get_text( $text_type );
		}
	}
}

return new LLMS_Processor_Certificate_Sync();
