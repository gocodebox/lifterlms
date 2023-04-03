<?php
/**
 * Admin Dashboard Widget
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Dashboard Widget class.
 *
 * @since [version]
 */
class LLMS_Admin_Dashboard_Widget {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
	}

	/**
	 * Add the dashboard widget.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'llms_dashboard_widget',
			'LifterLMS ' . __( 'Quick Links', 'lifterlms' ),
			array( $this, 'output' )
		);
	}

	/**
	 * Output the dashboard widget.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output() {
		?>
		<div class="llms-dashboard-widget-wrap">
			<h3><?php esc_html_e( 'Activity this week:', 'lifterlms' ); ?></h3>
			<a class="llms-button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=course' ) ); ?>">
				<i class="fa fa-graduation-cap" aria-hidden="true"></i>
				<?php esc_html_e( 'Create a New Course', 'lifterlms' ); ?>
			</a>
		</div>
		<div class="activity-block">
			<?php echo $this->get_widgets(); ?>
		</div>
		<div class="activity-block">
			<h3><?php esc_html_e( 'LifterLMS News & Podcasts', 'lifterlms' ); ?></h3>
		</div>
		<ul class="llms-dashboard-widget-feed">
			<?php foreach ( $this->get_feed() as $item ) : ?>
				<li>
					<a href="<?php echo esc_url( $item->get_permalink() ); ?>" target="_blank" rel="noopener">
						<?php echo esc_html( $item->get_title() ); ?>
					</a>
					<span class="llms-dashboard-widget-feed-date">
						<?php echo esc_html( date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ) ); ?>
						|
						<?php echo strpos( $item->get_permalink(), '//podcast' ) !== false ? esc_html__( 'Podcast', 'lifterlms' ) : esc_html__( 'Blog', 'lifterlms' ); ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
		<ul class="subsubsub">
			<li>
				<a href="https://lifterlms.com/blog/" target="_blank" rel="noopener">
					<?php esc_html_e( 'View all blog posts', 'lifterlms' ); ?>
					<span class="screen-reader-text">
						<?php esc_html_e( '(opens in a new tab)', 'lifterlms' ); ?>
					</span>
					<span aria-hidden="true" class="dashicons dashicons-external"></span>
				</a>
				|
			</li>
			<li>
				<a href="https://podcast.lifterlms.com/" target="_blank" rel="noopener">
					<?php esc_html_e( 'View all podcasts', 'lifterlms' ); ?>
					<span class="screen-reader-text">
						<?php esc_html_e( '(opens in a new tab)', 'lifterlms' ); ?>
					</span>
					<span aria-hidden="true" class="dashicons dashicons-external"></span>
				</a>
				|
			</li>
			<li>
				<a href="https://lifterlms.com/help/" target="_blank" rel="noopener">
					<?php esc_html_e( 'Get support', 'lifterlms' ); ?>
					<span class="screen-reader-text">
						<?php esc_html_e( '(opens in a new tab)', 'lifterlms' ); ?>
					</span>
					<span aria-hidden="true" class="dashicons dashicons-external"></span>
				</a>
			</li>
		</ul>
		<?php
	}

	/**
	 * Get the widget HTML.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_widgets(): string {
		return llms_get_template(
			'admin/reporting/tabs/widgets.php',
			array(
				'json'        => wp_json_encode(
					array(
						'current_tab'         => 'settings',
						'current_range'       => 'last-7-days',
						'current_students'    => array(),
						'current_courses'     => array(),
						'current_memberships' => array(),
						'dates'               => array(
							'start' => date( 'Y-m-d', strtotime( '-1 week' ) ),
							'end'   => current_time( 'Y-m-d' ),
						),
					)
				),
				'widget_data' => array(
					array(
						'enrollments'       => array(
							'title'       => __( 'Enrollments', 'lifterlms' ),
							'cols'        => '1-4',
							'content'     => __( 'loading...', 'lifterlms' ),
							'content_tag' => 'p',
							'info'        => __( 'Number of total enrollments during the selected period', 'lifterlms' ),
							'link'        => admin_url( 'admin.php?page=llms-reporting&tab=enrollments' ),
						),
						'registrations'     => array(
							'title'       => __( 'Registrations', 'lifterlms' ),
							'cols'        => '1-4',
							'content'     => __( 'loading...', 'lifterlms' ),
							'content_tag' => 'p',
							'info'        => __( 'Number of total user registrations during the selected period', 'lifterlms' ),
							'link'        => admin_url( 'admin.php?page=llms-reporting&tab=students' ),
						),
						'sold'              => array(
							'title'       => __( 'Net Sales', 'lifterlms' ),
							'cols'        => '1-4',
							'content'     => __( 'loading...', 'lifterlms' ),
							'content_tag' => 'p',
							'info'        => __( 'Total of all successful transactions during this period', 'lifterlms' ),
							'link'        => admin_url( 'admin.php?page=llms-reporting&tab=sales' ),
						),
						'lessoncompletions' => array(
							'title'       => __( 'Lessons Completed', 'lifterlms' ),
							'cols'        => '1-4',
							'content'     => __( 'loading...', 'lifterlms' ),
							'content_tag' => 'p',
							'info'        => __( 'Number of total lessons completed during the selected period', 'lifterlms' ),
							'link'        => admin_url( 'admin.php?page=llms-reporting&tab=courses' ),
						),
					),
				),
			)
		) ?? '';
	}

	/**
	 * Get blog and podcast feed.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_feed(): array {
		$blog    = fetch_feed( 'https://lifterlms.com/feed' );
		$podcast = fetch_feed( 'https://podcast.lifterlms.com/feed/' );

		if ( ! is_wp_error( $blog ) ) {
			$blog_max   = $blog->get_item_quantity( 3 );
			$blog_items = $blog->get_items( 0, $blog_max );
		}

		if ( ! is_wp_error( $podcast ) ) {
			$podcast_max   = $podcast->get_item_quantity( 3 );
			$podcast_items = $podcast->get_items( 0, $podcast_max );
		}

		$merged = array_merge(
			$blog_items ?? array(),
			$podcast_items ?? array()
		);

		usort(
			$merged,
			function ( $a, $b ) {
				return $b->get_date( 'U' ) - $a->get_date( 'U' );
			}
		);

		return array_slice( $merged, 0, 5 );
	}
}

return new LLMS_Admin_Dashboard_Widget();
