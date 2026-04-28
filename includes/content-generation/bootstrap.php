<?php
/**
 * Content Generator bootstrap
 * 
 * Wires all thee classes for DetIt\Content Generator
 * Called once from the loader
 * 
 * @package DetIt
 * @since 1.0.0
 */

namespace DetIt\ContentGenerator;

if (! defined('ABSPATH')) {
  exit;
}

require_once __DIR__ . '/DataCollector.php';
require_once __DIR__ . '/ContextBuilder.php';
require_once __DIR__ . '/generator-engine.php';
require_once __DIR__ . '/prompt-builder.php';
require_once __DIR__ . '/response-parser.php';





function boot()
{

    add_action('init', __NAMESPACE__ . '\\register_generator');
}

function register_generator()
{
    // initialize audit system
}
