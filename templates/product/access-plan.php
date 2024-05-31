<?php
/**
 * Single Access Plan Template
 *
 * @property  obj  $plan  Instance of the LLMS_Access_Plan
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.23.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="<?php echo llms_get_access_plan_classes( $plan ); ?>" id="llms-access-plan-<?php echo $plan->get( 'id' ); ?>">

	<?php
		/**
		 * llms_before_access_plan
		 *
		 * @hooked llms_template_access_plan_feature - 10
		 */
		do_action( 'llms_before_access_plan', $plan );
	?>

	<div class="llms-access-plan-content">
		<?php
			/**
			 * llms_acces_plan_content
			 *
			 * @hooked llms_template_access_plan_title - 10
			 * @hooked llms_template_access_plan_pricing - 20
			 * @hooked llms_template_access_plan_restrictions - 30
			 * @hooked llms_template_access_plan_description - 40
			 */
			do_action( 'llms_acces_plan_content', $plan );
		?>
	</div>

	<div class="llms-access-plan-footer">

		<?php
			/**
			 * llms_acces_plan_footer
			 *
			 * @hooked llms_template_access_plan_trial - 10
			 * @hooked llms_template_access_plan_button - 20
			 */
			do_action( 'llms_acces_plan_footer', $plan );
		?>

	</div>


	<?php
		/**
		 * llms_after_access_plan
		 */
		do_action( 'llms_after_access_plan', $plan );
	?>

</div>
