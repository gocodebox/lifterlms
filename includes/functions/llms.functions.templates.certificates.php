<?php
/**
 * Certificates & Related template functions
 *
 * @package LifterLMS/Functions
 *
 * @since unknown
 * @version unknown
 */

defined( 'ABSPATH' ) || exit;

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
	echo llms_get_certificate_preview( $certificate );
}

/**
 * Retrieve the number of columns used in certificates loops
 *
 * @since 3.14.0
 *
 * @return int
 */
function llms_get_certificates_loop_columns() {
	return apply_filters( 'llms_certificates_loop_columns', 5 );
}


/**
 * Get template for certificates loop
 *
 * @since 3.14.0
 *
 * @param LLMS_Student $student Optional. LLMS_Student (uses current if none supplied). Default is `null`.
 *                              The current student will be used if none supplied.
 * @param bool|int     $limit   Optional. Number of achievements to show (defaults to all). Default is `false`.
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

		$cols = llms_get_certificates_loop_columns();

		// Get certificates.
		$certificates = $student->get_certificates( 'updated_date', 'DESC', 'certificates' );
		if ( $limit && $certificates ) {
			$certificates = array_slice( $certificates, 0, $limit );
			if ( $limit < $cols ) {
				$cols = $limit;
			}
		}

		llms_get_template(
			'certificates/loop.php',
			array(
				'cols'         => $cols,
				'certificates' => $certificates,
			)
		);

	}
}
