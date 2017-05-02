<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 *
 */
class LLMS_Metabox_Table_Field extends LLMS_Metabox_Field implements Meta_Box_Field_Interface {

	/**
	 * Class constructor
	 * @param array $_field Array containing information about field
	 */
	function __construct( $_field ) {

		$this->field = $_field;
	}

	/**
	 * outputs the Html for the given field
	 * @return HTML
	 */
	public function output() {

		global $post;

		parent::output(); ?>
			<table class="llms-table zebra text-left">
				<thead>
					<?php foreach ( $this->field['titles'] as $title ) : ?>
						<th><?php echo $title; ?></th>
					<?php endforeach; ?>
				</thead>
				<tbody>
					<?php if ( $this->field['table_data'] ) : ?>
						<?php foreach ( $this->field['table_data'] as $row ) : ?>
							<tr>
								<?php foreach ( $row as $column ) : ?>
									<td><?php echo $column; ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					<?php elseif ( $this->field['empty_message'] ) : ?>
						<tr>
							<td colspan="<?php count( $this->field['titles'] ); ?>">
								<?php echo $this->field['empty_message']; ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		<?php
		parent::close_output();
	}
}

