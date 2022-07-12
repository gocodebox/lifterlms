<?php
/**
 * LLMS_Twenty_Twenty_Two class file
 *
 * @package LifterLMS/ThemeSupport/Classes
 *
 * @since 5.8.0
 * @version 6.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme Support: Twenty Twenty-Two.
 *
 * @since 5.8.0
 */
class LLMS_Twenty_Twenty_Two {

	/**
	 * Static "constructor".
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public static function init() {

		// This theme doesn't have a sidebar.
		remove_action( 'lifterlms_sidebar', 'lifterlms_get_sidebar', 10 );

		// Handle content wrappers.
		remove_action( 'lifterlms_before_main_content', 'lifterlms_output_content_wrapper', 10 );
		remove_action( 'lifterlms_after_main_content', 'lifterlms_output_content_wrapper_end', 10 );

		add_action( 'lifterlms_before_main_content', array( __CLASS__, 'handle_page_header_wrappers' ) );

		// Modify catalog & checkout columns when the catalog page isn't full width.
		add_filter( 'lifterlms_loop_columns', array( __CLASS__, 'modify_columns_count' ) );
		add_filter( 'llms_checkout_columns', array( __CLASS__, 'modify_columns_count' ) );

		// Use theme colors for various LifterLMS elements.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_inline_styles' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'add_inline_editor_styles' ) );

	}

	/**
	 * Enqueue inline styles for the block editor.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public static function add_inline_editor_styles() {
		wp_add_inline_style( 'llms-blocks-editor', self::generate_inline_styles( 'editor' ) );
	}

	/**
	 * Enqueue inline styles on the frontend.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public static function add_inline_styles() {
		wp_add_inline_style( 'twentytwentytwo-style', self::generate_inline_styles() );
	}

	/**
	 * Generate inline CSS for a given context.
	 *
	 * @since 5.8.0
	 * @since 5.9.0 Fixed stretched images in questions with pictures, and images in quiz/questions description.
	 * @since 6.8.0 Fixed label/text alignment by removing text’s margin top. Also, removed now outdated width rule.
	 *
	 * @param string|null $context Inline CSS context. Accepts "editor" to define styles loaded within the block editor or `null` for frontend styles.
	 * @return string
	 */
	protected static function generate_inline_styles( $context = null ) {

		$selector_prefix = ( 'editor' === $context ) ? '.editor-styles-wrapper' : '';

		$styles = array();

		// Frontend only.
		if ( is_null( $context ) ) {

			// Fix alignment of content in an access plan, and navigation.
			$styles[] = '.llms-access-plan-description ul, .llms-pagination ul { padding-left: 0; }';

			// Fix form input padding.
			$styles[] = '.llms-form-field input, .llms-form-field textarea, .llms-form-field select { padding: 6px 10px }';

			// Question layout.
			$styles[] = '.llms-question-wrapper ol.llms-question-choices li.llms-choice .llms-choice-text { margin-top: 0; }';

			// Payment gateway stylized radio buttons.
			$styles[] = LLMS_Theme_Support::get_css(
				array( '.llms-form-field.type-radio:not(.is-group) input[type=radio]:checked+label:before' ),
				array(
					'background-image' => '-webkit-radial-gradient(center,ellipse,var(--wp--preset--color--primary) 0,var(--wp--preset--color--primary) 40%,#fafafa 45%)',
					'background-image' => 'radial-gradient(ellipse at center,var(--wp--preset--color--primary) 0,var(--wp--preset--color--primary) 40%,#fafafa 45%)',
				)
			);
			// Completed lesson check.
			$styles[] = LLMS_Theme_Support::get_css(
				array(
					'.llms-lesson-preview.is-free .llms-lesson-complete',
					'.llms-lesson-preview.is-complete .llms-lesson-complete',
				),
				array(
					'color' => 'var(--wp--preset--color--primary)',
				)
			);
		}

		// Editor only.
		if ( 'editor' === $context ) {

			// Elements with a light background that become unreadable in darkmode in the block editor.
			$styles[] = LLMS_Theme_Support::get_css(
				array(
					'.wp-block-llms-course-progress .progress-bar .progress--fill',
					'.wp-block[data-type="llms/course-continue-button"] button',
					'.wp-block[data-type="llms/lesson-progression"] button',
				),
				array(
					'background-color' => 'var(--wp--preset--color--primary)',
					'color'            => 'var(--wp--preset--color--background)',
				),
				$selector_prefix
			);

		}

		// Fix lesson preview titles.
		$styles[] = '.llms-lesson-preview h6 { margin: 0 0 10px; }';

		// Primary background color.
		$styles[] = LLMS_Theme_Support::get_css(
			LLMS_Theme_Support::get_selectors_primary_color_background(),
			array(
				'background-color' => 'var(--wp--preset--color--primary)',
				'color'            => 'var(--wp--preset--color--background)',
			)
		);

		// Add border color to qualifying elements.
		$styles[] = LLMS_Theme_Support::get_css(
			LLMS_Theme_Support::get_selectors_primary_color_border(),
			array(
				'border-color' => 'var(--wp--preset--color--primary)',
			)
		);

		// Quiz.
		$styles[] = '.llms-quiz-ui { background: transparent; }';
		// Fix questions with pictures, and images in quiz/questions description.
		$styles[] = '.llms-quiz-wrapper img, .llms-quiz-question-wrapper img { max-width: 100%; height: auto; }';

		// Fix anchor buttons.
		$styles[] = 'a.llms-button-action, a.llms-button-danger, a.llms-button-primary, a.llms-button-secondary { display: inline-block; }';

		return implode( "\r", $styles );

	}

	/**
	 * Handle wrapping the catalog page header in 2022 theme elements.
	 *
	 * This method determines if the catalog title are to be displayed and adds additional actions
	 * which will wrap the elements in 2022 theme elements depending on what is meant to be displayed.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public static function handle_page_header_wrappers() {

		/** This filter is documented in templates/loop.php */
		$show_title = apply_filters( 'lifterlms_show_page_title', true );

		if ( $show_title ) {
			add_action( 'lifterlms_before_main_content', array( __CLASS__, 'page_header_wrap' ), 11 );
			add_action( 'lifterlms_archive_description', array( __CLASS__, 'page_header_wrap_end' ), 99999999 );
		}

		if ( $show_title && ! empty( lifterlms_get_archive_description() ) ) {
			add_action( 'lifterlms_archive_description', array( __CLASS__, 'output_archive_description_wrapper' ), -1 );
			add_action( 'lifterlms_archive_description', array( __CLASS__, 'output_archive_description_wrapper_end' ), 99999998 );
		}

	}

	/**
	 * Modify the number of catalog & checkout columns.
	 *
	 * @since 5.8.0
	 *
	 * @param int $cols Number of columns.
	 * @return int
	 */
	public static function modify_columns_count( $cols ) {
		return 1;
	}

	/**
	 * Output the catalog archive description 2022 theme wrapper opener.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public static function output_archive_description_wrapper() {
		echo '<div class="archive-description">';
	}

	/**
	 * Output the catalog archive description 2022 theme wrapper closer.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public static function output_archive_description_wrapper_end() {
		echo '</div><!-- .archive-description -->';
	}

	/**
	 * Output the catalog page header 2022 theme wrapper opener.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public static function page_header_wrap() {
		echo '<header class="page-header alignwide">';
	}

	/**
	 * Output the catalog page header 2022 theme wrapper closer.
	 *
	 * @since 5.8.0
	 *
	 * @return void
	 */
	public static function page_header_wrap_end() {
		echo '</header><!-- .page-header -->';
	}

}

return LLMS_Twenty_Twenty_Two::init();
