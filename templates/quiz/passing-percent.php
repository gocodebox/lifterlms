<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $quiz;

$passing_percent = $quiz->get_passing_percent();

if ($passing_percent) :
?>

<div class="clear"></div>
<div class="llms-template-wrapper">
	<h3 class="llms-content-block">
		<?php printf( __( 'A score of  <span class="llms-content llms-pass-perc">%s%%</span> or more is required to pass this test.', 'lifterlms' ), $passing_percent ); ?>
	</h3>
</div>

<?php endif; ?>
