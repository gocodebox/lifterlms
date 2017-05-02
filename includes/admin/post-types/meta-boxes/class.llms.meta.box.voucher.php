<?php
/**
 * Vouchers Metabox
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Meta_Box_Voucher extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-voucher';
		$this->title = __( 'Voucher Settings', 'lifterlms' );
		$this->screens = array(
			'llms_voucher',
		);
		$this->priority = 'high';

	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_fields() {

		$voucher = new LLMS_Voucher( $this->post->ID );

		$selected_couses = $voucher->get_products( 'course' );
		$selected_memberships = $voucher->get_products( 'llms_membership' );

		return array(
			array(
				'title' => __( 'General', 'lifterlms' ),
				'fields' => array(
					array(
						'data_attributes' => array(
							'post-type' => 'course',
						),
						'type' => 'select',
						'label' => __( 'Courses', 'lifterlms' ),
						'id' => $this->prefix . 'voucher_courses',
						'class' => 'input-full llms-select2-post',
						'selected' => $selected_couses,
						'value' => llms_make_select2_post_array( $selected_couses ),
						'multi' => true,
					),
					array(
						'data_attributes' => array(
							'post-type' => 'llms_membership',
						),
						'type' => 'select',
						'label' => __( 'Membership', 'lifterlms' ),
						'id' => $this->prefix . 'voucher_membership',
						'class' => 'input-full llms-select2-post',
						'selected' => $selected_memberships,
						'value' => llms_make_select2_post_array( $selected_memberships ),
						'multi' => true,
					),
					array(
						'type' => 'custom-html',
						'label' => __( 'Codes', 'lifterlms' ),
						'id' => '',
						'class' => '',
						'value' => self::codes_section_html(),
					),
				),
			),
			array(
				'title' => __( 'Redemptions', 'lifterlms' ),
				'fields' => array(
					array(
						'type' => 'custom-html',
						'label' => __( 'Redemptions', 'lifterlms' ),
						'id' => '',
						'class' => '',
						'value' => self::redemption_section_html(),
					),
				),
			),
		);

	}

	private function codes_section_html() {

		global $post;
		$voucher = new LLMS_Voucher( $post->ID );
		$codes = $voucher->get_voucher_codes();

		ob_start(); ?>
		<div class="llms-voucher-codes-wrapper" id="llms-form-wrapper">
			<table>

				<thead>
				<tr>
					<th></th>
					<th>Code</th>
					<th>Uses</th>
					<th>Actions</th>
				</tr>
				</thead>

				<?php $delete_icon = LLMS_Svg::get_icon( 'llms-icon-close', 'Delete Section', 'Delete Section', 'button-icon' ); ?>
				<script>var delete_icon = '<?php echo $delete_icon ?>';</script>

				<tbody id="llms_voucher_tbody">
				<?php if ( ! empty( $codes ) ) :
					foreach ( $codes as $code ) : ?>
						<tr>
							<td></td>
							<td>
								<input type="text" maxlength="20" placeholder="Code" value="<?php echo $code->code ?>"
									   name="llms_voucher_code[]">
								<input type="hidden" name="llms_voucher_code_id[]" value="<?php echo $code->id ?>">
							</td>
							<td><span><?php echo $code->used ?> / </span><input type="number" min="1" value="<?php echo $code->redemption_count ?>"
														placeholder="Uses" class="llms-voucher-uses"
														name="llms_voucher_uses[]"></td>
							<td>
								<a href="#" data-id="<?php echo $code->id ?>" class="llms-voucher-delete">
									<?php echo $delete_icon; ?>
								</a>
							</td>
						</tr>
					<?php endforeach;
				endif; ?>
				</tbody>

			</table>

			<div class="llms-voucher-add-codes">
				<p>Add <input type="number" max="50" placeholder="#" id="llms_voucher_add_quantity"> new code(s) with <input
						type="number" placeholder="#" id="llms_voucher_add_uses"> use(s) per code
					<button id="llms_voucher_add_codes" class="button-primary">Add</button>
				</p>
			</div>

			<input type="hidden" name="delete_ids" id="delete_ids">
		</div>

		<?php
		return ob_get_clean();
	}

	private function redemption_section_html() {

		global $post;

		$pid = $post->ID;
		$voucher = new LLMS_Voucher( $pid );
		$redeemed_codes = $voucher->get_redeemed_codes();
		ob_start(); ?>

		<div class="llms-voucher-redemption-wrapper">
			<table>

				<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Redemption Date</th>
					<th>Code</th>
				</tr>
				</thead>

				<tbody>
				<?php if ( ! empty( $redeemed_codes ) ) :
					foreach ( $redeemed_codes as $redeemed_code ) :

						$user = get_user_by( 'id', $redeemed_code->user_id );
						?>
						<tr>
							<td><?php echo $user->data->display_name ?></td>
							<td><?php echo $user->data->user_email ?></td>
							<td><?php echo $redeemed_code->redemption_date ?></td>
							<td><?php echo $redeemed_code->code ?></td>
						</tr>
					<?php endforeach;
				endif;
				?>
				</tbody>

			</table>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * save method
	 *
	 * cleans variables and saves using update_post_meta
	 *
	 * @param  int $post_id [id of post object]
	 *
	 * @return void
	 *
	 * @versin 3.0.0
	 */
	public function save( $post_id ) {

		if ( ! empty( $_POST['llms_generate_export'] ) || empty( $_POST['lifterlms_meta_nonce'] ) || ! wp_verify_nonce( $_POST['lifterlms_meta_nonce'], 'lifterlms_save_data' ) ) {
			return false;
		}

		// CODES SAVE
		$codes = array();

		$llms_codes = isset( $_POST['llms_voucher_code'] ) ? $_POST['llms_voucher_code'] : false;
		$llms_uses = isset( $_POST['llms_voucher_uses'] ) ? $_POST['llms_voucher_uses'] : false;
		$llms_voucher_code_id = isset( $_POST['llms_voucher_code_id'] ) ? $_POST['llms_voucher_code_id'] : false;

		$voucher = new LLMS_Voucher( $post_id );

		if ( isset( $llms_codes ) && ! empty( $llms_codes ) && isset( $llms_uses ) && ! empty( $llms_uses ) ) {

			foreach ( $llms_codes as $k => $code ) {
				if ( isset( $code ) && ! empty( $code ) && isset( $llms_uses[ $k ] ) && ! empty( $llms_uses[ $k ] ) ) {

					if ( isset( $llms_voucher_code_id[ $k ] ) ) {

						$data = array(
							'code' => $code,
							'redemption_count' => intval( $llms_uses[ $k ] ),
						);

						if ( intval( $llms_voucher_code_id[ $k ] ) ) {
							$data['id'] = intval( $llms_voucher_code_id[ $k ] );
						}

						$codes[] = $data;
					}
				}
			}
		}

		if ( ! empty( $codes ) ) {
			foreach ( $codes as $code ) {

				if ( isset( $code['id'] ) ) {
					$voucher->update_voucher_code( $code );
				} else {
					$voucher->save_voucher_code( $code );
				}
			}
		}

		// Courses and membership save

		$courses = isset( $_POST['_llms_voucher_courses'] ) ? $_POST['_llms_voucher_courses'] : false;
		$memberships = isset( $_POST['_llms_voucher_membership'] ) ? $_POST['_llms_voucher_membership'] : false;
		$products = array();

		if ( isset( $courses ) && ! empty( $courses ) ) {
			foreach ( $courses as $item ) {
				$products[] = intval( $item );
			}
		}

		if ( isset( $memberships ) && ! empty( $memberships ) ) {
			foreach ( $memberships as $item ) {
				$products[] = intval( $item );
			}
		}

		// remove old products
		$voucher->delete_products();

		// save new ones
		if ( ! empty( $products ) ) {
			foreach ( $products as $item ) {
				$voucher->save_product( $item );
			}
		}

		// set old codes as deleted
		if ( isset( $_POST['delete_ids'] ) && ! empty( $_POST['delete_ids'] ) ) {
			$delete_ids = explode( ',', $_POST['delete_ids'] );

			if ( ! empty( $delete_ids ) ) {
				foreach ( $delete_ids as $id ) {
					$voucher->delete_voucher_code( $id );
				}
			}
		}
	}
}
