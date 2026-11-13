import { cp, mkdir, readdir, rm, stat, writeFile } from "node:fs/promises";
import { fileURLToPath } from "node:url";
import path from "node:path";

const root = fileURLToPath(new URL("../", import.meta.url));
const output = fileURLToPath(new URL("./public/", import.meta.url));
await rm(output, { recursive: true, force: true });
await mkdir(output, { recursive: true });
// Publish the established static site, never the historical PHP sources or repo metadata.
for (const entry of ["index.html", "testimonials.html", "license.txt", "css", "js", "img", "images"]) {
  await cp(path.join(root, "docs", entry), path.join(output, entry), { recursive: true });
}
await cp(path.join(root, "ads.txt"), path.join(output, "ads.txt"));
await writeFile(path.join(output, "_redirects"), "/ /index.html 200\n");
const preview = process.argv.includes("--preview");
await writeFile(path.join(output, "_headers"),
  "/*\n  Strict-Transport-Security: max-age=63072000\n" +
  (preview ? "  X-Robots-Tag: noindex, nofollow\n" : ""));
let count = 0;
let largest = 0;
async function audit(directory) {
  for (const entry of await readdir(directory, { withFileTypes: true })) {
    const filename = path.join(directory, entry.name);
    if (entry.isDirectory()) await audit(filename);
    else {
      const { size } = await stat(filename);
      if (size > 25 * 1024 * 1024) throw new Error(`Asset exceeds 25 MiB: ${filename}`);
      largest = Math.max(largest, size);
      count++;
    }
  }
}
await audit(output);
if (count > 20000) throw new Error(`Asset count exceeds Free plan limit: ${count}`);
console.log(`Staged ${count} files; largest ${largest} bytes; preview=${preview}`);
