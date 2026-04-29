<?php

namespace Detit\ContentGenerator;

if (!defined('ABSPATH')) exit;

class PromptBuilder
{

    /**
     * Build the full prompt payload (system + user) for the AI request.
     *
     * @param  array $context  Output of ContextBuilder::build()
     * @return array           ['system' => string, 'user' => string]
     */
    public function build(array $context): array
    {
        return [
            'system' => $this->build_system_prompt($context['store']),
            'user'   => $this->build_user_prompt($context['product']),
        ];
    }

    // -------------------------------------------------------------------------
    // System prompt
    // -------------------------------------------------------------------------

    private function build_system_prompt(array $store): string
    {

        $store_name        = $this->sanitize($store['store_name']        ?? 'this store');
        $store_description = $this->sanitize($store['store_description'] ?? '');
        $target_audience   = $this->sanitize($store['target_audience']   ?? 'general consumers');
        $tone              = $this->sanitize($store['tone']              ?? 'professional');

        $store_context = $store_description
            ? "The store is described as: {$store_description}"
            : "No additional store description has been provided.";

        return <<<PROMPT
You are an expert e-commerce copywriter for {$store_name}.

{$store_context}

Your writing should always:
- Target this audience: {$target_audience}
- Use a {$tone} tone throughout
- Prioritise clarity, persuasion, and SEO value
- Avoid filler phrases, hype words, and repetition

You will receive product data and must return ONLY a valid JSON object that matches the schema provided in the user message. Do not include any explanation, markdown fences, or text outside the JSON object.
PROMPT;
    }

    // -------------------------------------------------------------------------
    // User prompt
    // -------------------------------------------------------------------------

    private function build_user_prompt(array $product): string
    {

        $title       = $this->sanitize($product['title']       ?? 'Unknown Product');
        $description = $this->sanitize($product['description'] ?? '');
        $short_desc  = $this->sanitize($product['short_desc']  ?? '');
        $categories  = $this->format_list($product['categories'] ?? []);
        $tags        = $this->format_list($product['tags']       ?? []);
        $schema      = OutputSchema::get_schema_json();

        $existing_content = $this->build_existing_content_block($description, $short_desc);

        return <<<PROMPT
Generate optimised product content for the following WooCommerce product.

## Product Data
- **Title:** {$title}
- **Categories:** {$categories}
- **Tags:** {$tags}
{$existing_content}

## Required Output Schema
Return ONLY a JSON object that strictly matches this schema — no extra keys, no missing keys:

{$schema}

Use the existing content above as context and source material. Improve, expand, or rewrite it as needed to maximise quality and SEO value.
PROMPT;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build the optional existing-content block so empty fields are omitted
     * cleanly rather than sending blank lines to the model.
     */
    private function build_existing_content_block(string $description, string $short_desc): string
    {

        $lines = [];

        if ($short_desc) {
            $lines[] = "- **Short Description:** {$short_desc}";
        }

        if ($description) {
            // Trim to a sensible length to stay within token limits
            $trimmed = $this->trim_to_words($description, 300);
            $lines[] = "- **Full Description:**\n{$trimmed}";
        }

        return $lines ? implode("\n", $lines) : '- *(No existing description provided)*';
    }

    private function sanitize(string $value): string
    {
        return wp_strip_all_tags(trim($value));
    }

    private function format_list(array $items): string
    {
        return $items ? implode(', ', array_map('sanitize_text_field', $items)) : 'None';
    }

    private function trim_to_words(string $text, int $max_words): string
    {
        $plain = wp_strip_all_tags($text);
        $words = explode(' ', $plain);

        if (count($words) <= $max_words) {
            return $plain;
        }

        return implode(' ', array_slice($words, 0, $max_words)) . '…';
    }
}
