<?php

/**
 * WPForms Validation for Dynamic Blacklist Updater.
 *
 * This code checks WPForms submissions against the blacklist.
 */

function dbu_wpforms_validate($errors, $fields, $form_data)
{
    $blacklist = get_option('blacklist_keys', '');
    if (empty($blacklist)) {
        return $errors;
    }
    $blacklist_array = array_filter(array_map('trim', explode("\n", $blacklist)));

    // Concatenate all field values.
    $haystack = '';
    foreach ($fields as $field) {
        if (isset($field['value'])) {
            $haystack .= ' ' . $field['value'];
        }
    }

    // Check each keyword.
    foreach ($blacklist_array as $keyword) {
        if ('' !== $keyword && stripos($haystack, $keyword) !== false) {
            // Increase the hit counter.
            $hits = (int) get_option('dbu_blacklist_hits', 0);
            update_option('dbu_blacklist_hits', ++$hits);
            // Add an error to block the submission.
            $errors['header'] = __('Your submission contains disallowed content.', 'dynamic-blacklist-updater');
            break;
        }
    }
    return $errors;
}
add_filter('wpforms_process_validate', 'dbu_wpforms_validate', 10, 3);
