<?php
/**
 * Single Access Plan Description
 * @property  obj  $plan  Instance of the LLMS_Access_Plan
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.23.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="llms-access-plan-description"><?php echo $plan->get( 'content' ); ?></div>
