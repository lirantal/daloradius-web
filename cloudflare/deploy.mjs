import { spawnSync } from "node:child_process";

if (!/^[a-f0-9]{32}$/i.test(process.env.CLOUDFLARE_ACCOUNT_ID ?? "")) {
  throw new Error("Set CLOUDFLARE_ACCOUNT_ID to the verified ID of the account named default before deploying.");
}
const preview = process.argv.includes("--preview");
for (const [command, args] of [
  [process.execPath, ["stage.mjs", ...(preview ? ["--preview"] : [])]],
  ["cf", preview ? ["previews", "deploy"] : ["deploy"]],
]) {
  const result = spawnSync(command, args, { stdio: "inherit" });
  if (result.error) throw result.error;
  if (result.status !== 0) process.exit(result.status ?? 1);
}

if (!preview) {
  const result = spawnSync("cf", ["workers", "triggers", "deploy", "--worker", "daloradius-web"], {
    stdio: "inherit",
  });
  if (result.error) throw result.error;
  if (result.status !== 0) process.exit(result.status ?? 1);
}
