<?php
/**
 * Certificates & Related template functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Get the content of a single certificates
 *
 * @param    obj $certificate  instance of an LLMS_User_Achievement
 * @return   void
 * @since    3.14.0
 * @version  3.14.0
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
	 * @param    obj $certificate  instance of an LLMS_User_Achievement
	 * @return   void
	 * @since    3.14.0
	 * @version  3.14.0
	 */
function llms_the_certificate_preview( $certificate ) {
	echo llms_get_certificate_preview( $certificate );
}

/**
 * Retrieve the number of columns used in certificates loops
 *
 * @return   int
 * @since    3.14.0
 * @version  3.14.0
 */
function llms_get_certificates_loop_columns() {
	return apply_filters( 'llms_certificates_loop_columns', 5 );
}


/**
 * Get template for certificates loop
 *
 * @param    obj       $student  LLMS_Student (uses current if none supplied)
 * @param    bool|int  $limit    number of certificates to show (defaults to all)
 * @return   void
 * @since    3.14.0
 * @version  3.14.0
 */
if ( ! function_exists( 'lifterlms_template_certificates_loop' ) ) {
	function lifterlms_template_certificates_loop( $student = null, $limit = false ) {

		// get the current student if none supplied
		if ( ! $student ) {
			$student = llms_get_student();
		}

		// don't proceed without a student
		if ( ! $student ) {
			return;
		}

		$cols = llms_get_certificates_loop_columns();

		// get certificates
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
