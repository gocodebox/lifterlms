<?php
/**
 * LifterLMS Emails Footer Template
 *
 * @since    1.0.0
 * @version  3.16.15
 */

defined( 'ABSPATH' ) || exit;

$mailer = LLMS()->mailer();

$terms = false;
if ( 'yes' === get_option( 'lifterlms_registration_require_agree_to_terms', 'no' ) ) {
	$terms = get_option( 'lifterlms_terms_page_id', false );
}
?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<!-- END MAIN CONTENT AREA -->
			</table>
			<!-- START FOOTER -->
			<div class="footer" style="clear:both;padding-top:10px;text-align:center;width:100%;">
				<table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%;">
					<tr>
						<td class="content-block" style="font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:<?php $mailer->get_css( 'font-size-small' ); ?>;vertical-align:top;color:#999999;text-align:center;">
							<?php echo wpautop( wp_kses_post( wptexturize( apply_filters( 'lifterlms_email_footer_text', get_option( 'lifterlms_email_footer_text' ) ) ) ) ); ?>
						</td>
					</tr>
					<tr>
						<td class="content-block powered-by" style="font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:<?php $mailer->get_css( 'font-size-small' ); ?>;vertical-align:top;color:#999999;text-align:center;">
							<a href="<?php echo esc_url( get_bloginfo( 'url', 'display' ) ); ?>" style="text-decoration:underline;color:<?php $mailer->get_css( 'main-color' ); ?>;"><?php echo get_bloginfo( 'name', 'display' ); ?></a>
							<?php if ( $terms ) : ?>
								| <a alt="<?php echo get_the_title( $terms ); ?>" href="<?php echo get_permalink( $terms ); ?>" style="text-decoration:underline;color:<?php $mailer->get_css( 'main-color' ); ?>;"><?php echo get_the_title( $terms ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
			<!-- END FOOTER -->
		</div>
		<!-- END CENTERED WHITE CONTAINER -->
	</td>
	<td style="font-family:<?php $mailer->get_css( 'font-family' ); ?>;font-size:<?php $mailer->get_css( 'font-size-small' ); ?>;vertical-align:top;">&nbsp;</td>
</tr>
</table>
</body>
</html>
