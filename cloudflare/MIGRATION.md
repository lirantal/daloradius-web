# daloRADIUS hosting migration

## Status and acceptance gate

Prepared on 2026-11-13 from `master` at
`6bebdecabb0149514cc9c65cf90d849024bb555f` on branch
`migrate/cloudflare-static-assets`. No existing migration PR or handoff was found.
The user authorized source preparation, scoped provisioning and preview verification.
**Present an actual deployed Git/PR preview for acceptance before production cutover.**
No source acceptance or production cutover authorization has been given.

At initial preparation, no provider resources had changed and previews were not
configured. See the verified resume section for current state.

## Verified resume — 2026-11-13

This section supersedes the initial access/provisioning observations below.

- Account `default` verified by `cf accounts list`: `72bac654168b15b354744bf1e8b6762f`.
- Active Free zone `daloradius.com`: `92444634a903b94f1bd99414649d4791`.
- Initial full DNS inventory: only apex A `76.76.21.21` and www CNAME
  `cname.vercel-dns.com`, both proxied. Both hostnames are migrate scope; all
  other services remain untouched. No DNS or production domain binding changed.
- Dedicated Worker `daloradius-web`, primary tag `70fb8364854e4146bd590ca8d90b64fb`.
- Existing SCM integration connected to `lirantal/daloradius-web` (repo 25085598).
  Production branch `master`; root `cloudflare`; Node `24.22.1`; build
  `npm ci && npm run build`; production deploy `npm run deploy`; preview deploy
  `npm run deploy:preview`; explicit verified account ID in both build environments.
- Dedicated user build token registered as `daloradius-web build token`; account
  settings read, Workers Scripts write in this account, user/membership read.
  No DNS, KV or R2 permissions. Token values remain outside Git. Other apps tokens
  were not reused or modified.
- CLI creation initially saved legacy branch settings; converted through
  `cf builds workers migrate-to-previews`. Parent `previews_enabled=true`;
  branch preview `3d35088cc38046979ee7035e66406fb7` has auto_build/auto_delete true.
- Proven Git `push_event`: commit `e9c5f8dcfcca403938f10c682f71c5ac3ee262e9`,
  build `8f92c602-ba97-469e-b1d6-eb81a86a6074`, deploy completed successfully.
  Immutable URL: https://326f3134-daloradius-web.liran-72b.workers.dev .
  Branch alias: https://migrate-cloudflare-static-assets-daloradius-web.liran-72b.workers.dev .
  The earlier legacy build was cancelled during conversion; manual previews are
  provisioning evidence only, not the automation proof.
- Dependencies restored with npm ci; local build passed. Browser baseline confirms
  Home/Community navigation and rendering, existing missing tmp slider images,
  mixed-content jQuery/fonts/vendor scripts, JS errors, and 1013px document width
  at 390px viewport. External analytics/vendor icons are proxy-blocked; not verified.
  These source issues predate migration and require explicit acceptance or later repair.
- Fresh apex/www checks still return Vercel 402 DEPLOYMENT_DISABLED. Vercel remains
  configured but is not a working rollback target. Recheck before any cutover.
- Preview acceptance is pending. Do not merge or change production DNS yet.
  The documentation update itself triggers another preview; final acceptance
  must identify its exact successful head build and immutable URL.

## Source and runtime

- `docs/` contains the established static site. GitHub Pages currently builds
  `master:/docs` with custom domain `daloradius.com`; preserve this existing setup.
- Root PHP files are historical sources. They include a remote RSS request and
  random testimonials, but the committed HTML site does not require PHP at runtime.
- No framework, package manager, lockfile, application build, database, authentication,
  queue, cron, server API, media streaming or private route was found for the static site.
- Chosen target: a dedicated `daloradius-web` Worker with Static Assets, no application
  Worker code. Check for an existing same-name resource before provisioning or deploying.
- The source static directory has 92 files, about 2.5 MB. Largest file: 405,977 bytes.
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

- Authorized account name: **default**. Its account ID is **not verified yet**.
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
Do not run it until scope/resource discovery is complete. No real deploy has been tested.

## Environment and access blocker

The initial selected executor was
`ccarenv_b64_Y2NhcmVudl8yMGY2ZjBlMzIwODQ4MTkxOGNlMTBlYzk1NWIwYzU0Yw`.
Observed revision 1 had current readiness and enforced restricted networking.
GitHub CLI authentication as `lirantal` worked. No Cloudflare secrets or outbound
identities were configured. Cloudflare CLI was initially absent and installed locally.

`https://api.cloudflare.com/client/v4/accounts` failed at proxy CONNECT with HTTP 403;
this is an environment denial, not a Cloudflare origin response. CLI device login
could not start. `cloudflare.com` alone does not allow its API subdomain.

The user will update environment settings and resume in a new session. Required
additional hosts: `api.cloudflare.com`, `dash.cloudflare.com`,
`developers.cloudflare.com`; include any additional documented OAuth host if observed.
Existing `*.workers.dev` access should cover Worker previews. Preserve existing hosts.
Freshly inspect the next executor's observed policy/readiness, then test access again.
Never ask for API tokens in chat or bypass the environment proxy.

## Local verification and limits

- `npm run build`: passed with pinned cf/Wrangler on Node 24.22.1.
- Local Wrangler asset server: `/`, `/index.html`, `/testimonials.html`, CSS, JS,
  a book-cover image and `/ads.txt` returned 200 with appropriate MIME types.
- `/missing-route`, `/index.php`, `/.git/config`, `/README.md`: true 404.
- HEAD `/`: 200, no body. HSTS present. Preview header present across tested routes
  and errors; production staging removes the preview header.
- Chromium desktop rendering, Home/Community navigation and 390px viewport inspected
  locally. External requests were deliberately aborted in that local rendering check;
  vendor integrations, jQuery-dependent interaction and live preview behavior remain
  unverified. This is not deployed acceptance.
- Existing source issues: both HTML pages reference three absent `tmp/` slider images;
  jQuery and Google Fonts use HTTP URLs, unsuitable for an HTTPS page. Several legacy
  vendor scripts/icons also use HTTP. Do not confuse them with migration regressions.
  The legacy fixed-width layout overflows a 390px viewport (document width 1013px).
  Resolve or explicitly accept these source issues during preview acceptance.
- No live Cloudflare preview, immutable deployment URL, successful Git-triggered
  Cloudflare build or production smoke test exists yet.
- GitHub Actions build checks are added for PRs and master; these do not deploy previews.
  Vercel's existing integration can report billing failures independently of this build.

## Resume checklist

1. Read the user-specified migration skill and this document. Inspect the current
   branch/PR/head and checks; resume these changes rather than recreating them.
2. Verify the new environment, provider network access, and `gh` authentication.
   Use supported `cf auth login --no-browser`; inspect command help/current schemas.
3. Verify the **default** account's actual ID; list the daloradius zone records,
   Worker resources, Vercel project and current settings. Do not reuse another app's
   Worker or other resources. Capture exact scope and missing facts in this document.
4. Provision/connect the dedicated Worker using the user's existing Cloudflare/GitHub
   SCM integration. Candidate settings, not yet applied:
   - repository `lirantal/daloradius-web`, production branch `master`
   - root `cloudflare`, Node 24.22.1, dependency install `npm ci`
   - build `npm run build`, production deploy `npm run deploy`
   - non-production deploy `npm run deploy:preview`
   - verified `CLOUDFLARE_ACCOUNT_ID` as a non-secret build variable
   - enable non-production builds and preview URLs; no production custom domains
   Inspect actual supported settings and primary Worker identity before applying.
5. Push a safe branch event after integration setup; verify the exact source SHA,
   successful automatic build and immutable deployed URL. A manual deploy is insufficient.
6. Run real HTTP/browser acceptance checks on that preview, including HTTPS dependencies,
   noindex, navigation, links/assets/errors and legacy source issues. Present the exact
   commit, immutable URL, evidence and limits for user acceptance. Stop before cutover.
7. After acceptance, verify main deployment, recheck Vercel rollback availability,
   snapshot current DNS/bindings/routes privately, and perform only the agreed hostname
   cutover. Persist final domains in configuration and verify the normal Git deployment.
