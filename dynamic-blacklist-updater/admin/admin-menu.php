<?php
namespace DynamicBlacklistUpdater\Admin;

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
            __NAMESPACE__ . '\dbu_render_admin_page',
            DBU_MENU_ICON,
            81
        );
    } else {
        add_options_page(
            'Dynamic Blacklist Updater',
            'Dynamic Blacklist Updater',
            'manage_options',
            'dynamic-blacklist-updater',
            __NAMESPACE__ . '\dbu_render_admin_page'
        );
    }
}
add_action('admin_menu', __NAMESPACE__ . '\dbu_register_menu');

/**
 * Add a Settings link to the plugin action links on the plugins page.
 */
function dbu_plugin_action_links($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=dynamic-blacklist-updater') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), __NAMESPACE__ . '\dbu_plugin_action_links');
