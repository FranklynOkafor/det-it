<?php
/**
 * DetIt Settings Save Logic
 *
 * @package DetIt
 */

namespace DetIt\Admin\Settings;

if (!defined('ABSPATH')) {
    exit;
}

class DetIt_Settings_Save
{
    /**
     * Saves settings data, validating against the schema.
     *
     * @param array $data Raw POST data
     * @return bool True on success, false on failure
     */
    public static function save($data)
    {
        if (!isset($data['detit_settings_nonce']) || !wp_verify_nonce($data['detit_settings_nonce'], 'detit_settings')) {
            return false;
        }

        if (!current_user_can('manage_options')) {
            return false;
        }

        $schema = DetIt_Settings_Fields::get_fields();
        $clean_data = [];

        foreach ($schema as $key => $field) {
            if ($field['type'] === 'text') {
                $clean_data[$key] = isset($data[$key]) ? sanitize_text_field($data[$key]) : '';
            } elseif ($field['type'] === 'select' || $field['type'] === 'select_with_other') {
                $value = isset($data[$key]) ? sanitize_text_field($data[$key]) : '';
                
                if ($field['type'] === 'select_with_other' && $value === 'other') {
                    $clean_data[$key . '_custom'] = isset($data[$key . '_custom']) ? sanitize_text_field($data[$key . '_custom']) : '';
                }
                
                $clean_data[$key] = $value;
            }
        }

        update_option('detit_context', $clean_data);
        update_option('detit_settings_completed', true);

        if (isset($data['detit_api_key'])) {
            update_option('detit_api_key', sanitize_text_field($data['detit_api_key']));
        }

        return true;
    }
}

// Removed helper function

