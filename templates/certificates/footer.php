<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<table cellpadding="0" cellspacing="0" border="0" align="center">
								<tr>
									<td width="600" valign="top">
										<?php echo wpautop( wp_kses_post( wptexturize( apply_filters( 'lifterlms_email_footer_text', get_option( 'lifterlms_email_footer_text' ) ) ) ) ); ?>
									</td>
								</tr>
							</table>
						</tr>
					</td>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
