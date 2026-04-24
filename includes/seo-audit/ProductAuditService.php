<?php

if (! defined('ABSPATH')) {
    exit;
}

use DetIt\Domain\DTO\AuditResult;
use DetIt\SeoAudit\ScoreEngine;

class ProductAuditService
{
    public function run(int $product_id): AuditResult
    {
        $product = wc_get_product($product_id);

        if (! $product) {
            return new AuditResult(
                $product_id,
                0,
                [],
                []
            );
        }

        $data = $this->get_product_data($product);

        $issue_ids = [];

        $checkers = $this->get_checkers();

        foreach ($checkers as $checker) {

            $results = $checker->check($data);

            if (! empty($results)) {
                $issue_ids = array_merge($issue_ids, $results);
            }
        }

        // Run the scoring engine
        $score_data = ScoreEngine::calculate($issue_ids);

        return new AuditResult(
            $product_id,
            $score_data['score'],
            $score_data['issue_counts'],
            $issue_ids
        );
    }

    private function get_product_data(WC_Product $product): array
    {
        return [
            'id' => $product->get_id(),
            'title' => $product->get_name(),
            'slug' => $product->get_slug(),
            'short_description' => $product->get_short_description(),
            'description' => $product->get_description(),
            'featured_image' => $product->get_image_id(),
            'gallery_images' => $product->get_gallery_image_ids(),
        ];
    }

    private function get_checkers(): array
    {
        return [
            new TitleCheck(),
            new DescriptionCheck(),
            new MetaDescriptionCheck(),
            new KeywordCheck(),
            new AltCheck(),
        ];
    }
}
