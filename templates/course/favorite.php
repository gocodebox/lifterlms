<?php
/**
 * @author      codeBOX
 * @package     lifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = new LLMS_Lesson( $post->ID );
$student = llms_get_student( get_current_user_id() );
?>

<div class="llms-favorite-wrapper">

    <?php do_action( 'llms_before_favorite_button', $lesson, $student ); ?>
    
    <?php if ( $student->is_favorite( $lesson->get( 'id' ), 'lesson' ) ) : ?>
        
        <i class="fa fa-heart llms-unfavorite"></i>

    <?php else : ?>

        <form action="" class="llms-favorite-lesson-form" method="POST" name="mark_favorite">

            <?php do_action( 'lifterlms_before_mark_favorite_lesson' ); ?>

            <input type="hidden" name="mark-favorite" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
            <!-- TODO: Dynamic [Lesson, Course, Instructor] value -->
            <input type="hidden" name="type" value="lesson" />
            <input type="hidden" name="action" value="mark_favorite" />
            <?php wp_nonce_field( 'mark_favorite' ); ?>

            <?php
            llms_form_field(
                array(
                    'columns'     => 1,
                    'classes'     => 'auto button llms-favorite',
                    'id'          => 'llms_mark_favorite',
                    'value'       => apply_filters( 'lifterlms_mark_lesson_favorite_button_text', __( '<i class="fa fa-heart-o"></i>', 'lifterlms' ), $lesson ),
                    'last_column' => true,
                    'name'        => 'mark_favorite',
                    'required'    => false,
                    'type'        => 'submit',
                )
            );
            ?>

            <?php do_action( 'lifterlms_after_mark_favorite_lesson' ); ?>

        </form>

    <?php endif; ?>

    <?php do_action( 'llms_after_favorite_button', $lesson, $student ); ?>

</div>
