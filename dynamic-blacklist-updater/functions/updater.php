<?php

namespace DynamicBlacklistUpdater\Functions;

/**
 * Fetch the blacklist from the primary URL (with fallback if needed)
 * and update the settings.
 *
 * @return bool True on success, false on failure.
 */
function dbu_fetch_blacklist()
{
    $primary_url = get_option('dbu_blacklist_url', DBU_DEFAULT_BLACKLIST_URL);
    $response    = wp_remote_get($primary_url, ['timeout' => 15]);
    $body        = wp_remote_retrieve_body($response);

    // If primary fails, try the fallback.
    if (is_wp_error($response) || empty($body)) {
        error_log('Dynamic Blacklist Updater: Primary URL failed, trying fallback URL.');
        $fallback_url = get_option('dbu_blacklist_fallback_url', DBU_DEFAULT_BLACKLIST_FALLBACK_URL);
        $response     = wp_remote_get($fallback_url, ['timeout' => 15]);
        $body         = wp_remote_retrieve_body($response);
        if (is_wp_error($response) || empty($body)) {
            error_log('Dynamic Blacklist Updater: Fallback URL failed as well.');
            return false;
        }
    }

    $blacklist_array = array_filter(array_map('trim', explode("\n", $body)));
    if (! empty($blacklist_array)) {
        $blacklist = implode("\n", $blacklist_array);
        update_option('blacklist_keys', $blacklist);
        update_option('moderation_keys', $blacklist);
        update_option('dbu_last_updated', date('Y-m-d H:i:s'));
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
    $response     = wp_remote_get($fallback_url, ['timeout' => 15]);
    $body         = wp_remote_retrieve_body($response);
    if (is_wp_error($response) || empty($body)) {
        error_log('Dynamic Blacklist Updater: Fallback URL failed.');
        return false;
    }
    $blacklist_array = array_filter(array_map('trim', explode("\n", $body)));
    if (! empty($blacklist_array)) {
        $blacklist = implode("\n", $blacklist_array);
        update_option('blacklist_keys', $blacklist);
        update_option('moderation_keys', $blacklist);
        update_option('dbu_last_updated', date('Y-m-d H:i:s'));
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
 * Count blacklist hits when a comment is processed.
 *
 * @param mixed $approved    The current approval status.
 * @param array $commentdata The comment data.
 * @return mixed Updated approval status.
 */
function dbu_count_blacklist_hit($approved, $commentdata)
{
    $blacklist = get_option('blacklist_keys', '');
    if (empty($blacklist)) {
        return $approved;
    }
    $blacklist_array = array_filter(array_map('trim', explode("\n", $blacklist)));
    $haystack        = $commentdata['comment_content'] . ' ' . $commentdata['comment_author'] . ' ' . $commentdata['comment_author_email'] . ' ' . $commentdata['comment_author_url'];
    foreach ($blacklist_array as $keyword) {
        if ('' !== $keyword && stripos($haystack, $keyword) !== false) {
            $hits = (int) get_option('dbu_blacklist_hits', 0);
            update_option('dbu_blacklist_hits', ++$hits);
            break;
        }
    }
    return $approved;
}
add_filter('pre_comment_approved', __NAMESPACE__ . '\dbu_count_blacklist_hit', 10, 2);
