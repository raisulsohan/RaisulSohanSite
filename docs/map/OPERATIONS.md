# Map — operations

| Task | Where | Notes |
|---|---|---|
| Install on a site | [../../README.md#installation](../../README.md#installation) | Upload the theme folder once; permalinks set to *Post name*; the homepage shows the latest posts. From then on the theme updates itself from this repository |
| Local setup | [../../README.md#building-the-assets](../../README.md#building-the-assets) | `npm install` for the build only; the theme runs as it is on any WordPress with PHP 8.0+ |
| Build | `scripts/build.js` | `npm run build` after every change under `src/`; commit `assets/*.min.*` with the sources |
| Checks before a PR | `.github/workflows/ci.yml` | `npm run check` (stale build), `php -l` on every file, and the version in `functions.php`, `style.css` and `package.json` in step; CI runs all three on every push to `main` and every pull request |
| Version bump | AGENTS.md § 3 | Four places: `RS_VERSION`, `style.css`, `package.json`, the README badge. `RS_VERSION` busts the asset cache and flushes rewrite rules once |
| Deploy | `inc/github-updater.php` | A push to `main` is the deploy: the live site's updater checks GitHub and installs the new version; what is committed is what the site serves |
| What ships | the repository | The whole tree at the commit, `README.md`, `docs/` and `scripts/` included; `node_modules/` is git-ignored |
| Rollback | `main` | Revert on `main` and let the updater pick it up, or re-upload an older version by hand |
| Health check | the page source | Assets carry `?ver=RS_VERSION`; `sw.js` is served with the theme version in `RS_SW_CONFIG`, so a stale service worker shows the old number |
| Configuration | Appearance › Theme Settings, the Customizer | Theme mods per site; each setting lives on exactly one screen. Nothing in the repository holds a site's settings |
| Secrets | none | No keys in the repository; the updater reads a public repository |
| External services | `inc/github-updater.php`, `inc/16-project-documentation.php` | GitHub for updates, and GitHub's contents and Markdown APIs for each portfolio project's documentation; the two editions are two sites in one WordPress network |
| Runtime data outside Git | `inc/09-rest-endpoints.php`, `inc/11-read-counts-in-the-admin.php` | Readers and reads in post meta through the REST endpoint; reading time and summaries cached in post meta; the reader's own history, shelf and preferences in their browser only |
| The PWA | `inc/14-progressive-web-app.php`, `sw.js`, `offline.html` | The manifest and the service worker with its pre-cached shell; a new theme version refreshes it |
| The portfolio's documentation pages | `inc/16-project-documentation.php` | Every top-level `docs/*.md` in a project's repository becomes a page under `/portfolio/<project>/documentation/`; a `docs/` without a README gets a made home page. Subfolders such as `docs/map/` are not read |
