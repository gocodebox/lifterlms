<?php
/**
 * True / False question template
 * @since    [version]
 * @version  [version]
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 * @arg  $question (obj)  LLMS_Question instance
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_get_template( 'quiz/questions/content-choice.php', $args );
