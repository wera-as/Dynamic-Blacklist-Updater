<?php

namespace DynamicBlacklistUpdater\Admin;

function dbu_enqueue_admin_scripts($hook)
{
    if (!in_array($hook, ['toplevel_page_dynamic-blacklist-updater', 'settings_page_dynamic-blacklist-updater'])) {
        return;
    }

    wp_enqueue_script(
        'server-time',
        DBU_PLUGIN_URL . 'includes/js/server-time.js',
        [],
        '1.0',
        true
    );

    wp_enqueue_script(
        'blacklist-toggle.js',
        DBU_PLUGIN_URL . 'includes/js/blacklist-toggle.js',
        [],
        '1.0',
        true
    );
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\dbu_enqueue_admin_scripts');

function dbu_enqueue_admin_styles($hook)
{
    $screen = get_current_screen();

    if (!isset($screen->id) || !in_array($screen->id, [
        'toplevel_page_dynamic-blacklist-updater',
        'settings_page_dynamic-blacklist-updater',
        'options-discussion'
    ], true)) {
        return;
    }

    wp_enqueue_style(
        'dbu_admin_css',
        DBU_PLUGIN_URL . 'includes/css/admin.css',
        [],
        '1.0',
        'all'
    );
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\dbu_enqueue_admin_styles');

function dbu_enqueue_discussion_lock_script($hook)
{
    $screen = get_current_screen();

    if (isset($screen->id) && 'options-discussion' === $screen->id) {
        wp_enqueue_script(
            'dbu-discussion-lock',
            DBU_PLUGIN_URL . 'includes/js/lock_moderation_fields.js',
            ['jquery'],
            '1.0',
            true
        );
    }
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\dbu_enqueue_discussion_lock_script');
