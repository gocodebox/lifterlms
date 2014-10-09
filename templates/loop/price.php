<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $course;
?>
<?php if ( ! llms_is_user_enrolled( get_current_user_id(), $course->id ) && $price_html = $course->get_price_html() ) : ?>
		<span class="llms-price"><?php echo $price_html; ?></span>
<?php endif; ?>