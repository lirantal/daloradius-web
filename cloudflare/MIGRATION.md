# daloRADIUS hosting migration

## Current status

Updated on 2026-11-13 after PR #5 merged to `master` as `bfb794c`. The user accepted
the corrected preview and authorized production deployment. The `daloradius-web`
Worker and its production hostname routes are deployed from `master`.

The verified Cloudflare account is named `default`. The active Free zone has exactly
two DNS records: the proxied apex A record and the proxied `www` CNAME. There are no
other DNS records in the zone. A fresh private DNS export, Worker settings snapshot,
and route snapshot were captured before this change. The zone had no Worker routes,
and `daloradius-web` had no additional bindings. Vercel still returns 402
`DEPLOYMENT_DISABLED`, so it is not a working rollback origin if the routes are removed.

The production setup uses Worker Routes for `daloradius.com/*` and
`www.daloradius.com/*`. Both DNS records are already proxied, so the routes can serve
the site without replacing DNS records or changing unrelated DNS. Both hostnames serve
the same content; no canonical redirect is added.

## Source and runtime

- `docs/` contains the established static site. GitHub Pages currently builds
  `master:/docs` with custom domain `daloradius.com`; preserve this existing setup.
- Root PHP files are historical sources. They include a remote RSS request and
  random testimonials, but the committed HTML site does not require PHP at runtime.
- No framework, package manager, lockfile, application build, database, authentication,
  queue, cron, server API, media streaming or private route was found for the static site.
- Target: a dedicated `daloradius-web` Worker with Static Assets and no application
  Worker code. The existing Worker was verified before deployment.
- The source static directory now has 93 files, about 2.6 MB. Largest file: 405,977 bytes.
  Current documented Workers limits checked 2026-11-13: 25 MiB per asset;
  20,000 assets on Free / 100,000 on Paid. This site fits the Free asset limits.
  Reference: https://developers.cloudflare.com/workers/platform/limits/
- `stage.mjs` copies the site's HTML, CSS, JS and images plus root `ads.txt` into
  generated `public/`. It excludes README, GitHub Pages CNAME, the empty `common.html`,
  PHP and repository internals. It also checks asset limits.
- Existing content, analytics and vendor purchase/download links remain unchanged;
  no new tracking was added. Keep Vercel's source/deployment setup for reference.

## HTTP contract

- `/` internally rewrites to `/index.html`; both return HTML without changing location.
- `/testimonials.html` retains its explicit extension and serves the Community page.
  HTML auto-normalization is disabled to avoid inventing extensionless redirects.
- Assets retain their public paths. `ads.txt` is staged explicitly from repo root.
- Missing paths return true 404s, without an SPA fallback.
- Preserve observed Vercel HSTS (`max-age=63072000`).
- Preview staging adds `X-Robots-Tag: noindex, nofollow`; normal staging removes it.
- Apex/www canonical redirect behavior could not be established because both Vercel
  origins return 402. Both hostnames therefore serve the site directly, without an
  invented `www` redirect.

## Account, hostnames and rollback scope

- Verified account name: **default**; account and zone IDs are intentionally omitted.
- The zone is active on the Free plan. Its complete DNS inventory contains only the
  website apex A record and `www` CNAME, both proxied. The `www` alias is confirmed.
- No Worker route existed before this change. The private snapshots preserve the
  existing DNS records and Worker settings for targeted rollback.
- Vercel project observed in the repository's latest Vercel status:
  `liran-tals-projects/daloradius-web`.
- Fresh HTTPS requests immediately before routing both hostnames returned **402
  `DEPLOYMENT_DISABLED`**. The preserved DNS targets lead back to Vercel, which
  currently cannot serve the site if the Worker routes are removed.
- DNS is not changed by the Worker Route cutover. To roll back routing, remove only
  these two route patterns and apply the Worker triggers again. The original DNS values
  remain in Cloudflare.

## Reproduce locally

Use Node 24.22.1 (tested) or the package's supported engines:

```sh
cd cloudflare
npm ci
npm run build
npm run dev -- --port 8787
```

The pinned official `cf` CLI delegates the static build to pinned Wrangler.
`cloudflare.config.ts` contains runtime, hosting and route trigger settings;
`wrangler.config.ts` contains the Static Assets build settings. No framework build or
tool autodetection is needed.
Dependencies, generated assets/build output and `.env` files are ignored by Git.

Deployment scripts refuse to run without an explicit 32-hex-digit
`CLOUDFLARE_ACCOUNT_ID`. This prevents falling through to a CLI default;
**the caller must verify that the ID belongs to the account named default**.

After verifying the account and authenticating with the supported OAuth login:

```sh
npm run deploy:preview
# Production only at the agreed stage:
npm run deploy
```

The production command deploys the Worker and then applies its configured route
triggers. Preview deployment does not apply production routes.

## Environment and access

The first executor initially denied Cloudflare API access at proxy CONNECT. The user
later verified the `default` account and working Git previews through the configured
integration. GitHub CLI authentication as `lirantal` is available in this workspace.
Do not store tokens or secrets in this document or ask for them in chat.

## Local verification and limits

- `npm run build`: passed with pinned cf/Wrangler on Node 24.22.1.
- Local Wrangler asset server: `/`, `/index.html`, `/testimonials.html`, CSS, JS,
  a book-cover image and `/ads.txt` returned 200 with appropriate MIME types.
- `/missing-route`, `/index.php`, `/.git/config`, `/README.md`: true 404.
- HEAD `/`: 200, no body. HSTS present. Preview header present across tested routes
  and errors; production staging removes the preview header.
- Local Playwright review verified that all three slider screenshots load, Next and
  Prev change slides, and the User Guide / VM action buttons share a baseline on wide
  layouts. The document fits 320px through desktop widths; jQuery is served locally and
  Google Fonts now use HTTPS.
- Several legacy ad, stats and customer-icon resources still use HTTP and were not
  verified. Their behavior remains outside this correction.
- The corrected live preview and Git-triggered deployment were accepted and verified.
  The production hostname routes are managed in `cloudflare.config.ts`.
- GitHub Actions build checks are added for PRs and master; these do not deploy previews.
  Vercel's existing integration can report billing failures independently of this build.

## Production verification

- Confirmed both `https://daloradius.com/` and `https://www.daloradius.com/` return 200.
- Confirmed `/testimonials.html`, the VM image, a screenshot image, jQuery and CSS
  return 200 on both hostnames.
- Confirmed HSTS is `max-age=63072000`, the preview `X-Robots-Tag` is absent, and a
  missing path returns 404 on both hostnames.
- Confirmed the apex A record and `www` CNAME are unchanged; both remain proxied.
