<?php
/**
 * Single Quiz View
 * @since    3.16.0
 * @version  3.16.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }
?>
<section class="llms-reporting-tab llms-reporting-quiz">

	<header class="llms-reporting-breadcrumbs">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=quizzes' ) ); ?>"><?php _e( 'Quizzes', 'lifterlms' ); ?></a>
		<?php do_action( 'llms_reporting_quiz_tab_breadcrumbs' ); ?>
	</header>

	<header class="llms-reporting-header">

		<div class="llms-reporting-header-info">
			<h2><a href="<?php echo get_edit_post_link( $quiz->get( 'id' ) ); ?>"><?php echo $quiz->get( 'title' ); ?></a></h2>
		</div>

	</header>

	<nav class="llms-nav-tab-wrapper llms-nav-secondary">
		<ul class="llms-nav-items">
		<?php foreach ( $tabs as $name => $label ) : ?>
			<li class="llms-nav-item<?php echo ( $current_tab === $name ) ? ' llms-active' : ''; ?>">
				<a class="llms-nav-link" href="<?php echo LLMS_Admin_Reporting::get_stab_url( $name ) ?>">
					<?php echo $label; ?>
				</a>
		<?php endforeach; ?>
		</ul>
	</nav>

	<section class="llms-gb-tab">
		<?php llms_get_template( 'admin/reporting/tabs/quizzes/' . $current_tab . '.php', array(
			'quiz' => $quiz,
		) ); ?>
	</section>

</section>
