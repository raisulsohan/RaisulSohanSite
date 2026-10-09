# Development & Architecture Guidelines for Raisul Sohan Site

This document defines the core architectural principles, performance standards, and coding conventions for the **Raisul Sohan** personal WordPress theme, the plugin-less Bengali and English site at raisulsohan.com. Any AI agent or developer working on this codebase must strictly follow these rules.

## Read first

1. This file.
2. The router for the task under **Project map** below, then only the files it links.
3. `git status` and `git log --oneline -10`.

When documents disagree: the code, then this file, then the README (whose *What's new* sections are history).

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
- Why this works, point by point: [docs/guides/dynamic-content-on-cached-pages.md](docs/guides/dynamic-content-on-cached-pages.md).

---

## 3. Performance & Core Web Vitals

1. **Layout Stability (Zero CLS):**
   - Structural heights, placeholders, and fonts must be rock-solid.
   - Core fonts (`noto-serif-bengali-*.woff2`) are preloaded in `header.php`.
   - Never inject elements asynchronously that push existing content down.

2. **Sources, build and cache busting:**
   - CSS lives in `src/css/NN-*.css` and JavaScript in `src/js/NN-*.js`; PHP lives in `inc/NN-*.php`, loaded by `functions.php` in numeric order. `style.css` holds only the theme header. Never edit `assets/style.min.css` or `assets/app.min.js` by hand.
   - The JS parts are joined into **one closure** in name order, exactly as the old single `app.js` was, so a variable defined in an earlier part is visible in later ones. Keep that in mind when adding a part.
   - A source **without** a numeric prefix (`src/css/name.css`, `src/js/name.js`) is a page bundle: it is built on its own to `assets/name.min.css` / `assets/name.min.js` and enqueued only on the page that needs it, so heavy page-only code never reaches the rest of the site. The interactive demos are the example: each project that has one names its bundle in `rs_project_demo_kit()` (`lazylord-demo`, `lazyimage-demo`, `lazykick-demo`, `lazymotion-demo`, `lazyeditmirror-demo`), `rs_project_demo_assets()` enqueues it on that project page, and the portfolio pop-up fetches it on first use.
   - After **every** change under `src/`, run `npm run build` and commit the rebuilt `assets/*.min.*` with the sources (the theme updates itself from the repository). CI runs `npm run check` and fails if the build is stale.
   - Then bump the version in all four places, which CI also checks:
     - `functions.php`: `define( 'RS_VERSION', 'X.Y.Z' );`
     - `style.css`: `Version: X.Y.Z`
     - `package.json`: `"version": "X.Y.Z"`
     - `README.md`: `Version-X.Y.Z-0080ff.svg`
   - `RS_VERSION` acts as the `?ver=` cache buster for script and style enqueues, and a change to it also rebuilds the rewrite rules once (`rs_flush_rewrite_on_update`).

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
- Commits are Raisul Sohan's alone. Never add an AI or co-author line to a commit, pull request or release. Do the work yourself; no subagents or workflows. Reply in Bengali; code, comments, docs and commit messages are English.

---

## Commands

```
npm run build    # src/css and src/js into assets/*.min.*
npm run check    # exit 1 if the committed build is stale (CI runs this)
```

## Project map

Before opening files, read the router for the task, then only the files it points to:

- [docs/map/PRODUCT.md](docs/map/PRODUCT.md): the templates, every `inc/` file, the build, the service worker
- [docs/map/FRONTEND.md](docs/map/FRONTEND.md): every `src/css` and `src/js` part in build order, and the page bundles
- [docs/map/OPERATIONS.md](docs/map/OPERATIONS.md): build, CI, the version bump, deploy through the updater, rollback, the PWA, runtime data

The map guides what to read first. It never replaces the rules above or a release gate.

**Keep the map true.** A change that adds, moves or removes a file a router names updates that router in the same change. The whole repository is the theme the live site pulls, so routers live only in `docs/map/`, never inside `inc/`, `src/` or `assets/`, and hold nothing private. Routers stay about 15–35 lines (the front-end list is longer, on purpose), link only to files that exist, and never hold secrets. Reference material goes in `docs/`, not here; keep this file under 8 KB.
