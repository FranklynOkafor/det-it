# DetIt

DetIt is a lightweight AI-powered content generation toolkit for WooCommerce product pages.

The plugin helps store owners generate, optimize, and manage product content directly inside the WordPress product editor.

Unlike traditional SEO plugins such as Yoast SEO or Rank Math, DetIt focuses specifically on WooCommerce product content generation and product-level SEO enhancement.

---

# Overview

DetIt is currently in active MVP development.

The plugin is being built to simplify one of the biggest problems in WooCommerce management:

* Writing optimized product content at scale
* Maintaining SEO consistency across products
* Generating product metadata efficiently
* Reducing manual product editing time

The current architecture has been designed with scalability, security, and future automation features in mind.

---

# Current MVP Features

## AI Product Content Generation

Generate optimized product content directly inside the WooCommerce product editor.

Current generation capabilities include:

* Product titles
* Short descriptions
* Long descriptions
* SEO meta descriptions
* Product tags

---

## Product SEO Assistance

DetIt currently includes lightweight SEO support features:

* Focus keyword field
* Meta description editor
* Basic product optimization workflow
* SEO plugin conflict detection

Supported SEO plugins:

* Yoast SEO
* Rank Math
* All in One SEO

If another SEO plugin is detected, DetIt disables frontend meta output to prevent conflicts and operates strictly as a content generation and optimization assistant.

---

# Architecture

DetIt is built using a modular architecture designed for long-term scalability and maintainability.

## Plugin Bootstrap System

The plugin initializes through a centralized bootstrap layer responsible for:

* Service loading
* Dependency registration
* Hook initialization
* Runtime setup

---

## Hook Registration Framework

All WordPress hooks are registered through a dedicated hook loader system to maintain clean separation of concerns and reduce scattered hook logic.

---

## Domain-Driven Core Models

DetIt uses structured domain models to support future audit, generation, and automation systems.

Current core models include:

* **Issue** – represents optimization problems detected during processing
* **AuditResult** – standardized audit result structure
* **FixPlan** – structured representation of recommended improvements

These models provide a shared contract between:

* Content generation systems
* Future audit engines
* Admin UI components
* Bulk operations

---

## Serialization Layer

Domain models include serialization helpers for safe storage and retrieval from the WordPress database.

This enables future support for:

* Cached audit results
* Background processing
* Bulk optimization queues
* AI generation history

---

## Security Infrastructure

DetIt includes a security-first request pipeline for all admin operations.

Implemented security systems include:

* Capability management
* Nonce generation and verification
* Permission enforcement utilities
* Sanitized request handling
* Contributor security guidelines

This foundation ensures future AI generation and bulk processing systems operate securely within WordPress standards.

---

# Planned Features

## AI Content Generation Expansion

Upcoming generation features include:

* Keyword suggestions
* Product attribute-based content generation
* Image ALT text generation
* Tone and style customization
* Multi-language generation support
* Bulk AI generation tools

---

## Product Optimization Tools

Planned optimization systems include:

* Product SEO scoring
* Keyword usage analysis
* Meta optimization suggestions
* Image optimization checks
* Product content quality analysis

---

## Bulk Operations

Future bulk tools will include:

* Bulk content generation
* Bulk SEO improvements
* Product optimization reports
* Product-wide content audits

---

# Compatibility

DetIt is designed to work alongside major WordPress SEO plugins.

Detected plugins include:

* Yoast SEO
* Rank Math
* All in One SEO

When another SEO plugin is active, DetIt avoids duplicate meta output and functions primarily as a WooCommerce product content generation assistant.

---

# Usage

1. Navigate to **Products → Edit Product**
2. Scroll to the **DetIt SEO** meta box
3. Generate or edit:

   * Product title
   * Short description
   * Long description
   * Meta description
   * Focus keyword
4. Save or update the product

---

# Development Philosophy

DetIt is being developed with a focus on:

* Modular architecture
* Security-first WordPress practices
* Scalable AI integration
* Maintainable code structure
* WooCommerce-focused workflows

The project is being built incrementally, with each development phase strengthening the underlying architecture before introducing larger automation systems.

---

# Roadmap

Planned development milestones include:

* AI-powered product generation improvements
* Advanced product optimization systems
* Bulk content generation
* WooCommerce SEO scoring
* Product analysis dashboards
* AI-assisted workflow automation

The long-term goal of DetIt is to become a focused WooCommerce product content generation and optimization toolkit for store owners managing products at scale.
