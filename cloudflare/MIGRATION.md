# daloRADIUS hosting migration

## Status and acceptance gate

Prepared on 2026-11-13 from `master` at
`6bebdecabb0149514cc9c65cf90d849024bb555f` on branch
`migrate/cloudflare-static-assets`. PR #3 was later merged by the user to `master`
as `e192724`.

The user reviewed the Git preview at
`https://d3337f13-daloradius-web.liran-72b.workers.dev` (source commit
`0ff0e8b9f201fab90013ac01991c6804722c0615`) and requested fixes for the carousel
screenshots, controls, and User Guide / VM alignment. The current correction is being
prepared on `fix/preview-ui`; present its automatic Git preview for acceptance.
The Cloudflare account named `default` and automatic Git previews were verified in the
prior session. Production DNS is unchanged. Vercel returns 402, so it is not currently
a usable rollback path.

**Present the corrected deployed Git preview for acceptance before production cutover.**
No preview acceptance or production cutover authorization has been given.

A dedicated `daloradius-web` Worker and Git integration are in use for previews. No
production domain binding or DNS record has been changed by this migration.

## Source and runtime

- `docs/` contains the established static site. GitHub Pages currently builds
  `master:/docs` with custom domain `daloradius.com`; preserve this existing setup.
- Root PHP files are historical sources. They include a remote RSS request and
  random testimonials, but the committed HTML site does not require PHP at runtime.
- No framework, package manager, lockfile, application build, database, authentication,
  queue, cron, server API, media streaming or private route was found for the static site.
- Chosen target: a dedicated `daloradius-web` Worker with Static Assets, no application
  Worker code. Check for an existing same-name resource before provisioning or deploying.
- The source static directory now has 93 files, about 2.6 MB. Largest file: 405,977 bytes.
  Current documented Workers limits checked 2026-11-13: 25 MiB per asset;
  20,000 assets on Free / 100,000 on Paid. This site fits the Free asset limits.
  Reference: https://developers.cloudflare.com/workers/platform/limits/
- `stage.mjs` copies the site's HTML, CSS, JS and images plus root `ads.txt` into
  generated `public/`. It excludes README, GitHub Pages CNAME, the empty `common.html`,
  PHP and repository internals. It also checks asset limits.
- Existing content, analytics and vendor purchase/download links remain unchanged;
  no new tracking was added. Keep Vercel's existing source/deployment setup for rollback.

## HTTP contract

- `/` internally rewrites to `/index.html`; both return HTML without changing location.
- `/testimonials.html` retains its explicit extension and serves the Community page.
  HTML auto-normalization is disabled to avoid inventing extensionless redirects.
- Assets retain their public paths. `ads.txt` is staged explicitly from repo root.
- Missing paths return true 404s, without an SPA fallback.
- Preserve observed Vercel HSTS (`max-age=63072000`).
- Preview staging adds `X-Robots-Tag: noindex, nofollow`; normal staging removes it.
- Apex/www canonical redirect behavior could not be established because both current
  origins return 402. Reconcile Vercel settings and the intended canonical hostname
  before binding production domains. No www redirect has been invented in this change.

## Account, hostnames and rollback scope

- Authorized account name: **default**. The account was verified in the prior session;
  its ID is intentionally omitted from this handoff.
- Zone ID, complete DNS inventory, zone overrides, Worker IDs/tags and plan: unknown.
- Intended migration: `daloradius.com` and its website alias `www.daloradius.com`.
  Confirm the alias and current bindings during provider discovery.
- Preserve all MX/TXT/mail, other hostnames, services and unrelated applications.
  No full DNS inventory was possible, so other subdomains are not classified yet.
- Vercel project observed in the repository's latest Vercel status:
  `liran-tals-projects/daloradius-web`.
- At about 07:37 UTC on 2026-11-13, fresh HTTPS requests to both apex and www returned
  **402 `DEPLOYMENT_DISABLED`** through Cloudflare. Latest GitHub Vercel deployment
  status was also failure, for `daloradius-p970wzays-liran-tals-projects.vercel.app`.
  Retaining this project does not currently provide usable rollback.
- Recheck Vercel before cutover; resolve the restriction or obtain the user's informed
  decision before relying on Cloudflare as the only available host.
- Before any DNS mutation read the skill's cutover reference, save fresh private DNS,
  binding and route snapshots, and prepare exact website-only restoration records.
  Never restore a whole historical zone or change mail/unrelated records.

## Reproduce locally

Use Node 24.22.1 (tested) or the package's supported engines:

```sh
cd cloudflare
npm ci
npm run build
npm run dev -- --port 8787
```

The pinned official `cf` CLI delegates the static build to pinned Wrangler.
`cloudflare.config.ts` contains runtime/hosting settings; `wrangler.config.ts`
contains only `assetsDirectory`. No framework build or tool autodetection is needed.
Dependencies, generated assets/build output and `.env` files are ignored by Git.

Deployment scripts refuse to run without an explicit 32-hex-digit
`CLOUDFLARE_ACCOUNT_ID`. This prevents falling through to a CLI default;
**the caller must verify that the ID belongs to the account named default**.

After verification and supported OAuth login:

```sh
npm run deploy:preview
# Production only at the agreed stage:
npm run deploy
```

The production deployment command still has no production custom domains configured.
Do not run it until scope/resource discovery is complete. No production deploy has been run.

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
- The earlier live preview and Git-triggered deployment were verified. Acceptance of the
  corrected branch preview is still pending; production cutover remains unauthorized.
- GitHub Actions build checks are added for PRs and master; these do not deploy previews.
  Vercel's existing integration can report billing failures independently of this build.

## Resume checklist

1. Confirm the automatic preview for the latest `fix/preview-ui` commit, including its
   source SHA, HTTPS behavior, image assets, carousel controls, navigation, noindex header
   and true 404s. Present the deployed URL for user acceptance.
2. Stop before production cutover until the user accepts the preview. Before cutover,
   verify the account and Worker identity, capture a fresh website-only DNS/binding
   snapshot, and recheck whether Vercel can serve as rollback.
3. Change only the agreed website hostname records after acceptance; preserve mail,
   unrelated hostnames and services. Verify the normal Git deployment after cutover.
