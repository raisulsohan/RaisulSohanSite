# Dynamic content on cached pages

The rule is in [AGENTS.md § 2](../../AGENTS.md#2-dynamic-content--the-candidate-pool-pattern): never swap content after paint; pre-render a candidate pool and let a synchronous inline script pick one before the browser paints. This page keeps the reasoning that used to sit beside the rule.

## Benefits of the pre-rendered candidate pool

- **Zero CLS (Cumulative Layout Shift):** The browser lays out only one active element from the start.
- **Zero Visual Flash:** The chosen post is already set before the initial paint.
- **Zero Network Delay:** No extra HTTP or REST API round-trip required on load.
- **100% Cache Friendly:** Every visitor and every page reload gets a fresh random item even when served the exact same cached HTML.

## Where it is used

- The featured post on the front page: the pool size comes from `rs_featured_pool_size()` in `inc/08-the-post-list.php`, and the inline picker sits directly after the container.
- The REST endpoints in `inc/09-rest-endpoints.php` are the counterpart for what a cached page cannot carry at all: view counts and other per-visitor state travel through them after paint, never replacing rendered content.
