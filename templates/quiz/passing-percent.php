<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $quiz;

$passing_percent = $quiz->get_passing_percent();

if ($passing_percent) :
?>

<div class="clear"></div>
<div class="llms-template-wrapper">
	<p class="llms-content-block">
		<?php printf( __('Percent Required to Pass test: <span class="llms-content llms-attempts">%s%%</span>', 'lifterlms'), $passing_percent ); ?>
	</p>
</div>

<?php endif; ?>



