<?php

/**
 * Meta Module Bootstrap
 *
 * Wires together all classes in the DetIt\Meta namespace.
 * Called once from the main plugin class during initialisation.
 *
 * Load order matters:
 *   1. SEO_Detector   — no dependencies
 *   2. Meta_Handler   — depends on SEO_Detector
 *   3. Meta_Output    — depends on SEO_Detector + Meta_Handler
 *   4. Meta_Box       — depends on SEO_Detector + Meta_Handler
 *
 * @package DetIt
 * @since   1.0.0
 */

namespace DetIt\Meta;

if (! defined('ABSPATH')) {
  exit;
}

require_once __DIR__ . '/class-seo-detector.php';
require_once __DIR__ . '/class-meta-handler.php';
require_once __DIR__ . '/class-meta-output.php';
require_once __DIR__ . '/class-meta-box.php';


/**
 * Initialise the meta module.
 *
 * @return void
 */
function boot(): void {

  // Front-end: output meta tags when no SEO plugin is present.
  if ( ! is_admin() ) {
    Meta_Output::register();
  }

  if ( is_admin() ) {
    Meta_Box::register();
  }

  

  // Test message to check if the plugin is working


  // $display_test_message = function() {
  //     $plugin = SEO_Detector::detect();
  //     $message = 'none' === $plugin ? 'No SEO plugin installed.' : 'Installed SEO plugin: ' . $plugin;
      
  //     echo '<div style="background: yellow; color: black; padding: 10px; margin: 10px; font-weight: bold; border: 2px solid red; z-index: 99999; position: relative; text-align: center;">DetIt Test: ' . esc_html( $message ) . '</div>';
  // };

  // add_action( 'admin_notices', $display_test_message );
  // add_action( 'wp_footer', $display_test_message );
}
