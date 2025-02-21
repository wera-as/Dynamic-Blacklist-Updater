<?php

/**
 * Gravity Forms Validation for Dynamic Blacklist Updater.
 *
 * This code checks Gravity Forms submissions against the blacklist.
 * If any blacklisted term is found in the submitted values, it increments
 * the hit counter and invalidates the form submission with an error message.
 */

function dbu_gravityforms_validate($validation_result)
{
    // Get the form object from the validation result.
    $form = $validation_result['form'];

    // Combine submitted field values into one string.
    $haystack = '';
    foreach ($form['fields'] as $field) {
        // Use Gravity Forms helper to get the field value.
        $value = rgpost('input_' . $field->id);
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        $haystack .= ' ' . $value;
    }

    // Retrieve the blacklist.
    $blacklist = get_option('blacklist_keys', '');
    if (empty($blacklist)) {
        return $validation_result;
    }
    $blacklist_array = array_filter(array_map('trim', explode("\n", $blacklist)));

    // Check the combined submission for each blacklisted keyword.
    foreach ($blacklist_array as $keyword) {
        if ('' !== $keyword && stripos($haystack, $keyword) !== false) {
            // Increase the hit counter.
            $hits = (int) get_option('dbu_blacklist_hits', 0);
            update_option('dbu_blacklist_hits', ++$hits);

            // Mark the form as invalid.
            $validation_result['is_valid'] = false;
            $validation_result['message'] = __('Your submission contains disallowed content.', 'dynamic-blacklist-updater');

            // Optionally, mark the first field as having an error.
            if (! empty($form['fields'])) {
                $form['fields'][0]->failed_validation = true;
                $form['fields'][0]->validation_message = __('Your submission contains disallowed content.', 'dynamic-blacklist-updater');
            }
            $validation_result['form'] = $form;
            break;
        }
    }

    return $validation_result;
}
add_filter('gform_validation', 'dbu_gravityforms_validate');
