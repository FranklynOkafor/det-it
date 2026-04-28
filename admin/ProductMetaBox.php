<?php
namespace Detit\Admin;

if (!defined('ABSPATH')) exit;

class ProductMetaBox {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
    }

    public function register_meta_box() {
        add_meta_box(
            'detit_meta_box',
            'DetIt',
            [$this, 'render_meta_box'],
            'product',
            'side',
            'high'
        );
    }

    public function render_meta_box($post) {
        ?>
        <div id="detit-container">
            <button 
                type="button"
                class="button button-primary detit-trigger"
                data-product-id="<?php echo esc_attr($post->ID); ?>"
                style="width:100%;">
                Detail It
            </button>

            <div id="detit-response" style="margin-top:10px;"></div>
        </div>
        <?php
    }
}