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

// New AI components
require_once DETIT_PLUGIN_DIR . 'admin/AI/OutputSchema.php';
require_once DETIT_PLUGIN_DIR . 'admin/AI/PromptBuilder.php';
require_once DETIT_PLUGIN_DIR . 'admin/AI/AICliend.php';
require_once DETIT_PLUGIN_DIR . 'admin/AI/ContentGenerator.php';



function boot()
{

    add_action('init', __NAMESPACE__ . '\\register_generator');
}

function register_generator()
{
    // initialize audit system
}
