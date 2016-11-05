<?php
/**
 * Single Student View: Achievements Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$table = new LLMS_AGBT_Achievements();
?>

<table class="llms-table zebra">
	<thead>
		<tr>
		<?php foreach ( $table->get_columns() as $id => $title ) : ?>
			<th class="<?php echo $id; ?>"><?php echo $title; ?></th>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $student->get_achievements() as $a ) : ?>
			<tr>
			<?php foreach ( $table->get_columns() as $id => $title ) : ?>
				<td class="<?php echo $id; ?>"><?php echo $table->get_data( $id, $a ); ?></td>
			<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

