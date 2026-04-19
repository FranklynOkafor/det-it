# Security Guidelines

## WordPress Security Principles

When developing for DetIt, all code must adhere to core WordPress security principles to protect both the site and the user data.

*   **Nonces:** Numbers Used Once (Nonces) protect against Cross-Site Request Forgery (CSRF) attacks. Every form submission, bulk action, or AJAX request that modifies state must include a nonce, which must be verified before processing the action.
*   **Capability Checks:** Never assume a user's role. Always verify if the current user has the correct capability to perform an action. For DetIt, WooCommerce capabilities (`manage_woocommerce`) or fallback options (`manage_options`) are used.
*   **Sanitization vs. Validation:**
    *   *Sanitization* modifies input data to make it safe (e.g., stripping HTML tags, ensuring it's an integer). Use functions like `sanitize_text_field` or the `Request` wrapper.
    *   *Validation* checks if the data meets specific criteria (e.g., is this string a valid email format?) and rejects it if it doesn't.
*   **Escaping Output:** Always escape data right before rendering it to the screen to prevent Cross-Site Scripting (XSS). Use `esc_html`, `esc_attr`, `esc_url`, etc., when outputting data to the frontend or admin screens.

## DetIt Security Flow

The plugin follows a strict request pipeline for handling incoming actions securely:

1.  **Controller receives request:** An action hook or AJAX endpoint routes the request to a controller.
2.  **Capability verified:** The controller uses `DetIt\Security\Permission` to enforce that the user has the correct authorization.
3.  **Nonce verified:** The controller uses `DetIt\Security\Nonce` to ensure the request is intentional and secure.
4.  **Input sanitized:** User inputs (POST/GET) are extracted securely using the `DetIt\Http\Request` helper layer. *No direct access to `$_POST` or `$_GET` is permitted.*
5.  **Domain service executed:** Once authorization is confirmed and data is safe, the actual business logic or domain service is executed.

## Secure Examples

### Secure Form Submission

```php
// In your view (rendering the form)
<form method="post" action="">
    <?php echo \DetIt\Security\Nonce::field('save_settings'); ?>
    <input type="text" name="setting_value" value="">
    <button type="submit">Save</button>
</form>

// In your controller (handling the submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Enforce Capability
    \DetIt\Security\Permission::enforceManageSettings();
    
    // 2. Enforce Nonce
    $nonce = \DetIt\Http\Request::text('_wpnonce');
    \DetIt\Security\Nonce::enforce($nonce, 'save_settings');
    
    // 3. Retrieve Sanitized Input
    $settingValue = \DetIt\Http\Request::text('setting_value');
    
    // 4. Execute Service
    $settingsService->save($settingValue);
}
```

### Secure AJAX Request

```php
// In your AJAX handler
public function handleAjaxRequest() {
    // 1. Verify AJAX Nonce
    if (!\DetIt\Security\Nonce::verifyAjax('run_audit_action')) {
        wp_send_json_error('Invalid security token', 403);
    }
    
    // 2. Check Capability
    if (!\DetIt\Security\Permission::check(\DetIt\Security\Capability::RUN_AUDIT)) {
        wp_send_json_error('Unauthorized action', 403);
    }
    
    // 3. Retrieve Sanitized Input
    $productId = \DetIt\Http\Request::int('product_id');
    
    // 4. Execute Service
    $result = $auditEngine->auditProduct($productId);
    
    wp_send_json_success($result);
}
```

### Secure Bulk Operation

```php
// In your bulk action handler
public function processBulkAction() {
    // 1. Enforce Capability
    \DetIt\Security\Permission::enforce(\DetIt\Security\Capability::APPLY_FIXES);
    
    // 2. Enforce Nonce
    $nonce = \DetIt\Http\Request::text('_wpnonce');
    \DetIt\Security\Nonce::enforce($nonce, 'bulk_apply_fixes');
    
    // 3. Retrieve Sanitized Input
    $productIds = \DetIt\Http\Request::textArray('product_ids');
    
    // 4. Execute Service
    $fixEngine->applyBulkFixes($productIds);
}
```
