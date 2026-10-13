import sharp from "sharp";
import { resolve } from "node:path";
import { mkdir } from "node:fs/promises";
const entries = process.argv.slice(2);
await mkdir("public/images", { recursive: true });
for (let i = 0; i < entries.length; i += 2) {
  const [name, path] = entries.slice(i, i + 2);
  if (!/^[a-z]+$/.test(name)) throw Error("Invalid asset name");
  await sharp(path)
    .resize({ width: name === "hero" ? 1600 : 800, withoutEnlargement: true })
    .jpeg({ quality: 83, mozjpeg: true })
    .toFile(resolve("public/images", name + ".jpg"));
  console.log("Optimized " + name);
}
