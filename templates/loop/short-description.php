<?php
if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
global $course;
?>

<?php if ( $short_description = $course->get_short_description() ) : ?>
	<div class="llms-short-description"><?php echo $short_description; ?></div>
<?php endif; ?>
