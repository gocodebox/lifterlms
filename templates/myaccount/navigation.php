<?php
/**
 * My Account Navigation Links
 * @since  2.?.?
 * @version 3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$sep = apply_filters( 'lifterlms_my_account_navigation_link_separator', '&bull;' );
?>

			<script>
				jQuery(function ($) {
					$("#llms-sd-items > li").addClass(function(i){return "llms-sd-item" + (i + 1);});
				});
			</script>

<nav class="llms-sd-nav">

	<?php do_action( 'lifterlms_before_my_account_navigation' ); ?>
	<ul id="llms-sd-items" class="llms-sd-items">
		<?php foreach ( LLMS_Student_Dashboard::get_tabs() as $var => $data ) : ?>
			<li class="llms-sd-item"><a class="llms-sd-link" href="<?php echo isset( $data['url'] ) ? $data['url'] : llms_get_endpoint_url( $var, '', llms_get_page_url( 'myaccount' ) ); ?>"><?php echo $data['title']; ?></a><span class="llms-sep"><?php echo $sep; ?></span></li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'lifterlms_after_my_account_navigation' ); ?>

</nav>
