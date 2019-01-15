<?php
/**
 * Notifications Center
 *
 * @since    3.8.0
 * @version  3.26.3
 */

defined( 'ABSPATH' ) || exit;

$sep = apply_filters( 'lifterlms_my_account_navigation_link_separator', '&bull;' );
?>

<div class="llms-sd-notification-center">

<!-- 	<nav class="llms-sd-nav">
		<ul class="llms-sd-items">
			<?php foreach ( $sections as $data ) : ?>
				<li class="llms-sd-item"><a class="llms-sd-link" href="<?php echo esc_url( $data['url'] ); ?>"><?php echo $data['name']; ?></a><span class="llms-sep"><?php echo $sep; ?></span></li>
			<?php endforeach; ?>
		</ul>
	</nav> -->

	<?php if ( isset( $notifications ) ) : ?>

		<?php if ( ! $notifications ) : ?>
			<p><?php _e( 'You have no notifications.', 'lifterlms' ); ?></p>
		<?php else : ?>
			<ol class="llms-notification-list">
			<?php foreach ( $notifications as $noti ) : ?>
				<li class="llms-notification-list-item">
					<?php echo $noti->get_html(); ?>
				</li>
			<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<footer class="llms-sd-pagination llms-my-notifications-pagination">
			<nav class="llms-pagination">
			<?php echo paginate_links( array(
				'base'         => str_replace( 999999, '%#%', esc_url( get_pagenum_link( 999999 ) ) ),
				'format'       => '?page=%#%',
				'total'        => $pagination['max'],
				'current'      => $pagination['current'],
				'prev_next'    => true,
				'prev_text'    => '« ' . __( 'Previous', 'lifterlms' ),
				'next_text'    => __( 'Next', 'lifterlms' ) . ' »',
				'type'         => 'list',
			) ); ?>
			</nav>
		</footer>

	<?php elseif ( isset( $settings ) ) : ?>

		<?php foreach ( $settings as $type => $triggers ) : ?>

			<h4><?php echo apply_filters( 'llms_notification_' . $type . '_title', $type ); ?></h4>
			<p><?php echo apply_filters( 'llms_notification_' . $type . '_desc', '' ); ?></p>
			<?php foreach ( $triggers as $id => $data ) : ?>
				<?php llms_form_field( array(
					'description' => '',
					'id' => $id,
					'label' => $data['name'],
					'last_column' => true,
					'name' => 'llms_notification_pref[' . $type . '][' . $id . ']',
					'selected' => ( 'yes' === $data['value'] ),
					'type'  => 'checkbox',
					'value' => 'yes',
				) ); ?>
			<?php endforeach; ?>

		<?php endforeach; ?>

	<?php endif; ?>

</div>
