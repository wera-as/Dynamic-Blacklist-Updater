<?php

/**
 * Contact Form 7 Validation for Dynamic Blacklist Updater.
 *
 * This code checks CF7 submissions against the blacklist.
 * If any blacklisted term is found in the submitted values,
 * the hit counter is incremented and a validation error is added.
 */

function dbu_cf7_validate($result, $tags)
{
    // Retrieve the blacklist from options.
    $blacklist = get_option('blacklist_keys', '');
    if (empty($blacklist)) {
        return $result;
    }
    $blacklist_array = array_filter(array_map('trim', explode("\n", $blacklist)));

    // Concatenate all submitted values.
    $haystack = '';
    foreach ($_POST as $key => $value) {
        if (is_string($value)) {
            $haystack .= ' ' . $value;
        }
    }

    // Loop through each keyword in the blacklist.
    foreach ($blacklist_array as $keyword) {
        if ('' !== $keyword && stripos($haystack, $keyword) !== false) {
            // Increase the hit counter.
            $hits = (int) get_option('dbu_blacklist_hits', 0);
            update_option('dbu_blacklist_hits', ++$hits);
            // Add an error to one of the form fields (this triggers a global error).
            if (is_array($tags) && !empty($tags)) {
                foreach ($tags as $tag) {
                    $result->invalidate($tag, __('Your submission contains disallowed content.', 'dynamic-blacklist-updater'));
                    break; // Invalidate only one field.
                }
            }
            break;
        }
    }
    return $result;
}
add_filter('wpcf7_validate', 'dbu_cf7_validate', 10, 2);
