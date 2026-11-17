<?php
/**
 * Course sub-resource commands file.
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.6
 * @version 0.0.6
 */

namespace LifterLMS\CLI\Commands\Course;

use LifterLMS\CLI\Commands\AbstractCommand;

/**
 * Additional course commands for sub-resource endpoints.
 *
 * These commands supplement the auto-generated CRUD commands
 * by adding access to sub-resource REST API routes that the
 * Restful bridge does not discover automatically.
 *
 * @since 0.0.6
 */
class Main extends AbstractCommand {

	use Content, Enrollments;

}
