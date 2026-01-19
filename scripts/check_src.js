const fs = require("fs");
const path = require("path");

const root = path.join(process.cwd(), "src");
const patterns = [
  /#[0-9A-Fa-f]{3,6}/,
  /rgb\(/i,
  /hsl\(/i,
  /\b(px|rem|em|vh|vw)\b/i,
];

function walk(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const ent of entries) {
    const full = path.join(dir, ent.name);
    if (ent.isDirectory()) walk(full);
    else if (ent.isFile()) checkFile(full);
  }
}

function checkFile(filePath) {
  const txt = fs.readFileSync(filePath, "utf8");
  for (const pat of patterns) {
    const m = txt.match(pat);
    if (m) {
      console.error("Guard violation in", filePath, "pattern:", pat);
      process.exitCode = 1;
    }
  }
}

if (!fs.existsSync(root)) {
  console.log("No src/ directory found — nothing to check.");
  process.exit(0);
}

walk(root);

if (process.exitCode === 1) {
  console.error("Raw values found in src/. Aborting.");
  process.exit(1);
} else {
  console.log("src/ scan passed — no raw color/length values found.");
}
