<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $question;
$options = $question->get_options();

?>

<input id="llms_answer_question" type="submit" class="button" name="llms_answer_question" value="<?php _e('Next Question', 'lifterlms'); ?>" />
<input type="hidden" name="action" value="llms_answer_question" />
<?php wp_nonce_field( 'llms_answer_question' ); ?>




