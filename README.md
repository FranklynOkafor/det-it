# DetIt

DetIt is a lightweight SEO toolkit for WooCommerce product pages.

The plugin helps store owners **analyze, improve, and generate SEO content** for their products directly inside the WordPress product editor.

Unlike general SEO plugins such as Yoast or Rank Math, DetIt focuses specifically on **product-level SEO optimization for WooCommerce stores**.

---

# Current Development Status

DetIt is currently in **active MVP development**.

The foundational architecture of the plugin has been implemented, including:

* Modular plugin bootstrap system
* Hook registration framework
* Core domain models for the audit pipeline
* Security baseline infrastructure for admin actions and bulk operations

These components establish the technical foundation required to safely build the SEO auditing and generation features planned for the MVP.

---

# Features

## Current MVP Features

* SEO meta box inside the WooCommerce product editor
* Meta description editor
* Focus keyword field
* Automatic meta description output when no SEO plugin is active
* SEO plugin detection:

  * Yoast SEO
  * Rank Math
  * All in One SEO

When another SEO plugin is detected, DetIt disables meta tag output to prevent conflicts and acts strictly as an **SEO auditing and optimization assistant**.

---

# Architecture

DetIt is built with a **modular architecture** to keep the plugin scalable and maintainable.

Key architectural components implemented so far:

### Plugin Bootstrap

The plugin initializes through a central bootstrap file that loads services, registers hooks, and ensures proper dependency loading.

### Hook Registration Framework

All WordPress hooks are registered through a structured hook loader to avoid scattered `add_action` and `add_filter` calls across the codebase.

### Domain Models

The core audit pipeline is built around standardized domain models:

* **Issue** – represents a single SEO problem detected during a product audit
* **AuditResult** – represents the result of auditing a product
* **FixPlan** – describes the set of actions required to resolve detected issues

These models act as a **shared data structure between the audit engine, UI, and bulk operations**.

### Serialization Layer

Domain models include serialization helpers that allow audit results and fix plans to be safely stored and retrieved from the WordPress database.

### Security Infrastructure

A security baseline layer has been implemented to ensure all future admin actions follow WordPress security best practices.

Security utilities include:

* Capability management system
* Nonce generation and verification helpers
* Permission enforcement utilities
* Secure request input handling
* Contributor security checklist

This ensures future features such as audits, bulk fixes, and AI generation operate within a **consistent and secure request pipeline**.

---

# Planned Features

## SEO Auditing

* Title length analysis
* Meta description analysis
* Keyword usage checks
* Image ALT attribute detection
* Product SEO scoring

## SEO Content Generation

* Generate optimized product descriptions
* Generate SEO meta descriptions
* Generate keyword suggestions
* Generate image ALT text

Content generation will assist store owners in producing optimized product content quickly while maintaining control over final edits.

---

# Compatibility

DetIt is designed to work alongside major SEO plugins.

If the plugin detects any of the following, it will **not output meta tags** to avoid conflicts:

* Yoast SEO
* Rank Math
* All in One SEO

In these cases DetIt functions strictly as an **SEO auditing and optimization tool**.

---

# Usage

1. Navigate to **Products → Edit Product**.
2. Scroll to the **DetIt SEO** meta box.
3. Enter or generate:

   * Meta description
   * Focus keyword
4. Update the product.

If no external SEO plugin is active, DetIt will output the meta description on the product page.

---

# Roadmap

Upcoming development phases include:

* Product SEO audit engine
* Product SEO scoring system
* AI-assisted SEO content generation
* Image optimization checks
* Product SEO reports
* Bulk product SEO analysis and fixes

The goal of DetIt is to become a **focused SEO toolkit dedicated to WooCommerce product optimization**.

---

# Development

DetIt is currently being developed with a focus on:

* Clean architecture
* Security-first WordPress patterns
* Modular plugin design
* Scalable audit and automation systems

The project is being built incrementally with each phase introducing a new architectural component before moving to feature development.
