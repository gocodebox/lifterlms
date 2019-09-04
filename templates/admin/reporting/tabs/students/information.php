<?php
/**
 * Single Student View: Information Tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.2.0
 * @version 3.15.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
?>

<?php do_action( 'llms_reporting_student_tab_info_stab_before_content' ); ?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>
			<h3><?php _e( 'Student Information', 'lifterlms' ); ?></h3>

		</header>
		<?php

		do_action( 'llms_reporting_single_student_overview_before_widgets', $student );

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of3',
				'icon'      => 'calendar',
				'id'        => 'llms-reporting-student-registered',
				'data'      => $student->get_registration_date(),
				'data_type' => 'date',
				'text'      => __( 'Registered', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of3',
				'icon'      => 'line-chart',
				'id'        => 'llms-reporting-student-registered',
				'data'      => $student->get_overall_progress(),
				'data_type' => 'percentage',
				'text'      => __( 'Overall Progress', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of3',
				'icon'      => 'graduation-cap',
				'id'        => 'llms-reporting-student-registered',
				'data'      => $student->get_overall_grade(),
				'data_type' => 'percentage',
				'text'      => __( 'Overall Grade', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols' => 'd-1of2',
				'icon' => 'trophy',
				'id'   => 'llms-reporting-student-achievements',
				'data' => count( $student->get_achievements( 'updated_date', 'DESC', 'achievements' ) ),
				'text' => __( 'Achievements earned', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols' => 'd-1of2',
				'icon' => 'certificate',
				'id'   => 'llms-reporting-student-certificates',
				'data' => count( $student->get_certificates( 'updated_date', 'DESC', 'certificates' ) ),
				'text' => __( 'Certificates earned', 'lifterlms' ),
			)
		);

		$address = $student->get( 'billing_address_1' );
		if ( $student->get( 'billing_address_2' ) ) {
			$address .= ' ' . $student->get( 'billing_address_2' );
		}
		$address .= '<br>' . $student->get( 'billing_city' ) . ', ' . $student->get( 'billing_state' ) . ' ' . $student->get( 'billing_zip' );
		$address .= ' ' . $student->get( 'billing_country' );

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of2',
				'icon'      => 'map-marker',
				'id'        => 'llms-reporting-student-address',
				'data'      => trim( $address ),
				'data_type' => 'text',
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of2',
				'icon'      => 'phone',
				'id'        => 'llms-reporting-student-address',
				'data'      => $student->get( 'phone' ),
				'data_type' => 'text',
			)
		);

		do_action( 'llms_reporting_single_student_overview_after_widgets', $student );
		?>

	</section>

	<aside class="llms-reporting-tab-side">

		<h3><i class="fa fa-bolt" aria-hidden="true"></i> <?php _e( 'Recent events', 'lifterlms' ); ?></h3>

		<?php foreach ( $student->get_events() as $event ) : ?>
			<?php LLMS_Admin_Reporting::output_event( $event, 'student' ); ?>
		<?php endforeach; ?>

	</aside>

</div>

<?php do_action( 'llms_reporting_student_tab_info_stab_after_content' ); ?>
