<?php
/**
* Admin Post Types Class
*
* Sets up post type custom messages and includes base metabox class
*
* @author codeBOX
* @project LifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Types {

	/**
	* Constructor
	*
	* Adds functions to actions and sets filter on post_updated_messages
	*/
	public function __construct() {

		add_action( 'admin_init', array( $this, 'include_post_type_metabox_class' ) );
		add_action( 'metabox_init', array( $this, 'meta_metabox_init' ) );

		add_filter( 'post_updated_messages', array( $this, 'llms_post_updated_messages' ) );

		add_filter( 'manage_lesson_posts_columns', array( $this, 'llms_add_lesson_columns' ), 10, 1 );
		add_action( 'manage_lesson_posts_custom_column', array( $this, 'llms_manage_lesson_columns' ), 10, 2 );

		add_filter( 'manage_section_posts_columns', array( $this, 'llms_add_section_columns' ), 10, 1 );
		add_action( 'manage_section_posts_custom_column', array( $this, 'llms_manage_section_columns' ), 10, 2 );

	}

	/**
	* Admin Menu
	*
	* Includes base metabox class
	*
	* @return void
	*/
	public function include_post_type_metabox_class() {
		include( 'post-types/class.llms.meta.boxes.php' );
	}

	/**
	 * Initializes core for metaboxes
	 *
	 * @return void
	 */
	public function meta_metabox_init() {
		include_once( 'llms.class.admin.metabox.php' );
		echo "<h1>Hello I'm here!</h1>";
	}

	/**
	* Customize post type messages.
	*
	* TODO: Tidy up post types array and make a db option. Allow users to customize them.
	*
	* @return array $messages
	*/
	public function llms_post_updated_messages( $messages ) {
		global $post;

		$llms_post_types = array(
			'course'			=> 'Course',
			'section' 			=> 'Section',
			'lesson' 			=> 'Lesson',
			'llms_order'		=> 'Order',
			'llms_email'		=> 'Email',
			'llms_email'		=> 'Email',
			'llms_certificate' 	=> 'Certificate',
			'llms_achievement' 	=> 'Achievement',
			'llms_engagement' 	=> 'Engagement',
			'llms_quiz' 		=> 'Quiz',
			'llms_question' 	=> 'Question',
			'llms_coupon'		=> 'Coupon',
		);

		foreach ( $llms_post_types as $type => $title ) {

			$messages[ $type ] = array(
				0 => '',
				1 => sprintf( __( $title . ' updated. <a href="%s">View ' . $title . '</a>', 'lifterlms' ), esc_url( get_permalink( $post->ID ) ) ),
				2 => __( 'Custom field updated.', 'lifterlms' ),
				3 => __( 'Custom field deleted.', 'lifterlms' ),
				4 => __( $title . ' updated.', 'lifterlms' ),
				5 => isset( $_GET['revision'] ) ? sprintf( __( $title .' restored to revision from %s', 'lifterlms' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __( $title . ' published. <a href="%s">View ' . $title .'</a>', 'lifterlms' ), esc_url( get_permalink( $post->ID ) ) ),
				7 => __( $title . ' saved.', 'lifterlms' ),
				8 => sprintf( __( $title . ' submitted. <a target="_blank" href="%s">Preview ' . $title . '</a>', 'lifterlms' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
				9 => sprintf( __( $title . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . $title . '</a>', 'lifterlms' ),
				date_i18n( __( 'M j, Y @ G:i', 'lifterlms' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
				10 => sprintf( __( $title . ' draft updated. <a target="_blank" href="%s">Preview ' . $title . '</a>', 'lifterlms' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
			);

		}

		return $messages;
	}



	/**
	 * Lesson post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 */
	public function llms_add_lesson_columns( $columns ) {
	    $columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Lesson Title' ),
		'course' => __( 'Assigned Course' ),
		'section' => __( 'Assigned Section' ),
		'prereq' => __( 'Prerequisite' ),
		'date' => __( 'Date' ),
		);
		return $columns;
	}

	/**
	 * Lesson post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 */
	public function llms_manage_lesson_columns( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'course' :

				$course = get_post_meta( $post_id, '_parent_course', true );
				$edit_link = get_edit_post_link( $course );

				if ( empty( $course ) ) {
					echo ''; } else { 					printf( __( '<a href="%s">%s</a>' ), $edit_link , get_the_title( $course ) ); }

				break;

			case 'section' :

				$section = get_post_meta( $post_id, '_parent_section', true );
				$edit_link = get_edit_post_link( $section );

				if ( empty( $section ) ) {
					echo ''; } else { 					printf( __( '<a href="%s">%s</a>' ), $edit_link, get_the_title( $section ) ); }

				break;

			case 'prereq' :

				$prereq = get_post_meta( $post_id, '_prerequisite', true );
				$edit_link = get_edit_post_link( $prereq );

				if ( empty( $prereq ) ) {
					echo ''; } else { 					printf( __( '<a href="%s">%s</a>' ), $edit_link, get_the_title( $prereq ) ); }

				break;

			default :
				break;
		}
	}

	/**
	 * Section post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 */
	public function llms_add_section_columns( $columns ) {
	    $columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Lesson Title' ),
		'course' => __( 'Assigned Course' ),
		'date' => __( 'Date' ),
		);
		return $columns;
	}

	/**
	 * Section post: Queries data based on column name
	 *
	 * @param  string $column  [custom column name]
	 * @param  int $post_id [ID of the individual post]
	 *
	 * @return void
	 */
	public function llms_manage_section_columns( $column, $post_id ) {
		global $post;

		switch ( $column ) {

			case 'course' :

				$course = get_post_meta( $post_id, '_parent_course', true );
				$edit_link = get_edit_post_link( $course );
				if ( empty( $course ) ) {
					echo ''; } else { 					printf( __( '<a href="%s">%s</a>' ), $edit_link, get_the_title( $course ) ); }

				break;

			default :
				break;
		}
	}

}

return new LLMS_Admin_Post_Types();
