<?php

namespace DetIt\ContentGenerator;

use DetIt\AI\GeminiClient;

if (!defined('ABSPATH')) exit;

class ContentGenerator
{
    private GeminiClient $client;
    private PromptBuilder $prompt_builder;

    public function __construct()
    {
        $this->client         = new GeminiClient();
        $this->prompt_builder = new PromptBuilder();
    }

    public function generate(int $product_id): array
    {
        $collector    = new DataCollector();
        $product_data = $collector->get_product_data($product_id);
        $store_data   = $collector->get_store_data();

        if (!$product_data) {
            throw new \RuntimeException('Product not found.');
        }

        $context  = ( new ContextBuilder() )->build($product_data, $store_data);
        $prompts  = $this->prompt_builder->build($context);
        $raw_json = $this->client->complete($prompts['system'], $prompts['user']);

        $data = $this->parse_response($raw_json);
        return $this->validate_tags($data, $context['relevant_tags'] ?? []);
    }

    private function validate_tags(array $data, array $relevant_tags): array
    {
        // If we found 6 or more perfectly matching tags from the site, enforce only those 6.
        if (count($relevant_tags) === 6) {
            $data['tags'] = $relevant_tags;
            return $data;
        }

        if (!isset($data['tags']) || !is_array($data['tags'])) {
            $data['tags'] = [];
            return $data;
        }

        $valid_tags = [];
        foreach ($data['tags'] as $tag) {
            if (is_string($tag)) {
                $tag = strtolower(trim($tag));
                if (!empty($tag)) {
                    $valid_tags[] = $tag;
                }
            }
        }

        $valid_tags = array_unique($valid_tags);
        $data['tags'] = array_values(array_slice($valid_tags, 0, 6));

        return $data;
    }

    private function parse_response(string $raw): array
    {

        $raw = trim($raw);

        // Strategy 1: decode directly
        $data = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        // Strategy 2: sanitise control characters then decode
        $sanitized = $this->sanitize_raw($raw);
        $data = json_decode($sanitized, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        // Strategy 3: strip markdown fences (```json ... ```)
        $stripped = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $stripped = preg_replace('/\s*```$/s', '', $stripped);
        $stripped = $this->sanitize_raw(trim($stripped));

        $data = json_decode($stripped, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        // Strategy 4: extract the outermost { ... } block
        if (preg_match('/\{.*\}/s', $raw, $matches)) {
            $data = json_decode($this->sanitize_raw($matches[0]), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        // All strategies failed
        $preview = mb_substr($raw, 0, 500);
        throw new \RuntimeException(
            'Failed to parse Gemini response as JSON. Raw preview: ' . $preview
        );
    }

    private function sanitize_raw(string $raw): string
    {
        // Gemini sometimes emits literal newlines/tabs inside JSON string values,
        // which is invalid JSON. Replace them with proper escape sequences.
        return preg_replace_callback(
            '/("(?:[^"\\\\]|\\\\.)*")/s',
            function ($m) {
                $val = $m[1];
                $val = str_replace("\r\n", '\n', $val);
                $val = str_replace("\r",   '\n', $val);
                $val = str_replace("\n",   '\n', $val);
                $val = str_replace("\t",   '\t', $val);
                return $val;
            },
            $raw
        ) ?? $raw;
    }
}
