<?php

namespace DetIt\Admin\Onboarding;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Saves onboarding data, validating against the schema.
 *
 * @param array $data Raw POST data
 * @return bool True on success, false on failure
 */
function detit_save_onboarding($data)
{
    if (!isset($data['detit_onboarding_nonce']) || !wp_verify_nonce($data['detit_onboarding_nonce'], 'detit_onboarding')) {
        return false;
    }

    if (!current_user_can('manage_options')) {
        return false;
    }

    $schema = require __DIR__ . '/onboarding-fields.php';
    $clean_data = [];

    foreach ($schema as $key => $field) {
        if ($field['type'] === 'text') {
            $clean_data[$key] = isset($data[$key]) ? sanitize_text_field($data[$key]) : '';
        } elseif ($field['type'] === 'select') {
            $value = isset($data[$key]) ? sanitize_text_field($data[$key]) : '';
            // If the user selected 'other' but there might be an option for them to type something, 
            // the schema doesn't strictly define an 'other' text box for 'select', but it's good practice
            // to capture it if they provided an 'other' input.
            if ($value === 'other' && isset($data[$key . '_other'])) {
                $clean_data[$key . '_other'] = sanitize_text_field($data[$key . '_other']);
            }
            $clean_data[$key] = $value;
        } elseif ($field['type'] === 'select_with_other') {
            $value = isset($data[$key]) ? sanitize_text_field($data[$key]) : '';
            if ($value === 'other') {
                $clean_data[$key . '_custom'] = isset($data[$key . '_custom']) ? sanitize_text_field($data[$key . '_custom']) : '';
            }
            $clean_data[$key] = $value;
        }
    }

    update_option('detit_onboarding_settings', $clean_data);
    update_option('detit_onboarding_completed', true);

    return true;
}
