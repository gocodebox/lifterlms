<?php
/**
 * Single Student View
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }
?>

<section class="llms-gb-student">

	<header class="llms-gb-student-header">

		<?php echo $student->get_avatar( 64 ); ?>
		<div class="llms-gb-student-info">
			<h2><a href="<?php echo get_edit_user_link( $student->get_id() ); ?>"><?php echo $student->get_name(); ?></a></h2>
			<h5><a href="mailto:<?php echo $student->get( 'user_email' ); ?>"><?php echo $student->get( 'user_email' ); ?></a></h5>
		</div>

	</header>

	<nav class="llms-nav-tab-wrapper llms-nav-secondary" id="llms-gb-student-tabs">
		<ul class="llms-nav-items">
		<?php foreach ( $tabs as $name => $label ) : ?>
			<li class="llms-nav-item"><a class="llms-nav-link" href="#llms-gb-tab-<?php echo $name; ?>"><?php echo $label; ?></a>
		<?php endforeach; ?>
		</ul>
	</nav>

	<section class="llms-gb-tabs">
	<?php foreach ( $tabs as $name => $label ) : ?>
		<div class="llms-gb-tab" id="llms-gb-tab-<?php echo $name; ?>">
			<?php llms_get_template( 'admin/grade-book/student/' . $name . '.php', array( 'student' => $student ) ); ?>
		</div>
	<?php endforeach; ?>
	</section>

	<?php if ( ! empty( $_SERVER['HTTP_REFERER'] ) && false !== strpos( $_SERVER['HTTP_REFERER'], 'admin.php?page=llms-grade-book' ) ) : ?>
		<p><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><?php _e( 'Back to Results', 'lifterlms' ); ?></a></p>
	<?php endif; ?>

</section>
