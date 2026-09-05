# Development & Architecture Guidelines for Raisul Sohan Site

This document defines the core architectural principles, performance standards, and coding conventions for the **Raisul Sohan** personal WordPress theme. Any AI agent or developer working on this codebase must strictly follow these rules.

---

## 1. Core Architectural Philosophy

1. **Zero-Plugin Architecture:**
   - The entire theme is strictly plugin-less. Everything—native SEO, AJAX navigation, reading modal, view counters, reading shelf, bookmarks, and portfolio—is built with bespoke, native PHP and clean vanilla JavaScript.
   - **Rule:** Never suggest or introduce external third-party WordPress plugins.

2. **Full-Page Cache Compatibility:**
   - The live site runs behind aggressive full-page caching (e.g., Cloudflare, LiteSpeed, Nginx fastcgi cache).
   - In a full-page cached environment, server-side PHP executes once during cache generation; all subsequent visitors receive the identical pre-rendered static HTML.
   - Any dynamic behavior (such as view counts, resume reading, read history, and random content selection) must be designed to work seamlessly within this caching model.

---

## 2. Dynamic Content & The Candidate Pool Pattern

### ⚠️ Anti-Pattern: No Post-Paint AJAX Swapping
- **Never** render a server placeholder and then use client-side AJAX after page load to visibly replace or swap one post with another.
- Doing so creates an annoying, amateur visual flash/swap for readers and introduces layout instability.

### ✅ The Golden Pattern: Pre-rendered Candidate Pool
- When dynamic or randomized content must appear on a cached page (e.g., the Featured Post block):
  1. Server renders a small pool of candidate items (e.g., 8 candidates via `rs_featured_pool_size()`).
  2. The first candidate renders normally; all remaining candidates render with the `hidden` attribute.
  3. An immediate, lightweight, synchronous inline `<script>` is placed directly after the container.
  4. The inline script picks a random candidate from the pool and toggles `hidden` **before the browser engine paints the region**.
- **Benefits:**
  - **Zero CLS (Cumulative Layout Shift):** The browser lays out only one active element from the start.
  - **Zero Visual Flash:** The chosen post is already set before the initial paint.
  - **Zero Network Delay:** No extra HTTP or REST API round-trip required on load.
  - **100% Cache Friendly:** Every visitor and every page reload gets a fresh random item even when served the exact same cached HTML.

---

## 3. Performance & Core Web Vitals

1. **Layout Stability (Zero CLS):**
   - Structural heights, placeholders, and fonts must be rock-solid.
   - Core fonts (`noto-serif-bengali-*.woff2`) are preloaded in `header.php`.
   - Never inject elements asynchronously that push existing content down.

2. **Asset Cache Busting:**
   - On **every** change to CSS or JavaScript files (`style.css`, `assets/app.js`, etc.), you **must** bump `RS_VERSION` across:
     - `functions.php`: `define( 'RS_VERSION', 'X.Y.Z' );`
     - `style.css`: `Version: X.Y.Z`
     - `README.md`: `Version-X.Y.Z-0080ff.svg`
   - `RS_VERSION` acts as the `?ver=` cache buster for script and style enqueues.

---

## 4. Bilingual & Localization Rules

1. **Multisite Bilingual Support:**
   - Check `rs_is_en()` for language detection (returns `true` for English subsite or English locales).
2. **Bengali Typography & Formatting:**
   - Always convert digits using `rs_bn_digits()` for Bengali output.
   - Use `rs_bn_date()` for dates and month names in Bengali.
   - Support proper dropcaps and virama-linked complex Bengali glyphs when shortening or summarizing text.

---

## 5. Git Commit Style

- Commit messages must follow the concise, semantic project convention:
  ```text
  v<version>: <Clear, high-level summary of what and why>
  ```
  *Example:* `v7.4.69: Pre-render featured post candidate pool and pick randomly via inline script to eliminate layout shift and visible swapping`
