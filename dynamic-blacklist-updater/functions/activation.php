<?php

namespace DynamicBlacklistUpdater\Functions;

/**
 * Schedule the blacklist update on plugin activation.
 */
function dbu_activate_plugin()
{
    // Set default options if not already set.
    if (false === get_option('dbu_update_interval')) {
        update_option('dbu_update_interval', 'daily');
    }
    if (false === get_option('dbu_menu_location')) {
        update_option('dbu_menu_location', 'top');
    }
    if (false === get_option('dbu_blacklist_url')) {
        update_option('dbu_blacklist_url', DBU_DEFAULT_BLACKLIST_URL);
    }
    if (false === get_option('dbu_blacklist_fallback_url')) {
        update_option('dbu_blacklist_fallback_url', DBU_DEFAULT_BLACKLIST_FALLBACK_URL);
    }

    // Schedule our WP Cron event.
    dbu_reschedule_event();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\dbu_activate_plugin');

/**
 * Clear the scheduled event on plugin deactivation.
 */
function dbu_deactivate_plugin()
{
    wp_clear_scheduled_hook('dbu_update_blacklist_event');
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\dbu_deactivate_plugin');

/**
 * Reschedule the event using the current update interval.
 */
function dbu_reschedule_event()
{
    wp_clear_scheduled_hook('dbu_update_blacklist_event');
    $interval = get_option('dbu_update_interval', 'daily');

    if (! wp_next_scheduled('dbu_update_blacklist_event')) {
        wp_schedule_event(time(), $interval, 'dbu_update_blacklist_event');
    }
}

// Hook our update function to the cron event.
// Make sure that dbu_fetch_blacklist() is defined in this namespace,
// or adjust the callback to reference its actual namespace.
add_action('dbu_update_blacklist_event', __NAMESPACE__ . '\dbu_fetch_blacklist');
