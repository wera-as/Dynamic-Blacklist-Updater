<?php
/*
Plugin Name: Dynamic Blacklist Updater
Plugin URI: https://wera.no
Description: Dynamically fetches a blacklist and updates WordPress settings.
Version: 1.1.5
Author: Wera AS
Author URI: https://wera.no
License: GPL2
*/

// Plugin header must remain at the top for WordPress to recognize the plugin

namespace DynamicBlacklistUpdater;

// Prevent direct access.
if (!defined('ABSPATH')) exit;

// Load branding.
require_once plugin_dir_path(__FILE__) . 'admin/icons.php';

// dynamic-blacklist-updater.php (your main plugin file)
define('DBU_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DBU_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Define constants.
if (!defined('DBU_DEFAULT_BLACKLIST_URL')) {
    define('DBU_DEFAULT_BLACKLIST_URL', 'https://raw.githubusercontent.com/splorp/wordpress-comment-blacklist/refs/heads/master/blacklist.txt');
}
if (!defined('DBU_DEFAULT_BLACKLIST_FALLBACK_URL')) {
    define('DBU_DEFAULT_BLACKLIST_FALLBACK_URL', 'https://raw.githubusercontent.com/wera-as/wordpress-comment-blacklist/refs/heads/master/blacklist.txt');
}

// Include necessary files.
require_once plugin_dir_path(__FILE__) . 'functions/updater.php';
require_once plugin_dir_path(__FILE__) . 'functions/activation.php';
require_once plugin_dir_path(__FILE__) . 'cron/schedules.php';
require_once plugin_dir_path(__FILE__) . 'cron/tasks.php';
require_once plugin_dir_path(__FILE__) . 'admin/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'admin/enqueue.php';

// Conditionally include partial validation files.
if (class_exists('WPForms')) {
    require_once plugin_dir_path(__FILE__) . 'partials/wpforms-validation.php';
}
if (class_exists('FrmAppController')) {
    require_once plugin_dir_path(__FILE__) . 'partials/formidable-validation.php';
}
if (defined('WPCF7_VERSION')) {
    require_once plugin_dir_path(__FILE__) . 'partials/cf7-validation.php';
}
if (class_exists('GFForms')) {
    require_once plugin_dir_path(__FILE__) . 'partials/gravityforms-validation.php';
}

add_action('admin_footer', function () {
    $screen = get_current_screen();
    if (isset($screen->id) && in_array($screen->id, [
        'toplevel_page_dynamic-blacklist-updater',
        'settings_page_dynamic-blacklist-updater'
    ], true)) {
?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#wpfooter').remove();
                $('#wpbody-content').css('padding-bottom', '0');
            });
        </script>
<?php
    }
});
