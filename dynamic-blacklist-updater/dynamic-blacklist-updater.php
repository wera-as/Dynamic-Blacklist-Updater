<?php
/*
Plugin Name: Dynamic Blacklist Updater
Plugin URI: https://wera.no
Description: Dynamically fetches a blacklist from a remote source and updates both the "Disallowed Comment Keys" and "Comment Moderation" settings in WordPress. It also tracks how many times the blacklist stops comments and shows the last updated time.
Version: 1.1
Author: Wera AS
Author URI: https://wera.no
License: GPL2
*/

// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

//Define the menu icon
define("DBU_MENU_ICON","data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCAyNCAyNCI+CiAgPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI5LjMuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDIuMS4wIEJ1aWxkIDE0NikgIC0tPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuc3QwIHsKICAgICAgICBmaWxsOiAjZmZmOwogICAgICB9CiAgICA8L3N0eWxlPgogIDwvZGVmcz4KICA8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTIsNC45YzEuOCwxLjEsMy41LDEuNiw1LDEuOHY3LjhjMCwxLjYtLjQsMS45LTUsNC44VjQuOVpNMjEsM3YxMS41YzAsNC42LTMuMiw1LjgtOSw5LjUtNS44LTMuNy05LTQuOS05LTkuNVYzYzMuNSwwLDUuNi0uMSw5LTMsMy40LDIuOSw1LjUsMyw5LDNaTTE5LDVjLTIuNC0uMS00LjUtLjYtNy0yLjQtMi41LDEuOC00LjYsMi4zLTcsMi40djkuNmMwLDMsMS43LDMuOCw3LDcuMSw1LjMtMy4zLDctNC4xLDctNy4xVjVaIi8+Cjwvc3ZnPg==");

// Define the default primary and fallback URLs.
if (!defined('DBU_DEFAULT_BLACKLIST_URL')) {
    define('DBU_DEFAULT_BLACKLIST_URL', 'https://raw.githubusercontent.com/splorp/wordpress-comment-blacklist/refs/heads/master/blacklist.txt');
}
if (!defined('DBU_DEFAULT_BLACKLIST_FALLBACK_URL')) {
    define('DBU_DEFAULT_BLACKLIST_FALLBACK_URL', 'https://raw.githubusercontent.com/wera-as/wordpress-comment-blacklist/refs/heads/master/blacklist.txt');
}

// Conditionally include validation partials for form plugins if they're active.
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

/**
 * Add custom cron schedules.
 */
add_filter('cron_schedules', 'dbu_custom_cron_schedules');
function dbu_custom_cron_schedules($schedules)
{
    $schedules['15min'] = array(
        'interval' => 900,
        'display' => __('Every 15 Minutes', 'dynamic-blacklist-updater'),
    );
    $schedules['6hours'] = array(
        'interval' => 21600,
        'display' => __('Every 6 Hours', 'dynamic-blacklist-updater'),
    );
    $schedules['weekly'] = array(
        'interval' => 604800,
        'display' => __('Weekly', 'dynamic-blacklist-updater'),
    );
    return $schedules;
}

/**
 * Fetch the blacklist from the primary URL (with fallback if needed)
 * and update the settings.
 *
 * @return bool True on success, false on failure.
 */
function dbu_fetch_blacklist()
{
    $primary_url = get_option('dbu_blacklist_url', DBU_DEFAULT_BLACKLIST_URL);
    $response = wp_remote_get($primary_url, array('timeout' => 15));
    $body = wp_remote_retrieve_body($response);

    // If primary fails, try the fallback.
    if (is_wp_error($response) || empty($body)) {
        error_log('Dynamic Blacklist Updater: Primary URL failed, trying fallback URL.');
        $fallback_url = get_option('dbu_blacklist_fallback_url', DBU_DEFAULT_BLACKLIST_FALLBACK_URL);
        $response = wp_remote_get($fallback_url, array('timeout' => 15));
        $body = wp_remote_retrieve_body($response);
        if (is_wp_error($response) || empty($body)) {
            error_log('Dynamic Blacklist Updater: Fallback URL failed as well.');
            return false;
        }
    }

    $blacklist_array = array_filter(array_map('trim', explode("\n", $body)));
    if (!empty($blacklist_array)) {
        $blacklist = implode("\n", $blacklist_array);
        update_option('blacklist_keys', $blacklist);
        update_option('moderation_keys', $blacklist);
        update_option('dbu_last_updated', current_time('mysql'));
        // Update the count of blacklist entries.
        update_option('dbu_blacklist_count', count($blacklist_array));
        return true;
    }
    return false;
}

/**
 * Fetch the blacklist using the fallback URL only.
 *
 * @return bool True on success, false on failure.
 */
function dbu_fetch_blacklist_fallback()
{
    $fallback_url = get_option('dbu_blacklist_fallback_url', DBU_DEFAULT_BLACKLIST_FALLBACK_URL);
    $response = wp_remote_get($fallback_url, array('timeout' => 15));
    $body = wp_remote_retrieve_body($response);
    if (is_wp_error($response) || empty($body)) {
        error_log('Dynamic Blacklist Updater: Fallback URL failed.');
        return false;
    }
    $blacklist_array = array_filter(array_map('trim', explode("\n", $body)));
    if (!empty($blacklist_array)) {
        $blacklist = implode("\n", $blacklist_array);
        update_option('blacklist_keys', $blacklist);
        update_option('moderation_keys', $blacklist);
        update_option('dbu_last_updated', current_time('mysql'));
        // Update the count.
        update_option('dbu_blacklist_count', count($blacklist_array));
        return true;
    }
    return false;
}

/**
 * Empty the blacklist by clearing the corresponding options.
 */
function dbu_empty_blacklist()
{
    update_option('blacklist_keys', '');
    update_option('moderation_keys', '');
    update_option('dbu_last_updated', '');
    update_option('dbu_blacklist_count', 0);
}

/**
 * Schedule the blacklist update on plugin activation.
 */
function dbu_activate_plugin()
{
    if (false === get_option('dbu_update_interval')) {
        update_option('dbu_update_interval', 'daily');
    }
    if (false === get_option('dbu_menu_location')) {
        update_option('dbu_menu_location', 'top');
    }
    // Set default URL options if not set.
    if (false === get_option('dbu_blacklist_url')) {
        update_option('dbu_blacklist_url', DBU_DEFAULT_BLACKLIST_URL);
    }
    if (false === get_option('dbu_blacklist_fallback_url')) {
        update_option('dbu_blacklist_fallback_url', DBU_DEFAULT_BLACKLIST_FALLBACK_URL);
    }
    dbu_reschedule_event();
}
register_activation_hook(__FILE__, 'dbu_activate_plugin');

/**
 * Clear the scheduled event on plugin deactivation.
 */
function dbu_deactivate_plugin()
{
    wp_clear_scheduled_hook('dbu_update_blacklist_event');
}
register_deactivation_hook(__FILE__, 'dbu_deactivate_plugin');

/**
 * Reschedule the event using the current update interval.
 */
function dbu_reschedule_event()
{
    wp_clear_scheduled_hook('dbu_update_blacklist_event');
    $interval = get_option('dbu_update_interval', 'daily');
    if (!wp_next_scheduled('dbu_update_blacklist_event')) {
        wp_schedule_event(time(), $interval, 'dbu_update_blacklist_event');
    }
}
add_action('dbu_update_blacklist_event', 'dbu_fetch_blacklist');

/**
 * Count blacklist hits when a comment is processed.
 */
function dbu_count_blacklist_hit($approved, $commentdata)
{
    $blacklist = get_option('blacklist_keys', '');
    if (empty($blacklist)) {
        return $approved;
    }
    $blacklist_array = array_filter(array_map('trim', explode("\n", $blacklist)));
    $haystack = $commentdata['comment_content'] . ' ' . $commentdata['comment_author'] . ' ' . $commentdata['comment_author_email'] . ' ' . $commentdata['comment_author_url'];
    foreach ($blacklist_array as $keyword) {
        if ('' !== $keyword && stripos($haystack, $keyword) !== false) {
            $hits = (int) get_option('dbu_blacklist_hits', 0);
            update_option('dbu_blacklist_hits', ++$hits);
            break;
        }
    }
    return $approved;
}
add_filter('pre_comment_approved', 'dbu_count_blacklist_hit', 10, 2);

/**
 * Register the admin menu.
 * If 'dbu_menu_location' is set to 'top', the settings page appears as a top‑level menu.
 * If set to 'settings', it appears under the Settings menu.
 */
function dbu_register_menu()
{
    $menu_location = get_option('dbu_menu_location', 'top');
    if ($menu_location === 'top') {
        add_menu_page(
            'Dynamic Blacklist Updater',
            'Blacklist Updater',
            'manage_options',
            'dynamic-blacklist-updater',
            'dbu_render_admin_page',
            DBU_MENU_ICON,
            81
        );
    } else {
        add_options_page(
            'Dynamic Blacklist Updater',
            'Dynamic Blacklist Updater',
            'manage_options',
            'dynamic-blacklist-updater',
            'dbu_render_admin_page'
        );
    }
}
add_action('admin_menu', 'dbu_register_menu');

/**
 * Render the plugin's admin page with a modern, Bootstrap-like UI.
 */
function dbu_render_admin_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $message = '';

    // Process settings form submission.
    if (isset($_POST['dbu_settings_update']) && check_admin_referer('dbu_settings_update_action')) {
        $new_interval = sanitize_text_field($_POST['dbu_update_interval']);
        $new_menu_location = sanitize_text_field($_POST['dbu_menu_location']);
        $new_primary_url = esc_url_raw(trim($_POST['dbu_blacklist_url']));
        $new_fallback_url = esc_url_raw(trim($_POST['dbu_blacklist_fallback_url']));
        if (
            in_array($new_interval, array('15min', 'hourly', '6hours', 'daily', 'weekly'), true) &&
            in_array($new_menu_location, array('top', 'settings'), true)
        ) {
            update_option('dbu_update_interval', $new_interval);
            update_option('dbu_menu_location', $new_menu_location);
            update_option('dbu_blacklist_url', $new_primary_url);
            update_option('dbu_blacklist_fallback_url', $new_fallback_url);
            dbu_reschedule_event();
            $message = 'Settings updated and schedule rescheduled. Please refresh the admin menu if needed.';
        } else {
            $message = 'Invalid update interval or menu location selected.';
        }
    }

    // Process Reset URLs action.
    if (isset($_POST['dbu_reset_urls']) && check_admin_referer('dbu_reset_urls_action')) {
        update_option('dbu_blacklist_url', DBU_DEFAULT_BLACKLIST_URL);
        update_option('dbu_blacklist_fallback_url', DBU_DEFAULT_BLACKLIST_FALLBACK_URL);
        $message = 'Blacklist URLs have been reset to default.';
    }

    // Handle manual update (primary URL).
    if (isset($_POST['dbu_update']) && check_admin_referer('dbu_manual_update')) {
        $updated = dbu_fetch_blacklist();
        $message = $updated ? 'Blacklist updated successfully.' : 'Failed to update blacklist. Please check your error log.';
    }

    // Handle fallback update.
    if (isset($_POST['dbu_update_fallback']) && check_admin_referer('dbu_manual_fallback_update')) {
        $updated = dbu_fetch_blacklist_fallback();
        $message = $updated ? 'Blacklist updated successfully using fallback URL.' : 'Failed to update blacklist using fallback URL.';
    }

    // Handle empty blacklist.
    if (isset($_POST['dbu_empty_blacklist']) && check_admin_referer('dbu_empty_blacklist_action')) {
        dbu_empty_blacklist();
        $message = 'Blacklist has been emptied.';
    }

    // Retrieve current data.
    $current_blacklist = get_option('blacklist_keys', '');
    $current_interval = get_option('dbu_update_interval', 'daily');
    $last_updated = get_option('dbu_last_updated');
    $last_updated_display = $last_updated ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_updated)) : 'Never';
    $hit_count = get_option('dbu_blacklist_hits', 0);
    $blacklist_count = get_option('dbu_blacklist_count', 0);

    $interval_options = array(
        '15min' => 'Every 15 Minutes',
        'hourly' => 'Hourly',
        '6hours' => 'Every 6 Hours',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
    );

    // Check if additional form plugins are active.
    $wpforms_active = class_exists('WPForms') ? true : false;
    $formidable_active = class_exists('FrmAppController') ? true : false;
    $cf7_active = defined('WPCF7_VERSION') ? true : false;
    $gravity_active = class_exists('GFForms') ? true : false;

    // Check if Disable Comments plugin is active.
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $disable_comments_active = is_plugin_active('disable-comments/disable-comments.php') ? 'Yes' : 'No';
    ?>
    <div class="wrap dbu-container">
        <h1 class="dbu-header">Dynamic Blacklist Updater <span style="font-size:1.5rem;">v1.1</span></h1>
        <?php if (!empty($message)): ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <style>
            /* Bootstrap-inspired, clean admin UI */
            .dbu-container {
                max-width: 900px;
                margin: 0 auto;
                padding: 2rem;
                background-color: #f8f9fa;
                border-radius: 0.25rem;
            }

            .dbu-header {
                font-size: 2.5rem;
                color: #343a40;
                text-align: center;
                margin-bottom: 1rem !important;
                padding-bottom: 0.5rem;
                border-bottom: 3px solid #007bff;
            }

            .dbu-card {
                background-color: #ffffff;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }

            .dbu-card h2 {
                font-size: 1.5rem;
                color: #343a40;
                margin-top: 0;
                border-bottom: 1px solid #dee2e6;
                padding-bottom: 0.5rem;
                margin-bottom: 1rem;
            }

            .dbu-form-group {
                margin-bottom: 1rem;
            }

            .dbu-label {
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: #495057;
            }

            .dbu-description {
                font-size: 0.875rem;
                color: #6c757d;
            }

            .dbu-inline-form {
                display: inline-block;
                margin-right: 1rem;
            }

            /* Use WP default buttons (they already adjust to the admin color scheme) */
            /* Plugin Status Grid Layout */
            .dbu-status-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 1rem;
                margin-top: 1rem;
            }

            .dbu-status-item {
                background-color: #ffffff;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                padding: 1rem;
                text-align: center;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }

            .dbu-status-item p {
                margin-bottom: 0.5rem;
                font-weight: 600;
                color: #343a40;
            }

            .dbu-status-button {
                font-size: 1rem;
                padding: 0.5rem 0.75rem;
                border: none;
                border-radius: 0.25rem;
                color: #ffffff;
                cursor: default;
            }

            .dbu-blacklist-textarea {
                width: 100%;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                padding: 0.75rem;
                background-color: #ffffff;
                box-shadow: inset 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                font-size: 1rem;
                color: #343a40;
                resize: vertical;
                transition: background-color 0.3s ease;
                user-select: none;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
            }

            .dbu-blacklist-textarea:focus {
                background-color: #e9ecef;
            }
        </style>

        <!-- Manual Blacklist Update Card -->
        <div class="dbu-card">
            <h2>Manual Blacklist Update</h2>
            <p>Fetch the latest blacklist or empty the current blacklist.</p>
            <div class="dbu-inline-form">
                <?php wp_nonce_field('dbu_manual_update'); ?>
                <input type="submit" name="dbu_update" class="button button-primary" value="Update Blacklist Now">
            </div>
            <div class="dbu-inline-form">
                <?php wp_nonce_field('dbu_manual_fallback_update'); ?>
                <input type="submit" name="dbu_update_fallback" class="button" value="Update Using Fallback URL">
            </div>
            <div class="dbu-inline-form">
                <?php wp_nonce_field('dbu_empty_blacklist_action'); ?>
                <input type="submit" name="dbu_empty_blacklist" class="button button-secondary" value="Empty Blacklist">
            </div>
        </div>

        <!-- Settings Card -->
        <div class="dbu-card">
            <h2>Settings</h2>
            <form method="post" action="">
                <?php wp_nonce_field('dbu_settings_update_action'); ?>
                <div class="dbu-form-group">
                    <label class="dbu-label" for="dbu_update_interval">Update Interval</label>
                    <select name="dbu_update_interval" id="dbu_update_interval">
                        <?php foreach ($interval_options as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_interval, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="dbu-description">Choose how often the blacklist is updated.</p>
                </div>
                <div class="dbu-form-group">
                    <label class="dbu-label" for="dbu_menu_location">Menu Location</label>
                    <select name="dbu_menu_location" id="dbu_menu_location">
                        <option value="top" <?php selected(get_option('dbu_menu_location', 'top'), 'top'); ?>>Main Menu
                        </option>
                        <option value="settings" <?php selected(get_option('dbu_menu_location', 'top'), 'settings'); ?>>
                            Settings Menu</option>
                    </select>
                    <p class="dbu-description">Select where the plugin settings page should appear in the admin menu.</p>
                </div>
                <div class="dbu-form-group">
                    <label class="dbu-label" for="dbu_blacklist_url">Primary Blacklist URL</label>
                    <input type="text" name="dbu_blacklist_url" id="dbu_blacklist_url"
                        value="<?php echo esc_attr(get_option('dbu_blacklist_url', DBU_DEFAULT_BLACKLIST_URL)); ?>"
                        style="width:100%;">
                    <p class="dbu-description">Enter the URL for the primary blacklist source.</p>
                </div>
                <div class="dbu-form-group">
                    <label class="dbu-label" for="dbu_blacklist_fallback_url">Fallback Blacklist URL</label>
                    <input type="text" name="dbu_blacklist_fallback_url" id="dbu_blacklist_fallback_url"
                        value="<?php echo esc_attr(get_option('dbu_blacklist_fallback_url', DBU_DEFAULT_BLACKLIST_FALLBACK_URL)); ?>"
                        style="width:100%;">
                    <p class="dbu-description">Enter the URL for the fallback blacklist source.</p>
                </div>
                <?php submit_button('Save Settings', 'primary', 'dbu_settings_update'); ?>
            </form>
            <form method="post" action="" style="margin-top:1rem;">
                <?php wp_nonce_field('dbu_reset_urls_action'); ?>
                <input type="submit" name="dbu_reset_urls" class="button button-secondary"
                    value="Reset Blacklist URLs to Default">
            </form>
        </div>

        <!-- Blacklist Information Card -->
        <div class="dbu-card">
            <h2>Blacklist Information</h2>
            <p><strong>Last Updated:</strong> <?php echo esc_html($last_updated_display); ?></p>
            <p><strong>Blacklist Hits:</strong> <?php echo esc_html($hit_count); ?></p>
        </div>

        <!-- Current Blacklist Card -->
        <div class="dbu-card">
            <h2>Current Blacklist</h2>
            <textarea class="dbu-blacklist-textarea" rows="10"
                readonly><?php echo esc_textarea($current_blacklist); ?></textarea>
            <p><strong>Terms in Blacklist:</strong> <?php echo esc_html($blacklist_count); ?></p>
        </div>

        <!-- Plugin Status Card -->
        <div class="dbu-card">
            <h2>Plugin Status</h2>
            <div class="dbu-status-grid">
                <div class="dbu-status-item">
                    <p>Disable Comments</p>
                    <button class="dbu-status-button"
                        style="background-color: <?php echo ($disable_comments_active === 'Yes') ? '#46b450' : '#dc3232'; ?>;"
                        disabled>
                        <?php echo esc_html($disable_comments_active); ?>
                    </button>
                </div>
                <div class="dbu-status-item">
                    <p>WPForms</p>
                    <button class="dbu-status-button"
                        style="background-color: <?php echo ($wpforms_active) ? '#46b450' : '#dc3232'; ?>;" disabled>
                        <?php echo $wpforms_active ? 'Yes' : 'No'; ?>
                    </button>
                </div>
                <div class="dbu-status-item">
                    <p>Formidable Forms</p>
                    <button class="dbu-status-button"
                        style="background-color: <?php echo ($formidable_active) ? '#46b450' : '#dc3232'; ?>;" disabled>
                        <?php echo $formidable_active ? 'Yes' : 'No'; ?>
                    </button>
                </div>
                <div class="dbu-status-item">
                    <p>Contact Form 7</p>
                    <button class="dbu-status-button"
                        style="background-color: <?php echo ($cf7_active) ? '#46b450' : '#dc3232'; ?>;" disabled>
                        <?php echo $cf7_active ? 'Yes' : 'No'; ?>
                    </button>
                </div>
                <div class="dbu-status-item">
                    <p>Gravity Forms</p>
                    <button class="dbu-status-button"
                        style="background-color: <?php echo ($gravity_active) ? '#46b450' : '#dc3232'; ?>;" disabled>
                        <?php echo $gravity_active ? 'Yes' : 'No'; ?>
                    </button>
                </div>
            </div>
        </div>

    </div>
    <?php
}

/**
 * Lock the Discussion settings fields for disallowed_keys and moderation_keys.
 * Adds an overlay indicating that these fields are managed by this plugin.
 */
function dbu_lock_discussion_fields()
{
    $screen = get_current_screen();
    if (isset($screen->id) && 'options-discussion' === $screen->id) {
        ?>
        <style>
            #disallowed_keys,
            #moderation_keys {
                background: #f9f9f9;
                pointer-events: none;
                opacity: 0.6;
            }

            .dbu-overlay-container {
                position: relative;
                display: inline-block;
                width: 100%;
            }

            .dbu-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.7);
                color: #555;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                pointer-events: none;
            }
        </style>
        <script>
            jQuery(document).ready(function ($) {
                $('#disallowed_keys, #moderation_keys').attr('readonly', 'readonly').each(function () {
                    var $this = $(this);
                    if (!$this.parent().hasClass('dbu-overlay-container')) {
                        $this.wrap('<div class="dbu-overlay-container"></div>');
                        $this.after('<div class="dbu-overlay">This field is managed by Dynamic Blacklist Updater plugin</div>');
                    }
                });
            });
        </script>
        <?php
    }
}
add_action('admin_head', 'dbu_lock_discussion_fields');

/**
 * Add a Settings link to the plugin action links on the plugins page.
 */
function dbu_plugin_action_links($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=dynamic-blacklist-updater') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dbu_plugin_action_links');
