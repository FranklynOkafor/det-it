<?php

namespace Detit\ContentGenerator;

use Detit\AI\GeminiClient;

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
        $collector = new DataCollector();
        $product_data = $collector->get_product_data($product_id);
        $store_data   = $collector->get_store_data();

        if (!$product_data) {
            throw new \RuntimeException('Product not found.');
        }

        $context  = ( new ContextBuilder() )->build($product_data, $store_data);
        $prompts  = $this->prompt_builder->build($context);
        $raw_json = $this->client->complete($prompts['system'], $prompts['user']);

        return $this->parse_response($raw_json);
    }

    private function parse_response(string $raw): array
    {
        // responseMimeType=application/json should give clean output,
        // but strip fences just in case
        $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $clean = preg_replace('/\s*```$/', '', $clean);

        $data = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse Gemini response as JSON: ' . json_last_error_msg());
        }

        return $data;
    }
}