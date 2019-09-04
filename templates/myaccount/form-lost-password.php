<?php
defined( 'ABSPATH' ) || exit;
?>

<?php llms_print_notices(); ?>

<form action="" class="llms-lost-password-form" method="POST">

	<?php foreach ( $fields as $field ) : ?>
		<?php llms_form_field( $field ); ?>
	<?php endforeach; ?>

	<?php wp_nonce_field( 'llms_' . $form, '_' . $form . '_nonce' ); ?>

</form>
