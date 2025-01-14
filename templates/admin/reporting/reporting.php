<?php
/**
 * Reporting Screen Main Template
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since Unknown
 * @version 3.29.0
 */

defined( 'ABSPATH' ) || exit;
is_admin() || exit;
?>
<div class="wrap lifterlms llms-reporting tab--<?php echo esc_attr( $current_tab ); ?>">
	<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="llms-reporting-nav" method="GET">

		<nav class="llms-nav-tab-wrapper llms-nav-secondary">

			<div class="llms-inside-wrap">
				<ul class="llms-nav-items">
				<?php foreach ( $tabs as $name => $label ) : ?>

					<?php $current_tab_class = ( $current_tab === $name ) ? ' llms-active' : ''; ?>
					<li class="llms-nav-item<?php echo esc_attr( $current_tab_class ); ?>"><a class="llms-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=' . $name ) ); ?>"><?php echo esc_html( $label ); ?></a>

				<?php endforeach; ?>
				</ul>
			</div>

		</nav>

		<?php do_action( 'llms_reporting_after_nav', $current_tab ); ?>

	</form>

	<h1 style="display:none;"></h1><!-- find a home for admin notices -->

	<div class="llms-options-page-contents">

		<?php do_action( 'llms_reporting_before_content', $current_tab ); ?>

		<?php do_action( 'llms_reporting_content_' . $current_tab ); ?>

		<?php do_action( 'llms_reporting_after_content', $current_tab ); ?>


	</div>

</div>
