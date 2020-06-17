<?php
/**
 * Admin Welcome Screen HTML
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setup Wizard Steps & Questions:
 *
 * + Where is your business located? (country)
 * ++ stored as option: lifterlms_country and used to determine the default lifterlms_currency option
 * ++ can be used to determine if their country is not supported by stripe/paypal and recommend WC as add-on/upsell
 * ++ Checkbox - I'm setting this site up for a client
 *
 * + Industry / type of content(?)?
 *
 * + What type of content will you offer?
 * ++ Courses
 * ++ Memberships
 * ++ Quizzes / tests (advanced quizzes upsell)
 * ++ Assignments (assignments upsell)
 * ++ Video content (advanced videos upsell)
 * -- upsells:
 * ++ Groups
 * ++ Private coaching / training
 * ++ Social learning or BuddyPress
 * ++ Forums (bbPress)
 *
 * + How many courses or memberships will you offer?
 *
 * + How will you distribute your content?
 * ++ Digital sales
 * ++ Offline sales
 * ++ Free (map open registration on)
 * ++ Private / internal only
 *
 * + What CRM are you using?
 * -- upsells:
 * ++ CK
 * ++ MC
 * ++ GroundHogg (free)
 * ++ Check with wp fusion for everything else
 *
 * + Currently using another platform?
 *
 * + Choose a theme
 */

?>

<div class="llms-setting-group top">
	<p class="llms-label"><?php _e( 'Set up your site', 'lifterlms' ); ?></p>
	<p class="llms-description"><?php _e( 'Here is a list of the most important steps to get your site ready for your learners.', 'lifterlms' ); ?></p>

	<a href="#">
		<h5><?php _e( 'Create your first course', 'lifterlms' ); ?></h5>
	</a>

	<a href="#">
		<h5><?php _e( 'Personalize your site', 'lifterlms' ); ?></h5>
	</a>

	<a href="#">
		<h5><?php _e( 'Personalize your site', 'lifterlms' ); ?></h5>
	</a>

</div>
