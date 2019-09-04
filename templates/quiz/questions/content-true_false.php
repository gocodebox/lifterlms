<?php
/**
 * True / False question template
 *
 * @package LifterLMS/Templates
 *
 * @since    3.16.0
 * @version  3.16.0
 *
 * @arg  $attempt  (obj)  LLMS_Quiz_Attempt instance
 * @arg  $question (obj)  LLMS_Question instance
 */

defined( 'ABSPATH' ) || exit;

llms_get_template( 'quiz/questions/content-choice.php', $args );
