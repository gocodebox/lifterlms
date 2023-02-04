<?php
/**
 * Dashboard Page HTML
 *
 * @since    TBD
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap lifterlms lifterlms-settings">

	<form action="" method="POST" id="mainform" enctype="multipart/form-data">
		<div class="llms-inside-wrap">
			<h2><?php esc_html_e( 'Activity This Week', 'lifterlms' ); ?></h2>
			<?php echo '<style type="text/css">#llms-charts-wrapper{display:none;}</style>'; ?>

			<?php
				echo llms_get_template(
					'admin/reporting/tabs/widgets.php',
					array(
						'json'        => json_encode(
							array(
								'current_tab'         => 'settings',
								'current_range'       => 'last-7-days',
								'current_students'    => array(),
								'current_courses'     => array(),
								'current_memberships' => array(),
								'dates'               => array(
									'start' => date( 'Y-m-d', current_time( 'timestamp' ) - WEEK_IN_SECONDS ),
									'end'   => current_time( 'Y-m-d' ),
								),
							)
						),
						'widget_data' => array(
							array(
								'enrollments'       => array(
									'title'   => __( 'Enrollments', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Number of total enrollments during the selected period', 'lifterlms' ),
								),
								'registrations'     => array(
									'title'   => __( 'Registrations', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Number of total user registrations during the selected period', 'lifterlms' ),
								),
								'sold'              => array(
									'title'   => __( 'Net Sales', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Total of all successful transactions during this period', 'lifterlms' ),
								),
								'lessoncompletions' => array(
									'title'   => __( 'Lessons Completed', 'lifterlms' ),
									'cols'    => '1-4',
									'content' => __( 'loading...', 'lifterlms' ),
									'info'    => __( 'Number of total lessons completed during the selected period', 'lifterlms' ),
								),
							),
						),
					)
				);
			?>

			<?php
				/**
				 * Get advert banner HTML.
				 */
				$view = new LLMS_Admin_AddOns();
				$url  = esc_url( admin_url( 'admin.php?page=llms-add-ons' ) );
			?>
			<h2 style="display:inline;"><?php esc_html_e( 'Most Popular Add-ons, Courses, and Resources', 'lifterlms' ); ?></h2>
			<?php
				echo '&nbsp;&nbsp;&nbsp;<a class="llms-button-primary small" href="' . $url . '">' . __( 'View More &rarr;', 'lifterlms' ) . '</a><br>'; 
				echo $view->output_for_settings();
			?>

			<h2><?php esc_html_e( 'Quick Links', 'lifterlms' ); ?></h2>
			<div class="llms-list">
				<ul>
					<li><p><?php echo sprintf( __( 'Version: %s', 'lifterlms' ), llms()->version ); ?></p></li>
					<li><p><?php echo sprintf( __( 'Need help? Get support on the %1$sforums%2$s', 'lifterlms' ), '<a href="https://wordpress.org/support/plugin/lifterlms" target="_blank">', '</a>' ); ?></p></li>
					<li><p><?php echo sprintf( __( 'Looking for a quickstart guide, shortcodes, or developer documentation? Get started at %s', 'lifterlms' ), '<a href="https://lifterlms.com/docs" target="_blank">https://lifterlms.com/docs</a>' ); ?></p></li>
					<li><p><?php echo sprintf( __( 'Get LifterLMS news, updates, and more on our %1$sblog%2$s', 'lifterlms' ), '<a href="http://blog.lifterlms.com/" target="_blank">', '</a>' ); ?></p></li>
				</ul>
			</div>
		</div>
	</form>

</div>
