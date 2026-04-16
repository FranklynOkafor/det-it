<?php

namespace DetIt;

class Loader
{


  public static function init()
  {

    self::load_modules();
    self::register_hooks();
  }

  private static function load_modules(): void{

    // Content Generation
    require_once DETIT_PLUGIN_DIR . 'includes/content-generation/bootstrap.php';
    require_once DETIT_PLUGIN_DIR . 'includes/content-generation/generator-engine.php';
    require_once DETIT_PLUGIN_DIR . 'includes/content-generation/prompt-builder.php';
    require_once DETIT_PLUGIN_DIR . 'includes/content-generation/response-parser.php';

    // SEO AUDIT
    require_once DETIT_PLUGIN_DIR . 'includes/seo-audit/score-engine.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo-audit/audit-engine.php';
    // SEO AUDIT CHECKS
    require_once DETIT_PLUGIN_DIR . 'includes/seo-audit/bootstrap.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo-audit/checks/alt-check.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo-audit/checks/keyword-check.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo-audit/checks/meta-description-check.php';
    require_once DETIT_PLUGIN_DIR . 'includes/seo-audit/checks/title-check.php';

    // META MODULE
    require_once DETIT_PLUGIN_DIR . 'includes/meta-module/bootstrap.php';
    \DetIt\Meta\boot(); // ← boot it after including


    // ADMIN
    require_once DETIT_PLUGIN_DIR . 'admin/dashboard.php';
    require_once DETIT_PLUGIN_DIR . 'admin/product-panel.php';
    require_once DETIT_PLUGIN_DIR . 'admin/bulk-tools.php';

    // API
    require_once DETIT_PLUGIN_DIR . 'api/audit-endpoint.php';
    require_once DETIT_PLUGIN_DIR . 'api/generator-endpoint.php';
    require_once DETIT_PLUGIN_DIR . 'api/scan-endpoint.php';
  }

  private static function register_hooks(): void
  {
    // future: enqueue scripts, register REST routes, etc.
  }
}
