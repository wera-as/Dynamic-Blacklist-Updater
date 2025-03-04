<?php

namespace DynamicBlacklistUpdater\Validation;

/**
 * Formidable Forms Validation for Dynamic Blacklist Updater.
 *
 * This code checks Formidable Forms submissions against the blacklist.
 */

function dbu_frm_validate_entry($errors, $values)
{
    $blacklist = get_option('blacklist_keys', '');
    if (empty($blacklist)) {
        return $errors;
    }
    $blacklist_array = array_filter(array_map('trim', explode("\n", $blacklist)));

    // Combine all submitted values.
    $haystack = '';
    foreach ($values as $field_id => $value) {
        if (is_array($value)) {
            $value = implode(" ", $value);
        }
        $haystack .= ' ' . $value;
    }

    foreach ($blacklist_array as $keyword) {
        if ('' !== $keyword && stripos($haystack, $keyword) !== false) {
            // Increase the hit counter.
            $hits = (int) get_option('dbu_blacklist_hits', 0);
            update_option('dbu_blacklist_hits', ++$hits);
            // Add an error message so the entry is blocked.
            $errors['dbu_blacklist'] = __('Your submission contains disallowed content.', 'dynamic-blacklist-updater');
            break;
        }
    }
    return $errors;
}
add_filter('frm_validate_entry', 'dbu_frm_validate_entry', 10, 2);
