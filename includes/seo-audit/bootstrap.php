<?php

/**
 * SEO Audit Bootstrap
 * 
 * Wires al the files for SEO Audit and also calls in their hooks
 * Called once from the loader
 * 
 * @package DetIt
 * @since 1.0.0
 */

namespace DetIt\SeoAudit;

if (! defined('ABSPATH')) {
    exit;
}

// SEO AUDIT
require_once __DIR__ . '/score-engine.php';
require_once __DIR__ . '/audit-engine.php';
// SEO AUDIT CHECKS
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/checks/alt-check.php';
require_once __DIR__ . '/checks/keyword-check.php';
require_once __DIR__ . '/checks/meta-description-check.php';
require_once __DIR__ . '/checks/title-check.php';
require_once __DIR__ . '/checks/description-check.php';


function boot()
{

    add_action('init', __NAMESPACE__ . '\\register_audit');
}

function register_audit()
{
    // initialize audit system
}
