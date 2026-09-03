# Raisul Sohan (Custom WordPress Theme)

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b.svg?logo=wordpress&logoColor=white)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg?logo=php&logoColor=white)](https://php.net)
[![Version](https://img.shields.io/badge/Version-7.2.0-0080ff.svg)](style.css)
[![Zero-Plugin Architecture](https://img.shields.io/badge/Plugins-0%20(Built--in)-success.svg)](#how-it-works)
[![Responsive](https://img.shields.io/badge/Responsive-Mobile%20%26%20Desktop-brightgreen.svg)](#how-it-works)

A beautifully crafted, bespoke WordPress theme designed and built by Raisul Sohan exclusively for his personal writings, with creativity and a focus on an immersive reading experience. It features lightning-fast AJAX navigation, a distraction-free reading modal, native SEO, and a completely plugin-less architecture.

---

## Installation

1. Upload the `raisul-sohan` folder by navigating to **Appearance -> Themes -> Add New -> Upload Theme** in your WordPress dashboard, or extract it directly into `wp-content/themes/`.
2. **Activate** the theme.
3. Go to **Settings -> Permalinks**, select **Post name**, and hit **Save Changes**. This is mandatory for the individual post URLs to work correctly.
4. In **Settings -> Reading**, ensure "Your homepage displays" is set to **Your latest posts**.

From now on, simply publish your writings as standard Posts, and they will automatically appear in the beautiful grid.

---

## Settings

You can customize the theme via **Appearance -> Theme Settings**:

| Setting | Function |
|---|---|
| Posts per page, front page | Number of posts to display on the homepage (1-100) |
| Posts per page, archives | Number of posts to display in categories, tags, and search results (1-100) |
| Heading text | The typewriter heading effect. Enter multiple phrases separated by commas to rotate them, or a single phrase to keep it static. |
| Heading image | Replaces the heading text with a banner image. It automatically crops to a 1600x300 ratio. You can drag to adjust the focal point on the settings page. |
| Email | The email address copied to the clipboard when a reader clicks the email icon in the header. |
| Facebook URL | Your Facebook profile/page link. Leave empty to hide the icon. |
| LinkedIn URL | Your LinkedIn profile link. Leave empty to hide the icon. |
| Footer text | Copyright text. Use `{year}` to dynamically display the current Bengali year. |
| Google verification code | The `content` value from your Google Search Console `google-site-verification` meta tag. Leave empty to disable. |
| About page | Select a static Page to serve as the "About" content, which opens elegantly in a modal. |
| Share image | The default fallback Open Graph image used when a post is shared on social media and doesn't contain any images of its own. Needs to be 600x600 or larger. |

*Note: **Featured summary length** and **Enable smooth animations** live in the native WordPress Customizer (**Appearance -> Customize -> Featured Post Settings**), where a change can be seen against the real post as it is made. Each setting lives on exactly one screen: `rs_settings_save()` writes every field on the Theme Settings page on each save, so a setting listed on both screens would be silently overwritten by whichever was saved last.*

The site title (centered in the header) is pulled from **Settings -> General**, and the author's name in the modal is pulled from the **Display name** in **Users -> Profile**.

---

## How It Works

- **Modal Reading Experience:** Clicking on a post opens it in a distraction-free modal, while the browser URL intelligently updates to the post's actual permalink. Hitting the back button closes the modal and restores the homepage URL seamlessly.
- **Direct Access:** If a reader visits a post's URL directly or comes from a search engine, `single.php` loads the post as a full page that visually mimics the modal experience. 
- **Server-Side Search:** The search feature operates via a custom REST API endpoint (`/wp-json/rs/v1/search`), highlighting matched keywords. Whether you have 200 or 600 posts, the page weight remains incredibly light.
- **AJAX Pagination:** Clicking pagination links doesn't trigger a full page reload. Instead, the server fetches just the new list (`?rs_ajax=1`) and updates the grid instantly. The URL updates, meaning back buttons and shared links work perfectly.
- **Bengali Dates:** Dates are automatically rendered with Bengali digits. Month abbreviations are managed by the `rs_bn_months()` function.
- **Animations & Featured Posts:** The site features smooth fade-up animations on load. Readers can toggle this using the toggle button. The featured post is fetched via AJAX immediately after page load to prevent caching issues.
- **Typography Controls:** The text size controls (A- A A+) save the reader's preference in the browser's local storage for future visits.
- **Dynamic Color Themes:** Readers can choose the site's accent color. A sophisticated algorithm calculates the entire color palette based on this single choice, adjusting contrasts to guarantee a WCAG compliant 4.5:1 ratio. **No matter what color is picked, the text remains highly readable.**
- **Read History:** Posts the reader has already opened appear faded in the list. This relies purely on local browser storage (up to 30 posts) and tracks no personal data.
- **Smart Recommendations:** At the bottom of each post, three random posts from the same category are displayed, keeping content discovery fresh rather than just showing chronological next/previous posts.
- **"Read Later" Shelf (পরে পড়ব):** A bookmark button on every list row and in every post's share bar. Saved stories appear in their own list at the top of the front page, newest first, each with a button to take it back off. Stored entirely in the reader's own browser (up to 50), so it needs no server, no account, and works on a fully cached page. This is the deliberate counterpart to the two automatic lists: "already read" and "resume reading" both watch what the reader does, while this one records what they intend to do.
- **"Resume Reading" System:** If a reader leaves halfway through a post, a smart notification will appear at the top of the homepage (or via a toast if they close the modal) offering to resume exactly where they left off. 
- **Progress Bar & Time Left:** A reading progress bar tracks the scroll depth, and a dynamic "Time Left to Read" indicator actively updates as the reader scrolls.
- **Smart Copy Attribution:** Copying text from an article automatically appends the author's name and a link back to the post, provided the copied snippet is over 60 characters.
- **Reading Time & Summaries:** Automatically calculated on the first load and cached in post meta to avoid heavy server processing on archive pages.
- **Native View Counter:** The theme tracks "Readers" (unique browsers) and "Reads" (total views) natively via a REST API endpoint (`POST /wp-json/rs/v1/view/<id>`). This bypasses page caching and accurately counts real human interactions. Logged-in editors are excluded from the counts.
- **Frontend Quick Edit:** Logged-in admins can edit a post's text directly from the reading interface without jumping into the heavy backend dashboard, complete with save protections and revision history integration.
- **Auto Image Optimization:** Uploaded JPG/PNG images are natively converted to WebP, auto-resized to max 1600px width, and optimized for quality directly within the theme, entirely replacing the need for image optimization plugins.

---

## Digital Book Library

The theme includes a specialized system for tracking and displaying your reading list natively, powered by a custom `rs_book` post type.

- **Digital Bookshelf View:** Readers can toggle between a classic list and an interactive "Digital Bookshelf" (`page-book-list.php`). The shelf view renders completely in CSS (no images required). Book spine colors are procedurally generated using a hash of the book's genre, and heights vary realistically based on the length of the book title.
- **Client-Side Search & Filters:** A lightning-fast, real-time search box instantly filters books by title and author with autocomplete suggestions. You can also filter by Genre and Author dropdowns.
- **Seamless AJAX Pagination:** Book lists paginate instantly without page reloads, mimicking the smooth navigation style of the main site.
- **Native Author Management:** Instead of a complex taxonomy, authors are managed via dynamic meta-queries. The WordPress backend features a clean UI to select existing authors from a dropdown or add new ones on the fly.
- **GitHub Auto-Updater:** The theme includes a built-in self-updater (`inc/github-updater.php`) that hooks into WordPress's native update system. When a new version is pushed to GitHub, the standard "Update Available" notice appears in the dashboard — no third-party plugins required. The download is pinned to the **commit SHA**, not to the `main` branch: a branch archive is served from a GitHub cache that can still hold the previous snapshot shortly after a push, which would make WordPress install the old copy and report success. A commit's archive address changes whenever its content does, so it can never be stale.

---

## SEO Optimization

Everything you need for SEO is built right into the theme, eliminating the need for bulky plugins:

- **Open Graph & Twitter Cards:** Automatically generated for posts, homepages, categories, and tags. Descriptions are dynamically pulled from the post content.
- **Smart Share Images:** The theme looks for a featured image, then the first image inside the post, and finally falls back to the default image set in the settings. Images are sent at `medium` size to ensure compact, text-focused preview cards on social media.
- **JSON-LD Structured Data:** Automatically generates `BlogPosting` schema including titles, dates, authors, and language, plus a `BreadcrumbList` on posts and category/tag archives so results can read `raisulsohan.com › গল্প › …` instead of a bare URL. The front page also declares `WebSite` with a `SearchAction` describing the site's own search. *(Note: Google stopped rendering the sitelinks search box for `SearchAction` in late 2023. It is kept because it is accurate and other consumers still read it — the visible gain here comes from the breadcrumbs.)*
- **Smart 404 Recovery:** A stale link's own address describes what the reader wanted, so the missing page uses it. The requested slug is broken into words and compared against every published post's slug — the only reliable comparison on this site, since slugs are romanised while the writing is Bengali. Words that are ordinary *on this site* are discounted using a frequency count taken from the slugs themselves rather than an English stopword list, and only the joint-best matches are offered, never padding. Falls back to three random posts when nothing resembles the request. This covers what WordPress's own `redirect_guess_404_permalink()` cannot: a word changed in the middle, a word dropped, or two posts it could equally have been.
- **Canonical URLs:** Handled gracefully across paginated archives so they are never flagged as duplicate content.
- **Smart `noindex`:** Date and author archives are set to `noindex` to prevent diluting search rankings with duplicate homepage content.

*Note: If you activate a dedicated SEO plugin like Yoast or Rank Math, the theme's native meta tags will gracefully disable themselves to prevent duplication.*

**Manual Steps Required:** 
Submit `/wp-sitemap.xml` (natively generated by WordPress) to **Google Search Console**, ensure **Settings -> General -> Site Language** is set correctly, and clear Facebook's Sharing Debugger cache if you ever change the default share image.

---

## Good to Know

- **Self-Hosted Fonts:** Noto Serif Bengali and Noto Sans Bengali are served locally from `assets/fonts/`. No external requests are made to Google Fonts, ensuring maximum privacy and speed.
- **Asset Caching:** A small `.htaccess` inside the theme folder tells browsers to keep the stylesheet, script, fonts and images for a year, so returning readers stop re-checking every file on every page. Cache busting is handled by the `?ver=` query string, which carries `RS_VERSION` — **so bump the theme version whenever you change `style.css` or `app.js`, or returning readers will keep the old copy.** Both blocks are wrapped in `IfModule`, so a server without `mod_headers`/`mod_expires` simply ignores them.
- **Block Editor Disabled:** The theme forces the Classic Editor interface using the `use_block_editor_for_post_type` filter because the reading experience is designed for clean, prose-heavy text. 
- **Graceful Degradation:** If JavaScript is disabled, the site won't break. Modals gracefully fall back to full-page loads, and pagination links work like standard links.
- **Admin Toolbar Language:** The top admin toolbar matches your personal profile language (Users -> Profile -> Language) rather than forcing the site's frontend language, ensuring an accessible dashboard experience.

---

## File Structure

```text
raisul-sohan/
├── .htaccess          Long-lived cache headers for the theme's own assets
├── style.css          Design tokens and all stylesheets
├── functions.php      Setup, API endpoints, hooks, and backend logic
├── inc/
│   └── github-updater.php  Self-updater: checks GitHub for new versions
├── header.php         Document header
├── footer.php         Document footer and modal shells
├── index.php          Post list template
├── single.php         Single post template
├── page.php           Standard page template
├── 404.php            Error 404 template
└── assets/
    ├── app.js         Core JavaScript (modals, search, pagination, themes)
    ├── editor.css     Classic editor typography styles
    ├── fonts.css      Self-hosted @font-face declarations
    └── fonts/         Noto Serif and Noto Sans Bengali (woff2 files)
```
