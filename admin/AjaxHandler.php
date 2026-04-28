<?php
namespace Detit\Admin;

use Detit\ContentGenerator\DataCollector;
use Detit\ContentGenerator\ContextBuilder;

if (!defined('ABSPATH')) exit;

class AjaxHandler
{

    public function __construct()
    {
        add_action('wp_ajax_detit_generate', [$this, 'handle']);
    }

    public function handle() {

    check_ajax_referer('detit_nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID']);
    }

    $collector = new DataCollector();
    $builder   = new ContextBuilder();

    $product_data = $collector->get_product_data($product_id);
    $store_data   = $collector->get_store_data();

    if (!$product_data) {
        wp_send_json_error(['message' => 'Product not found']);
    }

    $context = $builder->build($product_data, $store_data);

    wp_send_json_success([
        'context' => $context
    ]);
}
}
