# Domain Models Architecture

This document describes the core domain models used in the DetIt WooCommerce SEO auditing tool to establish shared data structures across the audit engine, generators, UI, and bulk operations. These models are pure Data Transfer Objects (DTOs) with no business logic.

## Issue Model
**Class:** `DetIt\Domain\DTO\Issue`

The `Issue` model represents a single SEO problem found during a product audit.

*   **Severity Levels:** The `severity` property indicates the critical nature of the issue (e.g., `high`, `medium`, `low`).
*   **Fields Audited:** The `field` property defines the specific data point that contains the issue, such as `product_title`, `meta_description`, or `image_alt`.
*   **Current and Recommended Values:** Holds the existing state (`current_value`) and the proposed fix or optimization (`recommended_value`).

## AuditResult Model
**Class:** `DetIt\Domain\DTO\AuditResult`

The `AuditResult` model encapsulates the outcome of auditing a specific WooCommerce product.

*   **Relationship:** It links directly to a product via `product_id` and contains a collection (`issues` array) of `Issue` objects.
*   **Score Storage:** The `score` property is a numeric representation of the product's SEO health. The scoring logic itself is external to this DTO; it solely stores the computed integer.
*   **Audit Pipeline Role:** It serves as the primary output of the audit engine and the input for suggestion/fix generators, ensuring consistent data transition.

## FixPlan Model
**Class:** `DetIt\Domain\DTO\FixPlan`

The `FixPlan` model represents a structured blueprint of actions required to automatically or manually resolve SEO issues.

*   **Automated Fixing:** Designed to be consumed by fix executors. It holds instructions without containing any of the execution logic itself.
*   **Actions Structure:** The `actions` array details specific operations. Example format: `['type' => 'update_meta', 'field' => 'seo_title', 'value' => 'Best Running Shoes']`.
*   **Bulk Operations:** Extensively used in bulk processing scenarios, allowing a series of `FixPlan` objects to be generated and sequentially processed.

## Serialization Flow

To facilitate database storage and API responses, DTOs implement serialization.

1.  **Contract:** DTOs implement the `DetIt\Contracts\SerializableDTO` interface, providing `toArray()` and `fromArray()` methods.
2.  **Implementation:** The `DetIt\Infrastructure\Serialization\JsonSerializer` class uses these methods to convert objects.
3.  **Process:**
    *   *Serialize:* The serializer calls `toArray()` on the DTO and encodes the resulting array into JSON.
    *   *Deserialize:* The serializer takes a JSON string, decodes it, instantiates the target DTO class, and populates it using `fromArray()`. Nested objects (like the `Issue` array inside `AuditResult`) are recursively handled during the `fromArray()` call.
