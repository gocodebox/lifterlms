<?php
/**
 * Theme Support: Twenty Twenty
 *
 * @package LifterLMS/ThemeSupport/Classes
 *
 * @since 3.37.0
 * @version 4.10.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Twenty_Twenty class..
 *
 * @since 3.37.0
 * @since 3.37.1 Fixed course information block misalignment.
 * @since 3.37.2 Updated to use `background-color` property instead of `background` shorthand
 *               when adding custom elements to style.
 * @since 3.37.3 Hide site header and footer, and set a white body background in
 *               single certificates.
 */
class LLMS_Twenty_Twenty {

	/**
	 * Constructor.
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	public static function init() {

		// This theme doesn't have a sidebar.
		remove_action( 'lifterlms_sidebar', 'lifterlms_get_sidebar', 10 );

		// Handle content wrappers.
		remove_action( 'lifterlms_before_main_content', 'lifterlms_output_content_wrapper', 10 );
		remove_action( 'lifterlms_after_main_content', 'lifterlms_output_content_wrapper_end', 10 );

		add_action( 'lifterlms_before_main_content', array( __CLASS__, 'output_content_wrapper' ), 10 );
		add_action( 'lifterlms_after_main_content', array( __CLASS__, 'output_content_wrapper_end' ), 10 );

		// Add the proper Twenty Twenty Body class on the catalogs.
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );

		// Modify catalog & checkout columns when the catalog page isn't full width.
		add_filter( 'lifterlms_loop_columns', array( __CLASS__, 'modify_columns_count' ) );
		add_filter( 'llms_checkout_columns', array( __CLASS__, 'modify_columns_count' ) );

		// Prevent meta output for LifterLMS custom Post Types.
		add_filter( 'twentytwenty_disallowed_post_types_for_meta_output', array( __CLASS__, 'hide_meta_output' ) );

		add_filter( 'twentytwenty_get_elements_array', array( __CLASS__, 'add_elements' ) );

		add_action( 'wp_head', array( __CLASS__, 'add_inline_styles' ), 100 );
	}

	/**
	 * Generate inline CSS using colors from the TwenyTwenty Theme settings.
	 *
	 * @since 3.37.0
	 * @since 3.37.1 Fixed course information block misalignment.
	 * @since 3.37.3 Hide site header and footer, and set a white body background in single certificates.
	 *
	 * @return void
	 */
	public static function add_inline_styles() {

		global $post_type;
		$accent = twentytwenty_get_color_for_area( 'content', 'accent' );

		?>
		<style id="llms-twentytweny-style">

		.llms-access-plan.featured .llms-access-plan-content,
		.llms-access-plan.featured .llms-access-plan-footer {
			border-left-color: <?php echo sanitize_hex_color( $accent ); ?>;
			border-right-color: <?php echo sanitize_hex_color( $accent ); ?>;
		}
		.llms-access-plan.featured .llms-access-plan-footer {
			border-bottom-color: <?php echo sanitize_hex_color( $accent ); ?>;
		}
		.llms-form-field.type-radio input[type=radio]:checked+label:before {
			background-image: -webkit-radial-gradient(center,ellipse,<?php echo sanitize_hex_color( $accent ); ?> 0,<?php echo sanitize_hex_color( $accent ); ?> 40%,#fafafa 45%);
			background-image: radial-gradient(ellipse at center,<?php echo sanitize_hex_color( $accent ); ?> 0,<?php echo sanitize_hex_color( $accent ); ?> 40%,#fafafa 45%);
		}
		.llms-checkout-section,
		.llms-lesson-preview section.llms-main  {
			padding-bottom: 0;
			padding-top: 0;
		}
		.llms-lesson-link .llms-pre-text,
		.llms-access-plan .llms-access-plan-title {
			margin-top: 0;
		}
		.llms-donut svg path {
			stroke: <?php echo sanitize_hex_color( $accent ); ?>;
		}
		.llms-notification,
		.llms-instructor-info .llms-instructors .llms-author {
			border-top-color: <?php echo sanitize_hex_color( $accent ); ?>;
		}
		.llms-pagination ul li:first-of-type,
		.llms-pagination ul {
			margin-left: 0;
			margin-right: 0;
		}
		.course .llms-meta-info {
			margin-left: auto;
			margin-right: auto;
		}
		<?php if ( 'llms_my_certificate' === $post_type || 'llms_certificate' === $post_type ) : ?>
		body {
			background-color: #fff;
			background-image: none;
		}
		#site-header,
		#site-footer {
			display: none;
		}
		<?php endif; ?>
		</style>
		<?php
	}

	/**
	 * Add LifterLMS Elments to the array of Twenty Twenty elements.
	 *
	 * This is used to automatically generate inline CSS via the Twenty Twenty Theme.
	 *
	 * @since 3.37.0
	 * @since 3.37.2 Updated to use `background-color` property instead of `background` shorthand.
	 *
	 * @param array $elements Multidimensional array of CSS selectors.
	 * @return array
	 */
	public static function add_elements( $elements ) {

		// Accent Background.
		$elements['content']['accent']['background-color'] = array_merge(
			$elements['content']['accent']['background-color'],
			self::add_elements_content_accent_background()
		);

		// Accent Border Color.
		$elements['content']['accent']['border-color'] = array_merge(
			$elements['content']['accent']['border-color'],
			self::add_elements_content_accent_border()
		);

		// Accent Color.
		$elements['content']['accent']['color'] = array_merge(
			$elements['content']['accent']['color'],
			self::add_elements_content_accent_color()
		);

		// Background Text Color.
		$elements['content']['background']['color'] = array_merge(
			$elements['content']['background']['color'],
			self::add_elements_content_background_color()
		);

		// Background Background Color.
		$elements['content']['background']['background-color'] = array_merge(
			$elements['content']['background']['background-color'],
			array( '.llms-checkout' )
		);

		// Text Color.
		$elements['content']['text']['color'] = array_merge(
			$elements['content']['text']['color'],
			array(
				'.llms-notice.llms-debug',
				'.llms-notice.llms-debug a',
			)
		);

		return $elements;
	}

	/**
	 * Get an array of selectors for items that have the accent color as the background.
	 *
	 * @since 3.37.0
	 * @since 4.10.0 Use LLMS_Theme_Support utility classes.
	 *
	 * @return string[]
	 */
	protected static function add_elements_content_accent_background() {
		return LLMS_Theme_Support::get_selectors_primary_color_background();
	}

	/**
	 * Get an array of selectors for items that have the accent color as the border.
	 *
	 * @since 3.37.0
	 * @since 4.10.0 Use LLMS_Theme_Support utility classes.
	 *
	 * @return string[]
	 */
	protected static function add_elements_content_accent_border() {
		return LLMS_Theme_Support::get_selectors_primary_color_border();
	}

	/**
	 * Get an array of selectors for items that have the accent color as the text color.
	 *
	 * @since 3.37.0
	 * @since 4.10.0 Use LLMS_Theme_Support utility classes.
	 *
	 * @return string[]
	 */
	protected static function add_elements_content_accent_color() {
		return LLMS_Theme_Support::get_selectors_primary_color_text();
	}

	/**
	 * Get an array of selectors for items that have the background color as the text color.
	 *
	 * @since 3.37.0
	 *
	 * @return string[]
	 */
	protected static function add_elements_content_background_color() {

		return array(

			// Buttons.
			'.llms-button-primary',
			'.llms-button-primary:hover',
			'.llms-button-primary.clicked',
			'.llms-button-primary:focus',
			'.llms-button-primary:active',
			'.llms-button-action',
			'.llms-button-action:hover',
			'.llms-button-action.clicked',
			'.llms-button-action:focus',
			'.llms-button-action:active',

			// Pricing Tables.
			'.llms-access-plan-title',
			'.llms-access-plan .stamp',
			'.llms-access-plan.featured .llms-access-plan-featured',

			// Checkout.
			'.llms-checkout-wrapper .llms-form-heading',

			// Notices.
			'.llms-notice',
			'.llms-notice a',

			// My Grades.
			'.llms-sd-widgets .llms-sd-widget .llms-sd-widget-title',

		);
	}

	/**
	 * Add Twenty Twenty's full-width template body class on catalogs where the page is set to use the Full Width template.
	 *
	 * @since 3.37.0
	 *
	 * @param string[] $classes Array of body classes.
	 * @return string[]
	 */
	public static function body_classes( $classes ) {

		$page_id = self::get_archive_page_id();
		if ( $page_id && self::is_page_full_width( $page_id ) ) {
			$classes[] = 'template-full-width';
		}

		return $classes;
	}

	/**
	 * Retrieve the page ID of a a catalog page.
	 *
	 * @since 3.37.0
	 *
	 * @return int|false
	 */
	protected static function get_archive_page_id() {

		$page_id = false;

		if ( is_courses() ) {
			$page_id = llms_get_page_id( 'courses' );
		} elseif ( is_memberships() ) {
			$page_id = llms_get_page_id( 'memberships' );
		}

		return $page_id;
	}

	/**
	 * Get the twenty twenty theme's "width" class for use in wrapper elements.
	 *
	 * If the "Full Width" template is utilized, there's no class, otherwise the class `thin` is used.
	 *
	 * @since 3.37.0
	 *
	 * @return string
	 */
	protected static function get_page_template_class() {

		$template_class = 'thin';
		$page_id        = self::get_archive_page_id();

		if ( $page_id ) {
			$template_class = self::is_page_full_width( $page_id ) ? '' : 'thin';
		} else {
			$template_class = is_page_template( 'templates/template-full-width.php' ) ? '' : 'thin';
		}

		return $template_class;
	}

	/**
	 * Prevent theme meta information from being output on LifterLMS Custom Post Types.
	 *
	 * @since 3.37.0
	 *
	 * @param string[] $post_types Array of post type names.
	 * @return string[]
	 */
	public static function hide_meta_output( $post_types ) {

		return array_merge( $post_types, array( 'course', 'llms_membership', 'lesson', 'llms_quiz' ) );
	}

	/**
	 * Determine if the given page is utilizing the twenty twenty full-width page template.
	 *
	 * @since 3.37.0
	 *
	 * @param int $page_id WP_Post ID of the catalog page.
	 * @return bool
	 */
	protected static function is_page_full_width( $page_id ) {

		return 'templates/template-full-width.php' === get_page_template_slug( $page_id );
	}

	/**
	 * Modify the number of catalog & checkout columns.
	 *
	 * If the default template is used, drop to a single column.
	 *
	 * @since 3.37.0
	 *
	 * @param int $cols Number of columns.
	 * @return int
	 */
	public static function modify_columns_count( $cols ) {

		if ( 'thin' === self::get_page_template_class() ) {
			return 1;
		}

		return $cols;
	}

	/**
	 * Output the opening wrapper for the content description element in the theme's header.
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	public static function output_archive_description_wrapper() {
		echo '<div class="archive-subtitle section-inner thin max-percentage intro-text">';
	}

	/**
	 * Output the closing wrapper for the content description element in the theme's header.
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	public static function output_archive_description_wrapper_end() {
		echo '</div><!-- .archive-subtitle -->';
	}

	/**
	 * Output Twenty Twenty theme wrapper openers
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	public static function output_content_wrapper() {

		$show_title = apply_filters( 'lifterlms_show_page_title', true );
		$has_desc   = has_action( 'lifterlms_archive_description' );

		if ( $has_desc ) {
			add_action( 'lifterlms_archive_description', array( __CLASS__, 'output_archive_description_wrapper' ), -1 );
			add_action( 'lifterlms_archive_description', array( __CLASS__, 'output_archive_description_wrapper_end' ), 99999999 );
		}

		if ( $show_title ) {
			add_filter( 'lifterlms_show_page_title', '__return_false' );
		}

		?>
		<main id="site-content" role="main">

			<?php if ( $show_title || $has_desc ) : ?>
				<header class="archive-header has-text-align-center header-footer-group">

					<div class="archive-header-inner section-inner medium">
						<?php if ( $show_title ) : ?>
							<h1 class="archive-title"><?php lifterlms_page_title(); ?></h1>
						<?php endif; ?>
			<?php endif; ?>
		<?php

		// If there's no description, output the end wrapper now.
		if ( $show_title && ! $has_desc ) {
			self::output_content_wrapper_part_two();
		} else {
			// Otherwise output the wrapper after the end wrapper for the description wrapper div.
			add_action( 'lifterlms_archive_description', array( __CLASS__, 'output_content_wrapper_part_two' ), 99999999 );
		}
	}

	/**
	 * Outputs header closing wrappers and inner element opening wrappers for the theme wrappers.
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	public static function output_content_wrapper_part_two() {
		?>
			</div><!-- .archive-header-inner -->
		</header><!-- .archive-header -->
		<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
			<div class="post-inner section-inner <?php echo esc_attr( self::get_page_template_class() ); ?> ">
				<div class="entry-content">
		<?php
	}

	/**
	 * Output Twenty Twenty theme wrapper closers
	 *
	 * @since 3.37.0
	 *
	 * @return void
	 */
	public static function output_content_wrapper_end() {
		?>
					</div><!-- .entry-content -->
				</div><!-- .post-inner -->
			</article><!-- .post -->
		</main><!-- #site-content -->
		<?php
	}
}

return LLMS_Twenty_Twenty::init();
