<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$blog_title = get_bloginfo( 'name' );

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?php echo $blog_title; ?><</title>
	<style type="text/css">		
		body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
		img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
		a img {border:none;}
		p, h1, h2, h3, h4, h5, h6 {line-height: 1;}
		table td {border-collapse: collapse;}
		table { mso-table-lspace:0pt; mso-table-rspace:0pt; }
		.hentry {background-color:none;}
		.image_fix {display:block;}
		#backgroundTable {margin:0; padding:20px; width:100% !important; line-height: 100% !important;}
		#outlook a {padding:0;}
		.ExternalClass {width:100%;}
		.ExternalClass, 
		.ExternalClass p, 
		.ExternalClass span, 
		.ExternalClass font, 
		.ExternalClass td, 
		.ExternalClass div {line-height: 100%;}
	</style>
</head>
<body>
	<table cellpadding="0" cellspacing="0" border="0" id="backgroundTable">
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" border="0" align="center">
					<tr>
						<td align="center" valign="top" width="600">
						<?php
						if ( $blog_logo = get_option( 'lifterlms_email_header_image' ) ) {

							echo '<img class="image_fix" src="' . esc_url( $blog_logo ) . '" alt="' . get_bloginfo( 'name' ) . '"/>';
						}
						?>
						</td>
					</tr>
					<tr>
						<td align="center" width="600">
	                    	<h1><?php echo $email_heading; ?></h1>
	                    </td>
					</tr>
					<tr>
						<td>
							<table cellpadding="0" cellspacing="0" border="0" align="center">
								<tr>
									<td width="600" valign="top">
									




