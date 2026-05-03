<?php
/**
 * DetIt Settings Page Controller
 *
 * @package DetIt
 */

namespace DetIt\Admin\Settings;

if (!defined('ABSPATH')) {
    exit;
}

class DetIt_Settings_Page
{
    public function handle_submit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['detit_settings_submit'])) {
            $success = DetIt_Settings_Save::save($_POST);
            if ($success) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'detit') . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('There was an error saving your settings.', 'detit') . '</p></div>';
            }
        }
    }

    public function render_page()
    {
        $schema = DetIt_Settings_Fields::get_fields();
        $values = self::get_context();
        
        $title = __('Content Context Settings', 'detit');
        $desc = __('Configure your store context to improve AI-generated product content.', 'detit');
        $button_text = __('Save Settings', 'detit');

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($title) . '</h1>';
        echo '<p>' . esc_html($desc) . '</p>';
        
        // Disclosure Notice
        echo '<div class="notice notice-info inline"><p>' . esc_html__('This information is used to improve AI-generated product content. No data is sent externally without user action.', 'detit') . '</p></div>';

        echo '<form method="post" action="">';
        
        wp_nonce_field('detit_settings', 'detit_settings_nonce');

        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';

        foreach ($schema as $key => $field) {
            $this->render_field($key, $field, $values);
        }

        echo '</tbody>';
        echo '</table>';

        echo '<p class="submit">';
        echo '<input type="submit" name="detit_settings_submit" id="submit" class="button button-primary" value="' . esc_attr($button_text) . '">';
        echo '</p>';

        echo '</form>';
        echo '</div>';
        
        // Add minimal inline script to toggle "other" fields if needed
        ?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                var selectWithOther = document.querySelectorAll('select.detit-settings-select-with-other');
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
            $class = $field['type'] === 'select_with_other' ? 'detit-settings-select-with-other' : '';
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

    /**
     * Helper to get all context settings.
     */
    public static function get_context()
    {
        return get_option('detit_context', []);
    }

    /**
     * Helper to check if settings are completed.
     */
    public static function is_settings_completed()
    {
        return (bool) get_option('detit_settings_completed', false);
    }
}
