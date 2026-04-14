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

// Front-end: output meta tags when no SEO plugin is present.
if (! is_admin()) {
  Meta_Output::register();
}

if (is_admin()) {
  Meta_Box::register();
}
