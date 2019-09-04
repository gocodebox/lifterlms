<?php
/**
 * LifterLMS Email Header Template
 *
 * @since    1.0.0
 * @version  3.16.15
 */

defined( 'ABSPATH' ) || exit;

$mailer       = LLMS()->mailer();
$header_image = $mailer->get_header_image_src();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
	<style>
	@media all {
		a {
			color: <?php $mailer->get_css( 'main-color' ); ?> !important; }
		p {
			Margin-bottom: 15px !important;
			Margin-top: 0 !important; }
		td[class=main-content] *:first-child {
			Margin-top: 0 !important; }
		td[class=main-content] *:last-child {
			Margin-bottom: 0 !important; }
		table[class=body] img.alignright {
			float:right; }
		table[class=body] img.alignleft {
			float:left; }
		table[class=body] img {
			display: block;
			height: auto !important;
			Margin: 0 auto;
			max-width: 100% !important;
			width: auto !important; }
		.ExternalClass {
			width: 100%; }
		.ExternalClass,
		.ExternalClass p,
		.ExternalClass span,
		.ExternalClass font,
		.ExternalClass td,
		.ExternalClass div {
			line-height: 100%; }
	@media only screen and (max-width: 620px) {
		table[class=body] p,
		table[class=body] ul,
		table[class=body] ol,
		table[class=body] td,
		table[class=body] span,
		table[class=body] a {
			font-size: 16px !important; }
		table[class=body] .wrapper,
		table[class=body] .article {
			padding: 10px !important; }
		table[class=body] .content {
			padding: 0 !important; }
		table[class=body] .container {
			padding: 0 !important;
			width: 100% !important; }
		table[class=body] .main {
			border-left-width: 0 !important;
			border-radius: 0 !important;
			border-right-width: 0 !important; } }
	<?php do_action( 'llms_email_after_css' ); ?>
	</style>
</head>
<body class="" style="background-color:<?php $mailer->get_css( 'background-color' ); ?>;color:<?php $mailer->get_css( 'font-color' ); ?>;font-family:<?php $mailer->get_css( 'font-family' ); ?>;-webkit-font-smoothing:antialiased;font-size:<?php $mailer->get_css( 'font-size' ); ?>;line-height:1.4;margin:0;padding:0;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
<table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse:collapse;color:<?php $mailer->get_css( 'font-color' ); ?>;mso-table-lspace:0pt;mso-table-rspace:0pt;background-color:<?php $mailer->get_css( 'background-color' ); ?>;width:100%;">
<tr>
	<td style="font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:<?php $mailer->get_css( 'font-size' ); ?>;vertical-align:top;">&nbsp;</td>
	<td class="container" style="font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:<?php $mailer->get_css( 'font-size' ); ?>;vertical-align:top;display:block;max-width:<?php $mailer->get_css( 'max-width' ); ?>;padding:10px;width:<?php $mailer->get_css( 'max-width' ); ?>;Margin:0 auto !important;">

		<?php if ( $header_image ) : ?>
		<div class="content" style="box-sizing:border-box;display:block;Margin:0 auto;max-width:<?php $mailer->get_css( 'max-width' ); ?>;padding:10px;">
			<img alt="<?php echo get_bloginfo( 'name' ); ?>" src="<?php echo esc_url( $header_image ); ?>" style="display:block;height:auto;Margin:0 auto;max-width:100%;" />
		</div>
		<?php endif; ?>

		<!-- START CENTERED WHITE CONTAINER -->
		<div class="content" style="box-sizing:border-box;color:<?php $mailer->get_css( 'font-color' ); ?>;display:block;Margin:0 auto;max-width:<?php $mailer->get_css( 'max-width' ); ?>;padding:10px;">

			<?php if ( ! empty( $email_heading ) ) : ?>
			<!-- START HEADING AREA -->
			<table class="main" style="background:<?php $mailer->get_css( 'heading-background-color' ); ?>;border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;border-radius:<?php printf( '%1$s %1$s 0 0', $mailer->get_css( 'border-radius' ) ); ?>;width:100%;">
				<tr>
					<td class="wrapper" style="font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:<?php $mailer->get_css( 'font-size' ); ?>;vertical-align:top;box-sizing:border-box;padding:0;">
						<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%;">
							<tr>
								<td style="color:<?php $mailer->get_css( 'heading-font-color' ); ?>;font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:28px;vertical-align:top;">
									<h1 style="color:<?php $mailer->get_css( 'heading-font-color' ); ?>;font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:28px;font-weight:400;Margin:0;padding:20px;">
										<?php echo $email_heading; ?>
									</h1>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<!-- END HEADING AREA -->
			<?php endif; ?>

			<table class="main" style="border-collapse:collapse;color:<?php $mailer->get_css( 'font-color' ); ?>;mso-table-lspace:0pt;mso-table-rspace:0pt;background:#fff;border-radius:<?php echo ! empty( $email_heading ) ? sprintf( '0 0 %1$s %1$s', $mailer->get_css( 'border-radius' ) ) : $mailer->get_css( 'border-radius' ); ?>;width:100%;">
				<tr>
					<td class="wrapper" style="color:<?php $mailer->get_css( 'font-color' ); ?>;font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:<?php $mailer->get_css( 'font-size' ); ?>;vertical-align:top;box-sizing:border-box;padding:20px;">
						<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;color:<?php $mailer->get_css( 'font-color' ); ?>;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%;">
							<tr>
								<td class="main-content" style="color:<?php $mailer->get_css( 'font-color' ); ?>;font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:<?php $mailer->get_css( 'font-size' ); ?>;vertical-align:top;">
