<?php

namespace DetIt\Admin\Onboarding;

if (!defined('ABSPATH')) {
    exit;
}

class Detit_Onboarding_Controller
{
    public function handle_submit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['detit_onboarding_submit'])) {
            $was_completed = detit_is_onboarding_completed();
            $success = detit_save_onboarding($_POST);
            if ($success) {
                $msg = $was_completed ? 'Settings saved successfully.' : 'Onboarding completed successfully. Welcome to your dashboard!';
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>There was an error saving your settings.</p></div>';
            }
        }
    }

    public function render_page()
    {
        $schema = require __DIR__ . '/onboarding-fields.php';
        $values = detit_get_onboarding_settings();
        $is_completed = detit_is_onboarding_completed();

        $title = $is_completed ? 'DetIt Settings' : 'DetIt Setup';
        $desc = $is_completed ? 'Update your DetIt store settings below.' : 'Please complete the onboarding process to tailor DetIt to your store.';
        $button_text = $is_completed ? 'Save Settings' : 'Complete Setup';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($title) . '</h1>';
        echo '<p>' . esc_html($desc) . '</p>';
        echo '<form method="post" action="">';
        
        wp_nonce_field('detit_onboarding', 'detit_onboarding_nonce');

        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';

        foreach ($schema as $key => $field) {
            $this->render_field($key, $field, $values);
        }

        echo '</tbody>';
        echo '</table>';

        echo '<p class="submit">';
        echo '<input type="submit" name="detit_onboarding_submit" id="submit" class="button button-primary" value="' . esc_attr($button_text) . '">';
        echo '</p>';

        echo '</form>';
        echo '</div>';
        
        // Add minimal inline script to toggle "other" fields if needed
        ?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                var selectWithOther = document.querySelectorAll('select.detit-onboarding-select-with-other');
                selectWithOther.forEach(function(select) {
                    var otherInputId = select.id + '_custom_container';
                    var otherInput = document.getElementById(otherInputId);
                    
                    if(otherInput) {
                        select.addEventListener('change', function() {
                            if (this.value === 'other') {
                                otherInput.style.display = 'block';
                            } else {
                                otherInput.style.display = 'none';
                            }
                        });
                        // Trigger on load
                        select.dispatchEvent(new Event('change'));
                    }
                });
            });
        </script>
        <?php
    }

    public function render_field($key, $field, $values)
    {
        $current_value = isset($values[$key]) ? esc_attr($values[$key]) : (isset($field['default']) ? esc_attr($field['default']) : '');

        echo '<tr>';
        echo '<th scope="row"><label for="' . esc_attr($key) . '">' . esc_html($field['label']) . '</label></th>';
        echo '<td>';

        if ($field['type'] === 'text') {
            echo '<input name="' . esc_attr($key) . '" type="text" id="' . esc_attr($key) . '" value="' . $current_value . '" class="regular-text">';
        } elseif ($field['type'] === 'select' || $field['type'] === 'select_with_other') {
            $class = $field['type'] === 'select_with_other' ? 'detit-onboarding-select-with-other' : '';
            echo '<select name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" class="' . esc_attr($class) . '">';
            echo '<option value="">&mdash; Select &mdash;</option>';
            foreach ($field['options'] as $opt_val => $opt_label) {
                $selected = selected($current_value, $opt_val, false);
                echo '<option value="' . esc_attr($opt_val) . '" ' . $selected . '>' . esc_html($opt_label) . '</option>';
            }
            echo '</select>';

            if ($field['type'] === 'select_with_other') {
                $custom_key = $key . '_custom';
                $custom_value = isset($values[$custom_key]) ? esc_attr($values[$custom_key]) : '';
                echo '<div id="' . esc_attr($key) . '_custom_container" style="display:none; margin-top: 10px;">';
                echo '<input name="' . esc_attr($custom_key) . '" type="text" id="' . esc_attr($custom_key) . '" value="' . $custom_value . '" class="regular-text" placeholder="Please specify...">';
                echo '</div>';
            }
        }

        echo '</td>';
        echo '</tr>';
    }
}

/**
 * Global helper to get all onboarding settings.
 *
 * @return array
 */
function detit_get_onboarding_settings()
{
    return get_option('detit_onboarding_settings', []);
}

/**
 * Global helper to get a specific onboarding setting.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function detit_get_onboarding_setting($key, $default = '')
{
    $settings = detit_get_onboarding_settings();
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Global helper to check if onboarding is completed.
 *
 * @return bool
 */
function detit_is_onboarding_completed()
{
    return (bool) get_option('detit_onboarding_completed', false);
}
