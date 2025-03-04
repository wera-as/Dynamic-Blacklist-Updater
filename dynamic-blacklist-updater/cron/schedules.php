<?php

namespace DynamicBlacklistUpdater\Cron;

/**
 * Add custom cron schedules.
 */
function dbu_custom_cron_schedules($schedules)
{
    $schedules['15min'] = [
        'interval' => 900,
        'display'  => __('Every 15 Minutes', 'dynamic-blacklist-updater'),
    ];
    $schedules['30min'] = [
        'interval' => 1800,
        'display'  => __('Every 30 Minutes', 'dynamic-blacklist-updater'),
    ];
    $schedules['hourly'] = [
        'interval' => 3600,
        'display'  => __('Hourly', 'dynamic-blacklist-updater'),
    ];
    $schedules['2hours'] = [
        'interval' => 7200,
        'display'  => __('Every 2 Hours', 'dynamic-blacklist-updater'),
    ];
    $schedules['4hours'] = [
        'interval' => 14400,
        'display'  => __('Every 4 Hours', 'dynamic-blacklist-updater'),
    ];
    $schedules['6hours'] = [
        'interval' => 21600,
        'display'  => __('Every 6 Hours', 'dynamic-blacklist-updater'),
    ];
    $schedules['12hours'] = [
        'interval' => 43200,
        'display'  => __('Every 12 Hours', 'dynamic-blacklist-updater'),
    ];
    $schedules['daily'] = [
        'interval' => 86400,
        'display'  => __('Daily', 'dynamic-blacklist-updater'),
    ];
    $schedules['weekly'] = [
        'interval' => 604800,
        'display'  => __('Weekly', 'dynamic-blacklist-updater'),
    ];
    $schedules['biweekly'] = [
        'interval' => 1209600,
        'display'  => __('Every 2 Weeks', 'dynamic-blacklist-updater'),
    ];
    $schedules['monthly'] = [
        'interval' => 2592000,
        'display'  => __('Monthly', 'dynamic-blacklist-updater'),
    ];

    return $schedules;
}
add_filter('cron_schedules', __NAMESPACE__ . '\dbu_custom_cron_schedules');
