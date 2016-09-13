<?php
/**
 * My Account Navigation Links
 * @since  2.?.?
 * @version 3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$sep = apply_filters( 'lifterlms_my_account_navigation_link_separator', '&bull;' );
?>
<nav class="llms-sd-nav">

	<?php do_action( 'lifterlms_before_my_account_navigation' ); ?>

	<ul class="llms-sd-items">
		<?php foreach ( LLMS_Student_Dashboard::get_tabs() as $var => $title ) : ?>
			<li class="llms-sd-item"><a class="llms-sd-link" href="<?php echo $title['url']; ?>"><?php echo $title['title']; ?></a><span class="llms-sep"><?php echo $sep; ?></span></li>
		<?php endforeach; ?>
	</ul>

	<?php do_action( 'lifterlms_after_my_account_navigation' ); ?>

</nav>
