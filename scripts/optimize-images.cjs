/* One-time release helper. Usage:
   node scripts/optimize-images.cjs <path-to-node_modules>
   Requires the `sharp` package in that node_modules directory. */

const fs = require('node:fs');
const path = require('node:path');

const moduleRoot = process.argv[2];
if (!moduleRoot) throw new Error('Pass the node_modules directory containing sharp.');
const sharp = require(path.join(path.resolve(moduleRoot), 'sharp'));

const imageDir = path.resolve(__dirname, '..', 'public_html', 'assets', 'images', 'banners');
const names = ['welcome', 'medicines', 'skincare', 'active'];

(async () => {
  for (const name of names) {
    for (const size of ['desktop', 'mobile']) {
      const input = path.join(imageDir, `banner-${name}-${size}.png`);
      const output = path.join(imageDir, `banner-${name}-${size}.webp`);
      if (!fs.existsSync(input)) throw new Error(`Missing source: ${input}`);
      const width = size === 'desktop' ? 1600 : 900;
      await sharp(input)
        .resize({ width, withoutEnlargement: true })
        .webp({ quality: 84, effort: 6, smartSubsample: true })
        .toFile(output);
      const before = fs.statSync(input).size;
      const after = fs.statSync(output).size;
      process.stdout.write(`${path.basename(output)} ${Math.round(before / 1024)}KB -> ${Math.round(after / 1024)}KB\n`);
    }
  }
})().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
