<?php
/**
 * LifterLMS Course Meta Information Wrapper Start.
 *
 * @package  LifterLMS/Templates
 * @since    3.0.0
 * @version  3.25.1
 */

defined( 'ABSPATH' ) || exit;

$title_tag = apply_filters( 'llms_course_meta_info_title_size', 'h3' );
if ( ! in_array( $title_tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) ) ) {
	$title_tag = 'h3';
}
?>

<div class="llms-meta-info">
	<<?php echo $title_tag; ?> class="llms-meta-title">
		<?php echo apply_filters( 'llms_course_meta_info_title', _x( 'Course Information', 'course meta info title', 'lifterlms' ) ); ?>
	</<?php echo $title_tag; ?>>
