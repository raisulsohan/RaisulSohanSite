# Map — front-end sources

`scripts/build.js` joins every numbered `src/css/NN-*.css` in name order into `assets/style.min.css`, and every numbered `src/js/NN-*.js` into `assets/app.min.js` as **one closure**, so an earlier part's variables are visible to later ones. A source without a number is a page bundle built on its own and enqueued only where it is needed. Each file is named for what it does; the tables list them in build order.

## `src/css/`

| Part | What it styles |
|---|---|
| `01-tokens.css`, `02-reset.css` | Design tokens (colours, the reader-picked accent, spacing); the reset |
| `03-header.css`, `04-hero-typewriter.css` | The header; the typewriter heading and heading image |
| `05-post-list.css`, `06-tooltip.css`, `07-font-size-controls.css`, `08-scroll-to-top.css`, `09-footer.css` | The post grid; tooltips; A- A A+; scroll to top; the footer |
| `10-modals.css`, `11-article-shared-by-modal-and-single-php.css`, `12-about-modal.css`, `13-search-modal.css`, `14-toast.css` | The modal shells; the article as shown both in the modal and by `single.php`; the About modal; the search modal; toasts |
| `15-single-post-page-direct-visit-crawler.css`, `16-archive-404.css`, `17-responsive.css`, `18-motion-and-print.css` | A post opened at its own address; archives and 404; breakpoints; motion and print |
| `19-quote-card.css`, `20-install-bar.css` | The quote-to-image card; the PWA install bar |
| `21-portfolio-page.css`, `22-case-study-pop-up-modal.css`, `23-story-timeline.css`, `24-command-palette.css`, `25-project-documentation.css` | The portfolio page; the case-study pop-up; the story timeline; the command palette; project documentation pages |
| `fonts.css` | Page bundle: the self-hosted `@font-face` declarations |
| `lazylord-demo.css`, `lazyimage-demo.css`, `lazykick-demo.css`, `lazymotion-demo.css` | Page bundles: one interactive demo per project, loaded only on that project's page |

## `src/js/`

| Part | What it does |
|---|---|
| `00-intro.js`, `01-small-helpers.js` | The opening of the closure; small shared helpers |
| `02-read-count.js`, `03-toasts.js`, `04-clipboard.js` | The view counter through the REST endpoint; toasts; clipboard helpers |
| `05-handing-a-story-on.js`, `06-reader-font-size.js`, `07-hero-heading.js`, `08-hover-summary.js` | The share bar; the reader's font size in local storage; the typewriter heading; the summary on hover |
| `09-overlays.js`, `10-post-modal.js`, `11-remembering-where-the-reader-stopped.js`, `12-arrow-keys-move-between-posts-while-the-modal-is-open.js` | Overlays; the reading modal with the URL swap; resume reading; arrow keys between posts |
| `13-search.js`, `14-page-links.js`, `15-scroll-to-top.js` | Server-side search with highlighting; AJAX pagination; scroll to top |
| `16-a-colour-the-reader-picks.js`, `17-dark-light-toggle.js`, `18-scroll-to-top-inside-the-post-modal.js` | The accent colour and its WCAG-safe palette; dark and light; scroll to top inside the modal |
| `19-reading-a-post-at-its-own-address.js`, `20-you-were-reading.js`, `21-a-shelf-the-reader-stocks-on-purpose.js` | A post opened directly; the resume-reading notice; the *পরে পড়ব* shelf |
| `22-carry-the-source-along-with-copied-text.js`, `23-quote-to-image-card.js`, `24-editing-a-post-where-it-is-read.js` | Copy attribution; the quote card; front-end quick edit for logged-in admins |
| `25-service-worker-helper.js`, `26-register-the-service-worker.js`, `27-pwa-installation-prompt.js` | The PWA: helper, registration, the install prompt |
| `28-command-palette.js`, `29-the-other-language-on-a-story-page.js` | The command palette; the switch to the other edition on a story page |
| `lazylord-demo.js`, `lazyimage-demo.js`, `lazykick-demo.js`, `lazymotion-demo.js` | Page bundles: the interactive demos, each running its project's own code; fetched by the portfolio pop-up on first use |

After any change here: `npm run build`, commit `assets/*.min.*` with the sources, bump the version in the four places (AGENTS.md § 3).
