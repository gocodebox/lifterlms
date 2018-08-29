<?php
/**
 * Single Access Plan Title
 * @property  obj  $plan  Instance of the LLMS_Access_Plan
 * @author    LifterLMS
 * @package   LifterLMS/Templates
 * @since     3.23.0
 * @version   3.23.0
 */
defined( 'ABSPATH' ) || exit;
?>
<h4 class="llms-access-plan-title"><?php echo $plan->get( 'title' ); ?></h4>
