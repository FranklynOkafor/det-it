# DetIt

DetIt is a lightweight SEO toolkit for WooCommerce product pages.

The plugin helps store owners **analyze, improve, and generate SEO content** for their products directly inside the WordPress product editor.

DetIt focuses specifically on product SEO rather than acting as a full-site SEO suite like Yoast or Rank Math.

---

## Features

Current MVP features include:

* SEO meta box inside the WooCommerce product editor
* Meta description editor
* Focus keyword field
* Automatic meta description output when no SEO plugin is active
* SEO plugin detection (Yoast, Rank Math, AIOSEO)
* Modular plugin architecture

Planned features:

### SEO Auditing

* Title length analysis
* Meta description analysis
* Keyword usage checks
* Image ALT attribute detection
* Product SEO scoring

### SEO Content Generation

* Generate optimized product descriptions
* Generate SEO meta descriptions
* Generate keyword suggestions
* Generate image ALT text

Content generation will assist store owners in producing optimized product content quickly while maintaining control over the final edits.

---

## Compatibility

DetIt is designed to work alongside major SEO plugins.

If the plugin detects any of the following, it will **not output meta tags** to avoid conflicts:

* Yoast SEO
* Rank Math
* All in One SEO

In these cases DetIt acts strictly as an **SEO auditing and content generation tool**.

---

## Usage

1. Open **Products → Edit Product**.
2. Scroll to the **DetIt SEO** meta box.
3. Enter or generate:

   * Meta description
   * Focus keyword
4. Update the product.

DetIt will analyze the product SEO and provide recommendations.

If no external SEO plugin is active, DetIt will output the meta description on the product page.

---

## Roadmap

Upcoming features include:

* SEO audit engine
* Product SEO scoring
* AI-assisted content generation
* Image optimization checks
* Product SEO reports
* Bulk product SEO analysis

The goal of DetIt is to become a **focused SEO toolkit for WooCommerce product optimization**.
