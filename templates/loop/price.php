<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $course;
?>

<?php if ( $price_html = $course->get_price_html() ) : ?>
	<span class="llms-price"><?php echo $price_html; ?></span>
<?php endif; ?>