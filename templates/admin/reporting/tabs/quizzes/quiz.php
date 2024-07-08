<?php
/**
 * Single Quiz View
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.16.0
 * @version 3.16.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
?>
<section class="llms-reporting-tab llms-reporting-quiz">

	<header class="llms-reporting-breadcrumbs">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=quizzes' ) ); ?>"><?php esc_html_e( 'Quizzes', 'lifterlms' ); ?></a>
		<?php do_action( 'llms_reporting_quiz_tab_breadcrumbs' ); ?>
	</header>

	<div class="llms-reporting-body">

		<header class="llms-reporting-header">

			<div class="llms-reporting-header-info">
				<h2><a href="<?php echo esc_url( get_edit_post_link( $quiz->get( 'id' ) ) ); ?>"><?php echo esc_html( $quiz->get( 'title' ) ); ?></a></h2>
			</div>

		</header>

		<nav class="llms-nav-tab-wrapper llms-nav-secondary">
			<ul class="llms-nav-items">
			<?php foreach ( $tabs as $name => $label ) : ?>
				<li class="llms-nav-item<?php echo ( $current_tab === $name ) ? ' llms-active' : ''; ?>">
					<a class="llms-nav-link" href="<?php echo esc_url( LLMS_Admin_Reporting::get_stab_url( $name ) ); ?>">
						<?php echo wp_kses_post( $label ); ?>
					</a></li>
			<?php endforeach; ?>
			</ul>
		</nav>

		<section class="llms-gb-tab">
			<?php
			llms_get_template(
				'admin/reporting/tabs/quizzes/' . $current_tab . '.php',
				array(
					'quiz' => $quiz,
				)
			);
			?>
		</section>

	</div>

</section>
