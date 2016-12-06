<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $quiz;

$passing_percent = $quiz->get_passing_percent();
if ( $passing_percent ) :
?>

<div class="clear"></div>
<div class="llms-template-wrapper">
	<h3 class="llms-content-block">
		<?php if ( 100 == $passing_percent ) : ?>
			<?php printf( __( 'A score of %s is required to pass this test.', 'lifterlms' ), '<span class="llms-content llms-pass-perc">' . $passing_percent . '%</span>' ); ?>
		<?php else : ?>
			<?php printf( __( 'A score of %s or more is required to pass this test.', 'lifterlms' ), '<span class="llms-content llms-pass-perc">' . $passing_percent . '%</span>' ); ?>
		<?php endif; ?>
	</h3>
</div>

<?php endif; ?>
