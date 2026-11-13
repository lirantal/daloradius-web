import { defineConfig } from "cf/config";

export default defineConfig({
  // Set CLOUDFLARE_ACCOUNT_ID only after verifying the account named "default".
  accountId: process.env.CLOUDFLARE_ACCOUNT_ID,
  worker: {
    name: "daloradius-web",
    compatibilityDate: "2026-11-13",
    assets: {
      htmlHandling: "none",
      notFoundHandling: "none",
    },
    workersDev: true,
    previewUrls: true,
    // Production domains are deliberately absent until preview acceptance.
  },
});
