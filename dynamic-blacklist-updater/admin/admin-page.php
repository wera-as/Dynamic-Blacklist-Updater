<?php

namespace DynamicBlacklistUpdater\Admin;

/**
 * Render the plugin's admin page with a modern, Bootstrap-like UI.
 */
function dbu_render_admin_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $message = '';

    // Process settings form submission.
    if (isset($_POST['dbu_settings_update']) && check_admin_referer('dbu_settings_update_action')) {
        $new_interval      = sanitize_text_field($_POST['dbu_update_interval']);
        $new_menu_location = sanitize_text_field($_POST['dbu_menu_location']);
        $new_primary_url   = esc_url_raw(trim($_POST['dbu_blacklist_url']));
        $new_fallback_url  = esc_url_raw(trim($_POST['dbu_blacklist_fallback_url']));
        if (
            in_array(
                $new_interval,
                [
                    '15min',
                    '30min',
                    'hourly',
                    '2hours',
                    '4hours',
                    '6hours',
                    '12hours',
                    'daily',
                    'weekly',
                    'biweekly',
                    'monthly'
                ],
                true
            ) &&
            in_array($new_menu_location, ['top', 'settings'], true)
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

    // Process manual update actions.
    if (isset($_POST['_wpnonce_manual_action']) && check_admin_referer('dbu_manual_action', '_wpnonce_manual_action')) {
        if (isset($_POST['dbu_update_blacklist'])) {
            $updated = dbu_fetch_blacklist();
            $message = $updated ? 'Blacklist updated successfully.' : 'Failed to update blacklist. Please check your error log.';
        } elseif (isset($_POST['dbu_update_fallback'])) {
            $updated = dbu_fetch_blacklist_fallback();
            $message = $updated ? 'Blacklist updated successfully using fallback URL.' : 'Failed to update blacklist using fallback URL.';
        } elseif (isset($_POST['dbu_empty_blacklist'])) {
            dbu_empty_blacklist();
            $message = 'Blacklist has been emptied.';
        } else {
            $message = 'Unknown action.';
        }
    }

    // Process User Defined Block Terms update.
    if (isset($_POST['dbu_user_defined_update'])) {
        check_admin_referer('dbu_user_defined_update_action', 'dbu_user_defined_update_nonce');
        $user_defined_input = sanitize_text_field($_POST['dbu_user_defined_terms']);
        $terms_array        = array_filter(array_map('trim', explode(',', $user_defined_input)));
        $terms_array        = array_unique($terms_array);
        update_option('dbu_user_defined_blacklist', $terms_array);
        $message = 'User defined terms updated.';
    }

    // Process Clear User Defined Block Terms action.
    if (isset($_POST['dbu_clear_user_defined'])) {
        check_admin_referer('dbu_clear_user_defined_action', 'dbu_clear_user_defined_nonce');
        update_option('dbu_user_defined_blacklist', array());
        $message = 'User defined terms cleared.';
    }

    // Retrieve current data.
    $current_blacklist    = get_option('blacklist_keys', '');
    $blacklist_count      = get_option('dbu_blacklist_count', 0);
    $current_interval     = get_option('dbu_update_interval', 'daily');
    $last_updated         = get_option('dbu_last_updated');
    $last_updated_display = $last_updated ? date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_updated)) : 'Never';
    $hit_count            = get_option('dbu_blacklist_hits', 0);

    $next_update = wp_next_scheduled('dbu_update_blacklist_event');
    if ($next_update) {
        $next_update_display = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_update);
    } else {
        $next_update_display = 'Not scheduled';
    }

    $interval_options = [
        '15min'    => 'Every 15 Minutes',
        '30min'    => 'Every 30 Minutes',
        'hourly'   => 'Hourly',
        '2hours'   => 'Every 2 Hours',
        '4hours'   => 'Every 4 Hours',
        '6hours'   => 'Every 6 Hours',
        '12hours'  => 'Every 12 Hours',
        'daily'    => 'Daily',
        'weekly'   => 'Weekly',
        'biweekly' => 'Every 2 Weeks',
        'monthly'  => 'Monthly',
    ];

    // Retrieve user defined blacklist.
    $user_defined_blacklist = get_option('dbu_user_defined_blacklist', array());
    $user_defined_terms     = is_array($user_defined_blacklist) ? implode(', ', $user_defined_blacklist) : '';
    $user_defined_count     = is_array($user_defined_blacklist) ? count($user_defined_blacklist) : 0;
    $total_entries          = $blacklist_count + $user_defined_count;

    // Check for additional form plugins.
    $wpforms_active    = class_exists('WPForms') ? true : false;
    $formidable_active = class_exists('FrmAppController') ? true : false;
    $cf7_active        = defined('WPCF7_VERSION') ? true : false;
    $gravity_active    = class_exists('GFForms') ? true : false;

    // Check if Disable Comments plugin is active.
    if (! function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    if (! function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $disable_comments_active = is_plugin_active('disable-comments/disable-comments.php') ? 'Installed' : 'Not Installed';

    $plugin_data    = get_plugin_data(DBU_PLUGIN_DIR . 'dynamic-blacklist-updater.php');
    $plugin_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '1.0';
?>
    <div class="wrap dbu-container">
        <div class="dbu-top-bar">
            <p>
                <span class="dbu-text-logo">Dynamic Blacklist Updater</span>
                <span class="dbu-version">v<?php echo esc_html($plugin_version); ?></span><br>
                <span id="server-time" data-server-timestamp="<?php echo time(); ?>">
                    <?php echo date('j. F, Y \--:--:--'); ?>
                </span>
            </p>
        </div>
        <?php if (! empty($message)) : ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Manual Blacklist Update Card -->
        <div class="dbu-card">
            <h2>Blacklist Actions</h2>
            <p>Fetch the latest blacklist or empty the current blacklist.</p>
            <form method="post" class="actions_form" action="<?php echo esc_url(admin_url('admin.php?page=dynamic-blacklist-updater')); ?>">
                <?php wp_nonce_field('dbu_manual_action', '_wpnonce_manual_action'); ?>
                <div class="dbu-form-group-action">
                    <input type="submit" name="dbu_update_blacklist" class="button button-primary" value="Update Blacklist Now">
                </div>
                <div class="dbu-form-group-action">
                    <input type="submit" name="dbu_update_fallback" class="button" value="Update Using Fallback URL">
                </div>
                <div class="dbu-form-group-action">
                    <input type="submit" name="dbu_empty_blacklist" class="button button-secondary" value="Empty Blacklist">
                </div>
            </form>
        </div>

        <!-- Settings Card -->
        <div class="dbu-card">
            <h2>Settings</h2>
            <form method="post" action="" style="margin-bottom:0rem;">
                <?php wp_nonce_field('dbu_settings_update_action'); ?>
                <div class="dbu-form-group">
                    <label class="dbu-label" for="dbu_update_interval">Update Interval</label>
                    <select name="dbu_update_interval" id="dbu_update_interval">
                        <?php foreach ($interval_options as $key => $label) : ?>
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
                        <option value="top" <?php selected(get_option('dbu_menu_location', 'top'), 'top'); ?>>Main Menu</option>
                        <option value="settings" <?php selected(get_option('dbu_menu_location', 'top'), 'settings'); ?>>Settings Menu</option>
                    </select>
                    <p class="dbu-description">Select where the plugin settings page should appear in the admin menu.</p>
                </div>
                <div class="dbu-form-group">
                    <label class="dbu-label" for="dbu_blacklist_url">Primary Blacklist URL</label>
                    <div class="dbu-input-with-lock">
                        <input type="text" name="dbu_blacklist_url" id="dbu_blacklist_url" value="<?php echo esc_attr(get_option('dbu_blacklist_url', DBU_DEFAULT_BLACKLIST_URL)); ?>" readonly="readonly">
                        <!-- Default icon is an open padlock (indicating field is locked) -->
                        <span class="dbu-lock-icon" data-target="#dbu_blacklist_url">🔓</span>
                    </div>
                    <p class="dbu-description">Enter the URL for the primary blacklist source.</p>
                </div>
                <div class="dbu-form-group">
                    <label class="dbu-label" for="dbu_blacklist_fallback_url">Fallback Blacklist URL</label>
                    <div class="dbu-input-with-lock">
                        <input type="text" name="dbu_blacklist_fallback_url" id="dbu_blacklist_fallback_url" value="<?php echo esc_attr(get_option('dbu_blacklist_fallback_url', DBU_DEFAULT_BLACKLIST_FALLBACK_URL)); ?>" readonly="readonly">
                        <span class="dbu-lock-icon" data-target="#dbu_blacklist_fallback_url">🔓</span>
                    </div>
                    <p class="dbu-description">Enter the URL for the fallback blacklist source.</p>
                </div>
                <?php submit_button('Save Settings', 'primary', 'dbu_settings_update'); ?>
            </form>
            <form method="post" action="" style="margin-top:0rem;">
                <?php wp_nonce_field('dbu_reset_urls_action'); ?>
                <input type="submit" name="dbu_reset_urls" class="button button-secondary" value="Reset Blacklist URLs to Default">
            </form>
        </div>

        <!-- Blacklist Information Card -->
        <div class="dbu-card">
            <h2>Blacklist Information</h2>
            <p><strong>Last Updated:</strong> <?php echo esc_html($last_updated_display); ?></p>
            <p><strong>Next Update:</strong> <?php echo esc_html($next_update_display); ?></p>
            <p><strong>Blacklist Hits:</strong> <?php echo esc_html($hit_count); ?></p>
        </div>

        <!-- Current Blacklist Card -->
        <div class="dbu-card">
            <h2>Current Blacklist<span class="dbu-entries-count">&emsp;<?php echo esc_html($total_entries); ?> entries and <?php echo esc_html($user_defined_count); ?> user defined entries</span></h2>
            <form method="post" action="">
                <?php
                // Create separate nonce fields with unique names.
                wp_nonce_field('dbu_user_defined_update_action', 'dbu_user_defined_update_nonce');
                wp_nonce_field('dbu_clear_user_defined_action', 'dbu_clear_user_defined_nonce');
                ?>
                <div class="dbu-form-group">
                    <label class="dbu-label" for="dbu_user_defined_terms">Enter custom terms (comma separated):</label>
                    <input type="text" name="dbu_user_defined_terms" id="dbu_user_defined_terms" value="<?php echo esc_attr($user_defined_terms); ?>">
                    <p class="dbu-description">These custom terms will be merged with the system blacklist.</p>
                </div>
                <div class="dbu-user-defined-buttons">
                    <input type="submit" name="dbu_user_defined_update" class="button button-primary" value="Save User Defined Terms">
                    <input type="submit" name="dbu_clear_user_defined" class="button button-secondary" value="Clear User Defined Terms">
                </div>
            </form>
            <div class="dbu-warning-box">
                <p><strong>⚠ Warning:</strong> The blacklist may contain extensive and offensive entries, including content related to:</p>
                <ul>
                    <li>Race and discrimination</li>
                    <li>Violence and hate speech</li>
                    <li>Profanity and explicit language</li>
                    <li>Sexual content and adult themes</li>
                    <li>Other disturbing or sensitive topics</li>
                </ul>
                <p><strong>Viewer discretion is advised.</strong></p>
            </div>
            <label class="dbu-checkbox-label">
                <input type="checkbox" id="show-blacklist"> I acknowledge the warning and want to view the blacklist
            </label>
            <textarea class="dbu-blacklist-textarea" id="blacklist-textarea" rows="10" readonly style="display: none;"><?php echo esc_textarea($current_blacklist); ?></textarea>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const checkbox = document.getElementById("show-blacklist");
                const textarea = document.getElementById("blacklist-textarea");

                checkbox.addEventListener("change", function() {
                    textarea.style.display = this.checked ? "block" : "none";
                });
            });
        </script>

        <!-- Plugin Status Card -->
        <div class="dbu-card">
            <h2>Supported Plugin Status</h2>
            <div class="dbu-status-grid">
                <!-- Disable Comments -->
                <div class="dbu-status-item">
                    <div class="plugin-info">
                        <img src="https://ps.w.org/disable-comments/assets/icon-128x128.png" alt="Disable Comments Icon" class="dbu-plugin-icon" />
                        <p>Disable Comments</p>
                    </div>
                    <button class="dbu-status-button" style="background-color: <?php echo ($disable_comments_active === 'Installed') ? '#2271b1' : 'lightgrey'; ?>;" disabled>
                        <?php echo esc_html($disable_comments_active); ?>
                    </button>
                </div>

                <!-- WPForms -->
                <div class="dbu-status-item">
                    <div class="plugin-info">
                        <img src="https://ps.w.org/wpforms-lite/assets/icon-128x128.png" alt="WPForms Icon" class="dbu-plugin-icon" />
                        <p>WPForms</p>
                    </div>
                    <button class="dbu-status-button" style="background-color: <?php echo ($wpforms_active) ? '#2271b1' : 'lightgrey'; ?>;" disabled>
                        <?php echo $wpforms_active ? 'Installed' : 'Not Installed'; ?>
                    </button>
                </div>

                <!-- Formidable Forms -->
                <div class="dbu-status-item">
                    <div class="plugin-info">
                        <img src="https://ps.w.org/formidable/assets/icon-128x128.png" alt="Formidable Forms Icon" class="dbu-plugin-icon" />
                        <p>Formidable Forms</p>
                    </div>
                    <button class="dbu-status-button" style="background-color: <?php echo ($formidable_active) ? '#2271b1' : 'lightgrey'; ?>;" disabled>
                        <?php echo $formidable_active ? 'Installed' : 'Not Installed'; ?>
                    </button>
                </div>

                <!-- Contact Form 7 -->
                <div class="dbu-status-item">
                    <div class="plugin-info">
                        <img src="https://ps.w.org/contact-form-7/assets/icon-128x128.png" alt="Contact Form 7 Icon" class="dbu-plugin-icon" />
                        <p>Contact Form 7</p>
                    </div>
                    <button class="dbu-status-button" style="background-color: <?php echo ($cf7_active) ? '#2271b1' : 'lightgrey'; ?>;" disabled>
                        <?php echo $cf7_active ? 'Installed' : 'Not Installed'; ?>
                    </button>
                </div>

                <!-- Gravity Forms -->
                <div class="dbu-status-item">
                    <div class="plugin-info">
                        <img src="<?php echo GRAVITYFORMS_ICON; ?>" alt="Gravity Forms Icon" class="dbu-plugin-icon" />
                        <p>Gravity Forms</p>
                    </div>
                    <button class="dbu-status-button" style="background-color: <?php echo ($gravity_active) ? '#2271b1' : 'lightgrey'; ?>;" disabled>
                        <?php echo $gravity_active ? 'Installed' : 'Not Installed'; ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="dbu-card">
            <h2>Credits</h2>
            <p><strong>Dynamic Blacklist Updater</strong> Plugin is developed by <a href="https://wera.no" target="_blank">Wera AS</a>.</p>
            <p>Special thanks to <a href="https://github.com/splorp/wordpress-comment-blacklist" target="_blank">Splorp's WordPress Comment Blacklist</a> for the blacklist.</p>
        </div>
    </div>
<?php
}
