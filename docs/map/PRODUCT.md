# Map — product code

Start here for any change. The theme root is the WordPress theme folder: templates at the top level, the PHP in `inc/` loaded by `functions.php` in numeric order, sources in `src/` built into `assets/`. The front-end parts have their own router: [FRONTEND.md](FRONTEND.md). The tree as the README draws it: [../../README.md#file-structure](../../README.md#file-structure).

| Folder or file | What lives there | Read before changing |
|---|---|---|
| `functions.php` | `RS_VERSION`, constants, then every `inc/NN-*.php` in order | [OPERATIONS.md](OPERATIONS.md) |
| `inc/01-theme-setup.php`, `02-assets.php` | Theme setup, `rs_is_en()` and `rs_flush_rewrite_on_update`; the enqueues with `RS_VERSION` as cache buster | AGENTS.md § 3 |
| `inc/03-bengali-numbers-and-dates.php`, `04-text-helpers.php` | `rs_bn_digits()`, `rs_bn_date()`, `rs_bn_months()`; summaries, dropcaps and complex Bengali glyphs | AGENTS.md § 4 |
| `inc/05-theme-settings.php` | Appearance › Theme Settings; `rs_settings_save()` writes every field on that page, so a setting lives on exactly one screen | [../../README.md#settings](../../README.md#settings) |
| `inc/06-icons.php`, `07-share-links.php` | Inline icons; the share bar's links | |
| `inc/08-the-post-list.php` | The post list, AJAX pagination (`?rs_ajax=1`), the featured-post candidate pool (`rs_featured_pool_size()`) | AGENTS.md § 2 |
| `inc/09-rest-endpoints.php` | `/wp-json/rs/v1/search`, `/view/<id>` and the other endpoints that work past the page cache | |
| `inc/10-seo-meta.php` | Native SEO: meta tags, Open Graph, the share image, both languages | [../../README.md#seo-optimization](../../README.md#seo-optimization) |
| `inc/11-read-counts-in-the-admin.php`, `12-housekeeping.php` | Readers and reads in the admin; housekeeping | |
| `inc/13-book-list.php` | The `rs_book` post type and the digital bookshelf | [../../README.md#digital-book-library](../../README.md#digital-book-library) |
| `inc/14-progressive-web-app.php` | The manifest, and `sw.js` served with `RS_SW_CONFIG` prepended (theme version, URI, shell assets) | |
| `inc/15-the-other-language.php` | The two editions as two sites in one network; `rs_is_en()`; a story written in both | AGENTS.md § 4 |
| `inc/16-project-documentation.php`, `parts/project-docs.php` | Each portfolio project's `docs/` read from GitHub and rendered at `/portfolio/<project>/documentation/`: every top-level `docs/*.md` becomes a page, and a subfolder with its own `README.md` is a book whose files are pages too (`rs_docs_books()`; chapters listed under the book in the sidebar; with no docs README the books come first and the Documentation link opens the first one, `rs_project_docs_entry_url()`) | |
| `inc/17-cv.php`, `page-cv.php` | The CV at `/cv/`: both editions keep their own URL and seeded page but read the same network option (`rs_cv_shared_data_v1`). The native editor controls CV text, repeatable lists, labels, multiple links per tool and the selected PDF; legacy `_rs_cv_data` page meta seeds the shared data on upgrade, and the bundled PDF remains the fallback | [FRONTEND.md](FRONTEND.md) (`cv.css`, admin-only `cv-editor.css` and `cv-editor.js`) |
| `inc/portfolio-cpt.php`, `inc/github-updater.php` | The portfolio post type, meta boxes and admin reorder, the case studies and the demo page bundles (`rs_project_demo_kit()`, `rs_project_demo_assets()`); the self-updater that checks GitHub for new versions | AGENTS.md § 3 |
| `header.php`, `footer.php` | Document head with the preloaded fonts; footer and the shared modal shells | AGENTS.md § 3 |
| `index.php`, `single.php`, `page.php`, `404.php` | The post list; a post as a full page (direct visits and crawlers); a page; *Missing Footage* | |
| `page-index.php`, `page-portfolio.php`, `page-book-list.php`, `page-timeline.php`, `page-cv.php` | Page templates: Index, Portfolio (with the case-study pop-up and documentation), Book List, Story Timeline, CV | |
| `sw.js`, `offline.html` | The service worker and its offline fallback | |
| `scripts/build.js` | Joins and minifies `src/` into `assets/`; `--check` compares with the committed build | [FRONTEND.md](FRONTEND.md) |
| `assets/` | `style.min.css` and `app.min.js` (built, committed, never edited), the page bundles (`cv.min.css`, admin-only `cv-editor.min.css` and `cv-editor.min.js`, `*-demo.min.*`), `editor.css`, `fonts.css`, `fonts/`, `img/`, `demo/`, `cv/` (the bundled CV PDF, printed from `docs/cv/cv-pdf.html`; the steps are in that file) | |
| `.github/workflows/ci.yml` | PHP syntax on 8.0–8.3, built assets match sources, the version in step | [OPERATIONS.md](OPERATIONS.md) |
