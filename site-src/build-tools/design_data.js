// Prints a JS array literal from a design page as JSON: node design_data.js <file> <start marker> 
const fs = require('fs');
const [file, marker] = process.argv.slice(2);
const s = fs.readFileSync(file, 'utf8');
const i = s.indexOf(marker); if (i < 0) { console.error('marker not found'); process.exit(1); }
let j = s.indexOf('[', i), depth = 0, k = j, q = null;
for (; k < s.length; k++) {
  const c = s[k];
  if (q) { if (c === '\\') { k++; continue; } if (c === q) q = null; continue; }
  if (c === "'" || c === '"' || c === '`') { q = c; continue; }
  if (c === '[') depth++; else if (c === ']') { depth--; if (!depth) break; }
}
console.log(JSON.stringify(eval('(' + s.slice(j, k + 1) + ')')));
