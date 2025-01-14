<?php
/**
 * Course & Membership Instructors Block
 *
 * @package LifterLMS/Templates/Shared
 *
 * @since 4.11.0
 * @version 4.11.0
 *
 * @param LLMS_Post_Model $llms_post   Instance of the LLMS_Post_Model for the current screen.
 * @param array[]         $instructors Array of instructor data from the post's `get_instructors()` method.
 * @param int             $count       Number of instructors found in the `$instructors` array.
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="llms-instructor-info">
	<h3 class="llms-meta-title">
		<?php
		/**
		 * Filters the displayed title of the Instructors block
		 *
		 * @since 4.11.0
		 *
		 * @param string          $title     The block's title.
		 * @param LLMS_Post_Model $llms_post The post model object.
		 * @param int             $count     Number of instructors found, used to pluralize the title.
		 */
		echo esc_html( apply_filters(
			'llms_instructors_info_title',
			// Translators: %s = The singular name of the post type, eg: "Course".
			sprintf( _n( '%s Instructor', '%s Instructors', $count, 'lifterlms' ), $llms_post->get_post_type_label() ),
			$llms_post,
			$count
		) );
		?>
	</h3>
	<div class="llms-instructors llms-cols">
		<?php foreach ( $instructors as $instructor ) : ?>
			<div class="llms-col-<?php echo esc_attr( $count <= 4 ? $count : 4 ); ?>">
				<?php
				// HTML output is escaped in the `llms_get_author()` function.
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo llms_get_author(
					array(
						'avatar_size' => 100,
						'bio'         => true,
						'label'       => $instructor['label'],
						'user_id'     => $instructor['id'],
					)
				);
				?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
