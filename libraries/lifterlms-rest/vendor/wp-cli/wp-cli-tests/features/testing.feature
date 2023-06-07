Feature: Test that WP-CLI loads.

  Scenario: WP-CLI loads for your tests
    Given a WP install

    When I run `wp eval 'echo "Hello world.";'`
    Then STDOUT should contain:
      """
      Hello world.
      """

  Scenario: WP Cron is disabled by default
    Given a WP install
    And a test_cron.php file:
    """
    <?php
    $cron_disabled = defined( "DISABLE_WP_CRON" ) ? DISABLE_WP_CRON : false;
    echo 'DISABLE_WP_CRON is: ' . ( $cron_disabled ? 'true' : 'false' );
    """

    When I run `wp eval-file test_cron.php`
    Then STDOUT should be:
      """
      DISABLE_WP_CRON is: true
      """
