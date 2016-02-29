<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Meta Box Builder
 *
 * Generates review metabox and builds forms
 */
class LLMS_Meta_Box_Review extends LLMS_Admin_Metabox {

    public static $prefix = '_';

    /**
     * Function to field WP::output() method call
     * Passes output instruction to parent
     *
     * @param object $post WP global post object
     * @return void
     */
    public static function output ( $post ) {
        global $post;
        parent::new_output( $post, self::metabox_options() );
    }

    /**
     * Builds array of metabox options.
     * Array is called in output method to display options.
     * Appropriate fields are generated based on type.
     *
     * @return array [md array of metabox fields]
     */
    public static function metabox_options() {
        global $post;

        $courses = LLMS_Analytics::get_posts('course');
        $coursesSelect = array();
        if (!empty($courses)) {
            foreach ($courses as $course) {
                $coursesSelect[] = array(
                    'key' => $course->ID,
                    'title' => $course->post_title
                );
            }
        }

        $meta_fields_review = array(
            array(
                'title' 	=> 'General',
                'fields' 	=> array(
                    array(
                        'type' => 'select',
                        'label' => 'Course',
                        'id' => self::$prefix . 'llms_review_course',
                        'class' => 'input-full llms-meta-select',
                        'value' => $coursesSelect,
                        'selected' => $post->post_parent,
                        'required' => true
                    ),
                )
            ),
        );

        if(has_filter('llms_meta_fields_review')) {
            $meta_fields_review = apply_filters('llms_meta_fields_review', $meta_fields_review);
        }

        return $meta_fields_review;
    }

    /**
     * Static save method
     *
     * cleans variables and saves using update_post_meta
     *
     * @param  int 		$post_id [id of post object]
     * @param  object 	$post [WP post object]
     *
     * @return void
     */
    public static function save( $post_id, $post ) {
        $course = isset( $_POST['_llms_review_course'] ) ? $_POST['_llms_review_course'] : false;

        if($course) {
            wp_update_post(
                array(
                    'ID' => $post_id,
                    'post_parent' => $course
                )
            );
        }
    }

}
