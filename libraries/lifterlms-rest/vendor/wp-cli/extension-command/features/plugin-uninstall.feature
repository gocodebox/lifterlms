Feature: Uninstall a WordPress plugin

  Background:
    Given a WP install

  Scenario: Uninstall an installed plugin
    When I run `wp plugin uninstall akismet`
    Then STDOUT should be:
      """
      Uninstalled and deleted 'akismet' plugin.
      Success: Uninstalled 1 of 1 plugins.
      """
    And the return code should be 0

  Scenario: Attempting to uninstall a plugin that's activated
    When I run `wp plugin activate akismet`
    Then STDOUT should not be empty

    When I try `wp plugin uninstall akismet`
    Then STDERR should be:
      """
      Warning: The 'akismet' plugin is active.
      Error: No plugins uninstalled.
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: Attempting to uninstall a plugin that doesn't exist
    When I try `wp plugin uninstall edit-flow`
    Then STDERR should be:
      """
      Warning: The 'edit-flow' plugin could not be found.
      Error: No plugins uninstalled.
      """
    And the return code should be 1

  Scenario: Uninstall all installed plugins
    When I run `wp plugin uninstall --all`
    Then STDOUT should be:
      """
      Uninstalled and deleted 'akismet' plugin.
      Uninstalled and deleted 'hello' plugin.
      Success: Uninstalled 2 of 2 plugins.
      """
    And the return code should be 0

    When I run the previous command again
    Then STDOUT should be:
      """
      Success: No plugins uninstalled.
      """

  Scenario:  Uninstall all installed plugins when one or more activated
    When I run `wp plugin activate --all`
    Then STDOUT should contain:
      """
      Success: Activated 2 of 2 plugins.
      """

    When I try `wp plugin uninstall --all`
    Then STDERR should be:
      """
      Warning: The 'akismet' plugin is active.
      Warning: The 'hello' plugin is active.
      Error: No plugins uninstalled.
      """
    And the return code should be 1

    When I run `wp plugin uninstall --deactivate --all`
    Then STDOUT should contain:
      """
      Success: Uninstalled 2 of 2 plugins.
      """

  Scenario: Excluding a plugin from uninstallation when using --all switch
    When I try `wp plugin uninstall --all --exclude=akismet,hello`
    Then STDOUT should be:
      """
      Success: No plugins uninstalled.
      """
    And the return code should be 0



  Scenario: Excluding a missing plugin should not throw an error
    Given a WP install
    And I run `wp plugin uninstall --all --exclude=missing-plugin`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      Success:
      """
    And the return code should be 0

  @require-wp-5.2
  Scenario: Uninstalling a plugin should remove its language pack too
    Given a WP install
    And I run `wp plugin install wordpress-importer`
    And I run `wp core language install fr_FR`
    And I run `wp site switch-language fr_FR`

    When I run `wp language plugin install wordpress-importer fr_FR`
    Then STDOUT should contain:
      """
      Success:
      """
    And the wp-content/languages/plugins/wordpress-importer-fr_FR.mo file should exist
    And the wp-content/languages/plugins/wordpress-importer-fr_FR.po file should exist

    When I run `wp plugin uninstall wordpress-importer`
    Then STDOUT should contain:
      """
      Success:
      """
    And the wp-content/languages/plugins/wordpress-importer-fr_FR.mo file should not exist
    And the wp-content/languages/plugins/wordpress-importer-fr_FR.po file should not exist
