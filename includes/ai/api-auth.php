<?php

namespace DetIt\AI;

use DetIt\ContentGenerator\OutputSchema;

if (!defined('ABSPATH')) exit;





if (!defined('ABSPATH')) exit;

class GeminiClient
{
    private string $api_key;

    public function __construct()
    {
        $api_key = get_option('detit_api_key');

        if (empty($api_key) && defined('DETIT_AI_API_KEY')) {
            $api_key = DETIT_AI_API_KEY;
        }

        if (empty($api_key)) {
            throw new \RuntimeException('DetIt requires a Gemini API key to function.');
        }

        $this->api_key = $api_key;
    }

    private function get_model(): string
    {
        $model = get_option('detit_ai_model');
        if (empty($model)) {
            $model = 'gemini-2.5-flash';
        }
        return apply_filters('detit_ai_model', $model);
    }

    /**
     * Send a prompt to Gemini and return the raw text response.
     *
     * @param  string $system_prompt
     * @param  string $user_prompt
     * @return string Raw response text (should be JSON)
     * @throws \RuntimeException on HTTP or API error
     */
    public function complete(string $system_prompt, string $user_prompt): string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->get_model() . ':generateContent?key=' . $this->api_key;

        $body = wp_json_encode([
            'system_instruction' => [
                'parts' => [
                    ['text' => $system_prompt],
                ],
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $user_prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature'      => 0.4,
                'maxOutputTokens'  => 4096,
            ],
        ]);

        $response = wp_remote_post($url, [
            'timeout' => 45,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
        ]);

        if (is_wp_error($response)) {
            throw new \RuntimeException('HTTP request failed: ' . esc_html($response->get_error_message()));
        }

        $status = wp_remote_retrieve_response_code($response);
        $raw    = wp_remote_retrieve_body($response);

        if ($status !== 200) {
            throw new \RuntimeException('Gemini API error ' . esc_html($status) . ': ' . esc_html($raw));
        }

        $data = json_decode($raw, true);

        // Gemini's response text is nested here
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}
