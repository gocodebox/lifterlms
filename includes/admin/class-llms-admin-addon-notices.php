<?php
/**
 * In-admin promotional notices for uninstalled LifterLMS add-ons.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Addon_Notices class.
 *
 * @since [version]
 */
class LLMS_Admin_Addon_Notices {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 */
	public function __construct() {

		add_filter( 'llms_metabox_fields_lifterlms_coupon', array( $this, 'add_coupons_promo_tab' ) );
		add_filter( 'llms_metabox_fields_lifterlms_course_options', array( $this, 'add_videos_promo_tab' ) );
		add_action( 'lifterlms_after_order_meta_box', array( $this, 'output_order_pdf_lock_button' ) );
		add_action( 'llms_reporting_single_student_course_actions', array( $this, 'output_reporting_pdf_lock_button' ), 50 );
		add_action( 'admin_footer', array( $this, 'output_pdf_dialog' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_pdf_promo_assets' ) );
	}

	/**
	 * Add an Advanced Coupons promo tab on the coupon metabox.
	 *
	 * @since [version]
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_coupons_promo_tab( $tabs ) {

		if ( class_exists( 'LLMS_Advanced_Coupons' ) ) {
			return $tabs;
		}

		$tabs[] = array(
			'title'  => __( 'Advanced Coupons', 'lifterlms' ),
			'fields' => array(
				array(
					'id'    => '_llms_advanced_coupons_promo',
					'type'  => 'custom-html',
					'label' => '',
					'value' => LLMS_Admin_Addon_Promo::get_html(
						array(
							'icon'        => 'tickets-alt',
							'headline'    => __( 'Create More Powerful Coupons', 'lifterlms' ),
							'message'     => __( 'Increase course and membership sales with URL coupons, checkout volume discounts, coupons by country, and more.', 'lifterlms' ),
							'button_text' => __( 'Get LifterLMS Advanced Coupons', 'lifterlms' ),
							'button_url'  => LLMS_Admin_Addon_Promo::get_utm_url(
								'https://lifterlms.com/product/lifterlms-advanced-coupons/',
								'Coupons'
							),
							'below_text'  => __( 'Included in the Infinity Bundle Plan.', 'lifterlms' ),
							'below_url'   => LLMS_Admin_Addon_Promo::get_utm_url(
								'https://lifterlms.com/pricing',
								'Coupons'
							),
						)
					),
				),
			),
		);

		return $tabs;
	}

	/**
	 * Add an Advanced Videos promo tab on the course options metabox.
	 *
	 * @since [version]
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_videos_promo_tab( $tabs ) {

		if ( class_exists( 'LifterLMS_Advanced_Videos' ) ) {
			return $tabs;
		}

		$tabs[] = array(
			'title'  => __( 'Advanced Videos', 'lifterlms' ),
			'fields' => array(
				array(
					'id'    => '_llms_advanced_videos_promo',
					'type'  => 'custom-html',
					'label' => '',
					'value' => LLMS_Admin_Addon_Promo::get_html(
						array(
							'icon'        => 'controls-play',
							'headline'    => __( 'Require 100% Video Watch Time', 'lifterlms' ),
							'message'     => __( 'Require students to watch entire lesson videos before completion, auto advance to the next video lesson like Netflix, configure video player controls, and more.', 'lifterlms' ),
							'button_text' => __( 'Learn About LifterLMS Advanced Videos →', 'lifterlms' ),
							'button_url'  => LLMS_Admin_Addon_Promo::get_utm_url(
								'https://lifterlms.com/product/advanced-videos/',
								'Course Metabox'
							),
							'below_text'  => __( 'Included in the Infinity Bundle Plan.', 'lifterlms' ),
							'below_url'   => LLMS_Admin_Addon_Promo::get_utm_url(
								'https://lifterlms.com/pricing',
								'Course Metabox'
							),
						)
					),
				),
			),
		);

		return $tabs;
	}

	/**
	 * Output a locked PDF download button matching the PDFs add-on order layout.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output_order_pdf_lock_button() {

		if ( $this->is_pdfs_loaded() ) {
			return;
		}

		llms_form_field(
			array(
				'attributes'      => array(
					'data-llms-addon-promo' => 'pdfs',
					'style'                 => 'margin-top: 30px',
				),
				'classes'         => 'llms-button-secondary auto llms-addon-promo-trigger',
				'columns'         => 12,
				'id'              => 'llms-dl-orders-promo',
				'last_column'     => true,
				'name'            => false,
				'type'            => 'button',
				'value'           => $this->get_pdf_download_button_label(),
				'wrapper_classes' => 'align-right',
			)
		);
	}

	/**
	 * Output a locked PDF download button matching the PDFs add-on reporting layout.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output_reporting_pdf_lock_button() {

		if ( $this->is_pdfs_loaded() ) {
			return;
		}

		echo '<style>.llms-addon-promo-dl{display:inline-block;float:right;margin-right:20px;margin-top:-4px;width:200px}</style>';
		echo '<div class="llms-addon-promo-dl">';
		llms_form_field(
			array(
				'attributes'      => array(
					'data-llms-addon-promo' => 'pdfs',
				),
				'classes'         => 'llms-button-secondary small auto llms-addon-promo-trigger',
				'columns'         => 12,
				'id'              => 'llms-dl-grades-promo',
				'last_column'     => true,
				'name'            => false,
				'type'            => 'button',
				'value'           => $this->get_pdf_download_button_label(),
				'wrapper_classes' => 'align-right',
			)
		);
		echo '</div>';
	}

	/**
	 * Enqueue assets for the PDF promo dialog.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function enqueue_pdf_promo_assets() {

		if ( $this->is_pdfs_loaded() || ! $this->is_pdf_promo_screen() ) {
			return;
		}

		llms()->assets->enqueue_script( 'llms-admin-addon-promo' );
	}

	/**
	 * Output the PDF promo dialog once in the admin footer.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output_pdf_dialog() {

		if ( $this->is_pdfs_loaded() || ! $this->is_pdf_promo_screen() ) {
			return;
		}

		$url       = LLMS_Admin_Addon_Promo::get_utm_url(
			'https://lifterlms.com/product/lifterlms-pdfs/',
			'PDF Download Button'
		);
		$below_url = LLMS_Admin_Addon_Promo::get_utm_url(
			'https://lifterlms.com/pricing',
			'PDF Download Button'
		);
		?>
		<style>
			.llms-addon-promo-dialog { border: none; border-radius: 4px; padding: 0; max-width: 420px; }
			.llms-addon-promo-dialog::backdrop { background: rgba(0, 0, 0, 0.45); }
			.llms-addon-promo-dialog-close { position: absolute; top: 8px; right: 8px; background: transparent; border: 0; font-size: 22px; cursor: pointer; line-height: 1; }
			.llms-addon-promo-preheader { margin: 20px 20px 0; text-align: center; text-transform: uppercase; letter-spacing: 0.04em; font-size: 12px; color: #646970; }
			.llms-addon-promo-dialog .llms-addon-promo { position: relative; }
		</style>
		<dialog id="llms-addon-promo-dialog-pdfs" class="llms-addon-promo-dialog">
			<button type="button" class="llms-addon-promo-dialog-close" aria-label="<?php esc_attr_e( 'Close', 'lifterlms' ); ?>">&times;</button>
			<p class="llms-addon-promo-preheader"><?php esc_html_e( 'Unlock LifterLMS PDFs', 'lifterlms' ); ?></p>
			<?php
			echo wp_kses_post(
				LLMS_Admin_Addon_Promo::get_html(
					array(
						'headline'    => __( 'Enable Powerful PDF Generation', 'lifterlms' ),
						'message'     => __( 'Make important elements of your LMS platform, like earned certificates, orders, grades & more… portable and beautiful with powerful PDF generation technology.', 'lifterlms' ),
						'button_text' => __( 'Learn About LifterLMS PDFs →', 'lifterlms' ),
						'button_url'  => $url,
						'below_text'  => __( 'Included in the Infinity Bundle Plan.', 'lifterlms' ),
						'below_url'   => $below_url,
					)
				)
			);
			?>
		</dialog>
		<?php
	}

	/**
	 * Whether the current admin screen should load the PDF promo dialog.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	private function is_pdf_promo_screen() {

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		return in_array(
			$screen->id,
			array(
				'llms_order',
				'lifterlms_page_llms-reporting',
			),
			true
		);
	}

	/**
	 * Whether the LifterLMS PDFs plugin is loaded.
	 *
	 * Checked at callback time so plugin load order cannot leak the promo UI
	 * next to the real download buttons.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	private function is_pdfs_loaded() {
		return function_exists( 'llms_pdfs' );
	}

	/**
	 * Label HTML for promo PDF download buttons.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_pdf_download_button_label() {
		return esc_html__( 'Download', 'lifterlms' ) . ' <i class="fa fa-cloud-download" aria-hidden="true"></i>';
	}
}

new LLMS_Admin_Addon_Notices();
