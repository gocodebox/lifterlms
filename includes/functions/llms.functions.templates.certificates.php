<?php
/**
 * Certificates & Related template functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.14.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads the certificate content template.
 *
 * @since 6.0.0
 *
 * @param LLMS_User_Certificate $certificate Certificate object.
 * @return void
 */
function llms_certificate_content( $certificate ) {
	$template = 1 === $certificate->get_template_version() ? 'content-legacy' : 'content';
	llms_get_template(
		"certificates/{$template}.php",
		compact( 'certificate' )
	);
}

/**
 * Outputs dynamic CSS for a single certificate template.
 *
 * Hooked to action `wp_head` at priority 10.
 *
 * @since 6.0.0
 *
 * @return void
 */
function llms_certificate_styles() {

	$certificate = llms_get_certificate( get_the_ID(), true );
	if ( ! $certificate || 1 === $certificate->get_template_version() ) {
		return;
	}

	$image          = $certificate->get_background_image();
	$background_img = $image['src'];

	$background_color = $certificate->get( 'background' );

	$padding = implode( ' ', $certificate->get_margins( true ) );

	$dimensions = $certificate->get_dimensions_for_display();
	$width      = $dimensions['width'];
	$height     = $dimensions['height'];

	$fonts = $certificate->get_custom_fonts();

	llms_get_template(
		'certificates/dynamic-styles.php',
		compact( 'certificate', 'width', 'height', 'background_color', 'background_img', 'padding', 'fonts' )
	);
}

/**
 * Loads the certificate actions template.
 *
 * @since 6.0.0
 *
 * @param LLMS_User_Certificate $certificate Certificate object.
 * @return void
 */
function llms_certificate_actions( $certificate ) {

	if ( ! $certificate->can_user_manage() ) {
		return;
	}

	$dashboard_url   = get_permalink( llms_get_page_id( 'myaccount' ) );
	$cert_ep_enabled = LLMS_Student_Dashboard::is_endpoint_enabled( 'view-certificates' );

	$back_link = $cert_ep_enabled ? llms_get_endpoint_url( 'view-certificates', '', $dashboard_url ) : $dashboard_url;
	$back_text = $cert_ep_enabled ? __( 'All certificates', 'lifterlms' ) : __( 'Dashboard', 'lifterlms' );

	$is_template        = 'llms_certificate' === $certificate->get( 'type' );
	$is_sharing_enabled = $certificate->is_sharing_enabled();
	llms_get_template(
		'certificates/actions.php',
		compact( 'certificate', 'back_link', 'back_text', 'is_sharing_enabled', 'is_template' )
	);
}

/**
 * Get the content of a single certificates
 *
 * @since 3.14.0
 *
 * @param LLMS_User_Certificate $certificate Instance of an LLMS_User_Certificate.
 * @return void
 */
function llms_get_certificate_preview( $certificate ) {

	ob_start();

	llms_get_template(
		'certificates/preview.php',
		array(
			'certificate' => $certificate,
		)
	);

	return ob_get_clean();
}
/**
 * Output the content of a single certificate
 *
 * @since 3.14.0
 *
 * @param LLMS_User_Certificate $certificate Instance of an LLMS_User_Certificate.
 * @return void
 */
function llms_the_certificate_preview( $certificate ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in template file.
	echo llms_get_certificate_preview( $certificate );
}

/**
 * Retrieve the number of columns used in certificates loops
 *
 * @since 3.14.0
 * @since 6.0.0 Reduced default columns from 5 to 3.
 *
 * @return int
 */
function llms_get_certificates_loop_columns() {
	/**
	 * Filters the number of columns used to display a list of certificate previews.
	 *
	 * @since 3.14.0
	 *
	 * @param integer $cols Number of columns.
	 */
	return apply_filters( 'llms_certificates_loop_columns', 3 );
}


/**
 * Get template for certificates loop
 *
 * @since 3.14.0
 * @since 6.0.0 Updated to use the new signature of the {@see LLMS_Student::get_certificates()}.
 *              Add pagination.
 *
 * @param LLMS_Student $student Optional. LLMS_Student (uses current if none supplied). Default is `null`.
 *                              The current student will be used if none supplied.
 * @param bool|int     $limit   Optional. Number of certificates to show (defaults to all). Default is `false`.
 * @return void
 */
if ( ! function_exists( 'lifterlms_template_certificates_loop' ) ) {
	function lifterlms_template_certificates_loop( $student = null, $limit = false ) {

		// Get the current student if none supplied.
		if ( ! $student ) {
			$student = llms_get_student();
		}

		// Don't proceed without a student.
		if ( ! $student ) {
			return;
		}

		$cols     = llms_get_certificates_loop_columns();
		$per_page = $cols * 5;

		// Get certificates.
		$query        = $student->get_certificates(
			array(
				'page'     => max( 1, get_query_var( 'paged' ) ),
				'per_page' => $limit ? min( $limit, $per_page ) : $per_page,
			)
		);
		$certificates = $query->get_awards();

		/**
		 * If no columns are specified and we have a specified limit
		 * and results and the limit is less than the number of columns
		 * force the columns to equal the limit.
		 */
		if ( $limit && $limit < $cols && $query->get_number_results() ) {
			$cols = $limit;
		}

		$pagination = 'dashboard' === LLMS_Student_Dashboard::get_current_tab( 'slug' ) ? false : array(
			'total'   => $query->get_max_pages(),
			'context' => 'student_dashboard',
		);

		llms_get_template(
			'certificates/loop.php',
			compact( 'cols', 'certificates', 'pagination' )
		);
	}
}

/**
 * Automatically remove all non-safelisted print stylesheets from certificate and certificate templates.
 *
 * @since 6.0.0
 *
 * @return boolean Returns `false` when run on non-certificate post types, otherwise returns `true`.
 */
function llms_certificates_remove_print_styles() {

	if ( ! in_array( get_post_type(), array( 'llms_certificate', 'llms_my_certificate' ), true ) ) {
		return false;
	}

	/**
	 * A list of registered print stylesheet handles which should be allowed for certificate and certificate templates.
	 *
	 * By default, any enqueued print stylesheets are automatically dequeued to prevent visual issues encountered when
	 * printing certificates.
	 *
	 * Any stylesheets added to this safelist will not be removed from certificates.
	 *
	 * @since 6.0.0
	 *
	 * @param string[] $safelist Array of print stylesheet handles.
	 */
	$safelist = apply_filters( 'llms_certificate_print_styles_safelist', array() );

	$styles = wp_styles();
	foreach ( $styles->queue as $handle ) {
		$style = $styles->registered[ $handle ] ?? false;
		if ( ! empty( $style->args ) && 'print' === $style->args && ! in_array( $handle, $safelist, true ) ) {
			wp_dequeue_style( $handle );
		}
	}

	return true;
}
add_action( 'wp_enqueue_scripts', 'llms_certificates_remove_print_styles', 999 );
