<?php
/**
 * This class handles the admin side of the reviews.
 * It is responsible for creating the meta box on the course
 * page (and in the future the membership page).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Admin_Reviews {

	public static $prefix = '_';

	/**
	 * The constructor for the class. It adds the methods here to the appropriate
	 * actions. The actions are for:
	 * 1) Creating the custom column set in the llms_review post screen
	 * 2) Making a column sortable
	 * 3) Adding content to the column
	 * 4) Outputting the content.
	 * 5) Adding the meta boxes to the course page
	 * 6) Handling the saving of the data
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'manage_llms_review_posts_columns', array( $this, 'init' ) );
		add_action( 'manage_edit-llms_review_sortable_columns', array( $this, 'make_columns_sortable' ) );
		add_action( 'manage_llms_review_posts_custom_column', array( $this, 'generate_column_data' ), 10, 2 );
		add_filter( 'llms_metabox_fields_lifterlms_course_options', array( $this, 'add_review_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_review_meta_boxes' ) );
	}

	/**
	 * This function generates the custom column set. It takes in
	 * the array of standard columns, then modifies that set to
	 * contain the needed fields.
	 *
	 * @param array $columns The array of standard WP columns
	 *
	 * @return array The updated array of columns.
	 */
	public function init( $columns ) {

		unset( $columns['date'] );
		unset( $columns['comments'] );
		$columns['title']  = __( 'Review Title', 'lifterlms' );
		$columns['course'] = __( 'Course Reviewed', 'lifterlms' );
		$columns['author'] = __( 'Review Author', 'lifterlms' );
		$columns['date']   = __( 'Review Date', 'lifterlms' );
		return $columns;
	}

	/**
	 * This function makes the 'Course' column sortable
	 *
	 * @param array $columns Array of sortable columns
	 * @return array Updated column array.
	 */
	public function make_columns_sortable( $columns ) {

		$columns['course'] = 'course';
		return $columns;
	}

	/**
	 * This function entered the information into the course section
	 * of the llms_review post page. It takes the column that is being
	 * worked on, as well as the comment ID, then echoes the content
	 * required.
	 *
	 * @param string $column  Type of column being worked on
	 * @param int    $post_id ID of comment
	 *
	 * @return void
	 */
	public function generate_column_data( $column, $post_id ) {

		switch ( $column ) {
			case 'course':
				echo ( wp_get_post_parent_id( $post_id ) != 0 ) ? get_the_title( wp_get_post_parent_id( $post_id ) ) : '';
				break;
		}
	}

	/**
	 * This function builds the additional content that is added
	 * to the course meta box. It builds the additional fields and
	 * then returns the updated array of fields
	 *
	 * @param array $content Array of meta fields
	 *
	 * @return array Updated array of meta fields
	 */
	public function add_review_meta_boxes( $content ) {

		/**
		 * This array is what holds the updated fields.
		 * It is created in such a way so that a plugin
		 * can latch onto it to extend the review functionality
		 *
		 * @var array
		 */
		$fields = array(
			array(
				'type'       => 'checkbox',
				'label'      => __( 'Enable Reviews', 'lifterlms' ),
				'desc'       => __( 'Select to enable reviews.', 'lifterlms' ),
				'id'         => self::$prefix . 'llms_reviews_enabled',
				'class'      => '',
				'value'      => '1',
				'desc_class' => 'd-3of4 t-3of4 m-1of2',
				'group'      => '',
			),
			array(
				'type'       => 'checkbox',
				'label'      => __( 'Display Reviews', 'lifterlms' ),
				'desc'       => __( 'Select to display reviews on the page.', 'lifterlms' ),
				'id'         => self::$prefix . 'llms_display_reviews',
				'class'      => 'llms-num-reviews-top',
				'value'      => '1',
				'desc_class' => 'd-3of4 t-3of4 m-1of2',
				'group'      => 'llms-num-reviews-top',
			),
			array(
				'type'       => 'number',
				'min'        => '0',
				'label'      => __( 'Number of Reviews', 'lifterlms' ),
				'desc'       => __( 'Number of reviews to display on the page.', 'lifterlms' ),
				'id'         => self::$prefix . 'llms_num_reviews',
				'class'      => 'input-full',
				'value'      => '',
				'desc_class' => 'd-all',
				'group'      => 'bottom llms-num-reviews-bottom',
			),
			array(
				'type'       => 'checkbox',
				'label'      => __( 'Prevent Multiple Reviews', 'lifterlms' ),
				'desc'       => __( 'Select to prevent a user from submitting more than one review.', 'lifterlms' ),
				'id'         => self::$prefix . 'llms_multiple_reviews_disabled',
				'class'      => '',
				'value'      => '1',
				'desc_class' => 'd-3of4 t-3of4 m-1of2',
				'group'      => '',
			),
		);

		if ( has_filter( 'llms_review_fields' ) ) {
			$fields = apply_filters( 'llms_review_fields', $fields );
		}

		$metaboxtab = array(
			'title'  => __( 'Reviews', 'lifterlms' ),
			'fields' => $fields,
		);
		array_push( $content, $metaboxtab );
		return $content;
	}

	/**
	 * This function handles the logic to save the information that is contained
	 * in the custom metabox. It looks at each of the values, then makes sure that
	 * there are proper default values so that the program doesn't go crashy crashy
	 * (nobody likes crashy crashy)
	 *
	 * @return void
	 */
	public function save_review_meta_boxes() {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified by core before triggering hook.

		$enabled  = ( isset( $_POST['_llms_reviews_enabled'] ) ) ? llms_filter_input( INPUT_POST, '_llms_reviews_enabled', FILTER_SANITIZE_STRING ) : '';
		$display  = ( isset( $_POST['_llms_display_reviews'] ) ) ? llms_filter_input( INPUT_POST, '_llms_display_reviews', FILTER_SANITIZE_STRING ) : '';
		$num      = ( isset( $_POST['_llms_num_reviews'] ) ) ? llms_filter_input( INPUT_POST, '_llms_num_reviews', FILTER_SANITIZE_STRING ) : 0;
		$multiple = ( isset( $_POST['_llms_multiple_reviews_disabled'] ) ) ? llms_filter_input( INPUT_POST, '_llms_multiple_reviews_disabled', FILTER_SANITIZE_STRING ) : '';

		$post_id = llms_filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_NUMBER_INT );

		if ( $post_id ) {
			update_post_meta( $post_id, '_llms_reviews_enabled', $enabled );
			update_post_meta( $post_id, '_llms_display_reviews', $display );
			update_post_meta( $post_id, '_llms_num_reviews', $num );
			update_post_meta( $post_id, '_llms_multiple_reviews_disabled', $multiple );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

	}
}

return new LLMS_Admin_Reviews();
