<?php

namespace DetIt\SEO;

if (!defined('ABSPATH')) {
    exit;
}

interface SEO_Adapter_Interface
{
    /**
     * Get the SEO title for a product.
     *
     * @param int $post_id
     * @return string
     */
    public function get_title(int $post_id): string;

    /**
     * Get the SEO description for a product.
     *
     * @param int $post_id
     * @return string
     */
    public function get_description(int $post_id): string;

    /**
     * Get the SEO focus keyword for a product.
     *
     * @param int $post_id
     * @return string
     */
    public function get_keyword(int $post_id): string;

    /**
     * Set the SEO meta fields for a product.
     *
     * @param int    $post_id
     * @param string $title
     * @param string $description
     * @param string $keyword
     * @return void
     */
    public function set_meta(int $post_id, string $title, string $description, string $keyword): void;
}
