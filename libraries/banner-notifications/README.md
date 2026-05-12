Banner Notifications
================

[![Test PHPUnit](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/test-phpunit.yml/badge.svg)](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/test-phpunit.yml)
[![PHP Code Coverage Report](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/php-test-coverage.yml/badge.svg)](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/php-test-coverage.yml)
[![Coding Standards](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/gocodebox/lifterlms-blocks/actions/workflows/coding-standards.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/49df50fa2a04ab1f8e55/maintainability)](https://codeclimate.com/github/gocodebox/lifterlms-blocks/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/49df50fa2a04ab1f8e55/test_coverage)](https://codeclimate.com/github/gocodebox/lifterlms-blocks/test_coverage)

WordPress Editor (Gutenberg) blocks for LifterLMS.

---

## Installing
TBW

- Adding notification area in admin header

```php
    <?php
    // Default to showing notification banners.
    $show_notifications = true;

    // Hide notifications on certain pages.
    $hide_on_these_pages = array( 'pmpro-updates' );
    if ( ! empty( $_REQUEST['page'] ) && in_array( sanitize_text_field( $_REQUEST['page'] ), $hide_on_these_pages ) ) {
        $show_notifications = false;
    }

    // Hide notifications if the user has disabled them.
    if( pmpro_get_max_notification_priority() < 1 ) {
        $show_notifications = false;
    }

    if( $show_notifications ) :
    ?>
    <div id="pmpro_notifications">
    </div>
    <?php
        // To debug a specific notification.
        if ( !empty( $_REQUEST['pmpro_notification'] ) ) {
            $specific_notification = '&pmpro_notification=' . intval( $_REQUEST['pmpro_notification'] );
        } else {
            $specific_notification = '';
        }
    ?>
    <script>
        jQuery(document).ready(function() {
            jQuery.get('<?php echo esc_url_raw( admin_url( "admin-ajax.php?action=pmpro_notifications" . $specific_notification ) ); ?>', function(data) {
                if(data && data != 'NULL')
                    jQuery('#pmpro_notifications').html(data);
            });
        });
    </script>
    <?php endif; ?>

```

- Loading an instance of the class with unique slug (ie. for the )

```
$GLOBALS['pmpro_banner_notifications'] = new Gocodebox_Banner_Notifier( array( 
    'prefix' => 'pmpro',
    'version' => PMPRO_VERSION, 
    'notifications_url' => 'https://notifications.paidmembershipspro.com/v2/notifications.json'
) );
```

## Development

TBW