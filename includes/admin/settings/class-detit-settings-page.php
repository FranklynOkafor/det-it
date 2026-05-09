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

        // API key warning — read AFTER handle_submit() so value is always fresh.
        $current_api_key = get_option('detit_api_key');
        if (empty($current_api_key) && !defined('DETIT_AI_API_KEY')) {
            echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__('DetIt:', 'detit') . '</strong> ' . esc_html__('DetIt requires a Gemini API key to function. Enter your key below.', 'detit') . '</p></div>';
        }

        echo '<form method="post" action="">';
        
        wp_nonce_field('detit_settings', 'detit_settings_nonce');

        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';

        foreach ($schema as $key => $field) {
            $this->render_field($key, $field, $values);
        }

        $api_key = get_option('detit_api_key');
        echo '<tr>';
        echo '<th scope="row"><label for="detit_api_key">' . esc_html__('Gemini API Key', 'detit') . '</label></th>';
        echo '<td>';
        echo '<input name="detit_api_key" type="password" id="detit_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
        echo '</td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';

        echo '<p class="submit">';
        echo '<input type="submit" name="detit_settings_submit" id="submit" class="button button-primary" value="' . esc_attr($button_text) . '">';
        echo '</p>';

        echo '</form>';

        echo '<hr>';
        echo '<h2>' . esc_html__('How To Get Your Gemini API Key', 'detit') . '</h2>';
        echo '<p>' . esc_html__('The plugin will not function without a Gemini API key.', 'detit') . '</p>';
        echo '<ol>';
        echo '<li>' . esc_html__('Go to Google AI Studio', 'detit') . '</li>';
        echo '<li>' . esc_html__('Sign in with your Google account', 'detit') . '</li>';
        echo '<li>' . esc_html__('Create a new API key', 'detit') . '</li>';
        echo '<li>' . esc_html__('Copy the generated API key', 'detit') . '</li>';
        echo '<li>' . esc_html__('Paste it into the DetIt settings field above', 'detit') . '</li>';
        echo '<li>' . esc_html__('Save settings', 'detit') . '</li>';
        echo '</ol>';
        echo '<p><a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" class="button">' . esc_html__('Get API Key from Google AI Studio', 'detit') . '</a></p>';

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
