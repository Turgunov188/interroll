// Parametric shapes for the product page: technical drawing (SVG) + CSS 3D model.
// Parameters and their labels come from the server (App\Enums\Configurator::parameters()).
// Every shape is a prism (polygon × depth), so one 3D builder serves all of them.
const IRShapes = (function () {
  const INK = '#111418', MUTED = '#5B636D', HUB = '#8A939D', HOLE = '#1E2227';
  const r1 = (n) => Math.round(n * 10) / 10;
  const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));

  // ---------- 2D drawing helpers (viewBox 640 × 380)
  const defs = '<defs><pattern id="h" width="7" height="7" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">'
    + '<rect width="7" height="7" fill="#FFF"/><line x1="0" y1="0" x2="0" y2="7" stroke="' + INK + '" stroke-width="1.2"/></pattern></defs>';
  const txt = (x, y, s, anchor, size) => '<text x="' + r1(x) + '" y="' + r1(y) + '" text-anchor="' + (anchor || 'middle') + '" font-family="JetBrains Mono, monospace" font-size="' + (size || 13) + '" fill="' + INK + '">' + s + '</text>';
  const line = (x1, y1, x2, y2, extra, color) => '<line x1="' + r1(x1) + '" y1="' + r1(y1) + '" x2="' + r1(x2) + '" y2="' + r1(y2) + '" stroke="' + (color || INK) + '" stroke-width="1" ' + (extra || '') + '/>';
  const axis = (x1, y1, x2, y2) => line(x1, y1, x2, y2, 'stroke-dasharray="14 4 2 4"', MUTED);
  const dimH = (x1, x2, y, label) => line(x1, y, x2, y) + line(x1, y - 6, x1, y + 6) + line(x2, y - 6, x2, y + 6) + txt((x1 + x2) / 2, y - 8, label);
  const dimV = (x, y1, y2, label) => line(x, y1, x, y2) + line(x - 6, y1, x + 6, y1) + line(x - 6, y2, x + 6, y2)
    + '<text transform="translate(' + r1(x - 10) + ' ' + r1((y1 + y2) / 2) + ') rotate(-90)" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="13" fill="' + INK + '">' + label + '</text>';
  const circle = (cx, cy, r, fill, extra) => '<circle cx="' + r1(cx) + '" cy="' + r1(cy) + '" r="' + r1(Math.max(r, 0.5)) + '" fill="' + fill + '" stroke="' + INK + '" stroke-width="1.6" ' + (extra || '') + '/>';
  const rect = (x, y, w, h, fill) => '<rect x="' + r1(x) + '" y="' + r1(y) + '" width="' + r1(Math.max(w, 0.5)) + '" height="' + r1(Math.max(h, 0.5)) + '" fill="' + fill + '" stroke="' + INK + '" stroke-width="1.6"/>';
  const poly = (pts, fill) => '<polygon points="' + pts.map((p) => r1(p[0]) + ',' + r1(p[1])).join(' ') + '" fill="' + fill + '" stroke="' + INK + '" stroke-width="1.6"/>';
  const sheet = (inner, o) => {
    const svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 380">' + defs + '<rect width="640" height="380" fill="#FFF"/>' + inner
      + txt(24, 360, o.stamp, 'start', 11).replace('fill="' + INK + '"', 'fill="' + MUTED + '"')
      + txt(616, 360, 'INTER ROLL', 'end', 11).replace('fill="' + INK + '"', 'fill="' + MUTED + '"')
      + txt(24, 32, o.note, 'start', 12) + '</svg>';
    return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
  };

  // ---------- 3D helpers: a prism = side faces along the polygon edges + two caps
  const shade = (color, nx, ny) => {
    const light = 0.5 + 0.5 * (-0.6 * nx - 0.8 * ny); // light from top-left
    const o = (0.32 * (1 - light)).toFixed(3);
    return 'linear-gradient(rgba(17,20,24,' + o + '),rgba(17,20,24,' + o + ')),' + color;
  };
  const face = (w, h, transform, background, extra) => ({
    style: 'position:absolute;left:' + r1(-w / 2) + 'px;top:' + r1(-h / 2) + 'px;width:' + r1(w) + 'px;height:' + r1(h) + 'px;'
      + 'transform:' + transform + ';background:' + background + ';' + (extra || '')
  });
  function prism(pts, depth, color, capBg, capShape, inward) {
    const out = [];
    for (let i = 0; i < pts.length; i++) {
      const a = pts[i], b = pts[(i + 1) % pts.length];
      const dx = b[0] - a[0], dy = b[1] - a[1], len = Math.sqrt(dx * dx + dy * dy);
      if (len < 0.01) continue;
      const ang = Math.atan2(dy, dx) * 180 / Math.PI;
      const nx = dy / len, ny = -dx / len;
      out.push(face(len + 0.6, depth, 'translate3d(' + r1((a[0] + b[0]) / 2) + 'px,' + r1((a[1] + b[1]) / 2) + 'px,0) rotateZ(' + r1(ang) + 'deg) rotateX(90deg)',
        inward ? 'linear-gradient(rgba(0,0,0,.35),rgba(0,0,0,.35)),' + HOLE : shade(color, inward ? -nx : nx, inward ? -ny : ny)));
    }
    if (capBg) {
      const xs = pts.map((p) => p[0]), ys = pts.map((p) => p[1]);
      const minX = Math.min.apply(null, xs), maxX = Math.max.apply(null, xs), minY = Math.min.apply(null, ys), maxY = Math.max.apply(null, ys);
      const w = maxX - minX, h = maxY - minY;
      const clip = capShape === 'circle' ? 'border-radius:50%;'
        : 'clip-path:polygon(' + pts.map((p) => r1((p[0] - minX) / w * 100) + '% ' + r1((p[1] - minY) / h * 100) + '%').join(',') + ');';
      out.push(face(w, h, 'translate3d(' + r1((minX + maxX) / 2) + 'px,' + r1((minY + maxY) / 2) + 'px,' + r1(depth / 2) + 'px)', capBg, clip));
      out.push(face(w, h, 'translate3d(' + r1((minX + maxX) / 2) + 'px,' + r1((minY + maxY) / 2) + 'px,' + r1(-depth / 2) + 'px)', capBg, clip));
    }
    return out;
  }
  const ngon = (r, n) => { const p = []; for (let i = 0; i < n; i++) { const a = (i / n) * Math.PI * 2; p.push([r * Math.cos(a), r * Math.sin(a)]); } return p; };
  const ring = (rIn, rOut, inner, outer) => 'radial-gradient(circle closest-side,' + HOLE + ' 0 ' + r1(rIn / rOut * 100) + '%,' + (inner || outer) + ' ' + r1(rIn / rOut * 100 + 0.6) + '% 100%)';
  const starPts = (R, z) => {
    const Ri = R * 0.64, p = [], step = Math.PI * 2 / z, d = step * 0.22, di = step * 0.18;
    for (let i = 0; i < z; i++) {
      const a = i * step - Math.PI / 2;
      p.push([R * Math.cos(a - d), R * Math.sin(a - d)], [R * Math.cos(a + d), R * Math.sin(a + d)]);
      p.push([Ri * Math.cos(a + step / 2 - di), Ri * Math.sin(a + step / 2 - di)], [Ri * Math.cos(a + step / 2 + di), Ri * Math.sin(a + step / 2 + di)]);
    }
    return p;
  };
  const SIZE3D = 230; // px for the biggest dimension

  const shapes = {
    // Wheel / tyre / guide roller: D outer, d bore, B width, T polyurethane thickness
    wheel: {
      fix: (p) => { p.d = clamp(p.d, 5, p.D - 20); p.T = clamp(p.T, 2, (p.D - p.d) / 2 - 3); return p; },
      chips: (p) => [['D', 'Ø' + p.D], ['d', 'Ø' + p.d], ['B', p.B], ['T', p.T + ' mm']],
      draw: (p, o) => {
        const k = Math.min(240 / p.D, 220 / p.B, 240 / p.D), R = p.D / 2 * k, cx = 170, cy = 190;
        const hub = (p.D / 2 - p.T) * k, bore = p.d / 2 * k, w = p.B * k, x0 = 450 - w / 2;
        return sheet(axis(cx - R - 20, cy, cx + R + 20, cy) + axis(cx, cy - R - 20, cx, cy + R + 20)
          + circle(cx, cy, R, 'url(#h)') + circle(cx, cy, hub, '#D2D7DC') + circle(cx, cy, bore, '#FFF')
          + dimH(cx - R, cx + R, cy - R - 18, 'Ø' + p.D) + txt(cx, cy + 5, 'Ø' + p.d, 'middle', 12)
          + rect(x0, cy - R, w, R - hub, 'url(#h)') + rect(x0, cy + hub, w, R - hub, 'url(#h)')
          + rect(x0, cy - hub, w, hub - bore, '#D2D7DC') + rect(x0, cy + bore, w, hub - bore, '#D2D7DC')
          + axis(x0 - 20, cy, x0 + w + 20, cy) + dimH(x0, x0 + w, cy + R + 28, 'B=' + p.B) + dimV(x0 + w + 26, cy - R, cy - hub, 'T=' + p.T), o);
      },
      faces: (p, c) => {
        const k = SIZE3D / Math.max(p.D, p.B), R = p.D / 2 * k, hub = (p.D / 2 - p.T) * k, bore = Math.max(2, p.d / 2 * k), B = Math.max(6, p.B * k);
        const cap = 'radial-gradient(circle closest-side,' + HOLE + ' 0 ' + r1(bore / R * 100) + '%,' + HUB + ' ' + r1(bore / R * 100 + 0.6) + '% ' + r1(hub / R * 100) + '%,' + c + ' ' + r1(hub / R * 100 + 0.6) + '% 100%)';
        return prism(ngon(R, 36), B, c, cap, 'circle').concat(prism(ngon(bore, 18), B + 0.5, c, null, null, true));
      }
    },
    // Bushing / spring / sleeve: D outer, d bore, L length
    bushing: {
      fix: (p) => { p.d = clamp(p.d, 2, p.D - 4); return p; },
      chips: (p) => [['D', 'Ø' + p.D], ['d', 'Ø' + p.d], ['L', p.L], ['S', r1((p.D - p.d) / 2) + ' mm']],
      draw: (p, o) => {
        const k = Math.min(220 / p.D, 250 / p.L, 220 / p.D), R = p.D / 2 * k, r = p.d / 2 * k, cx = 160, cy = 190, w = p.L * k, x0 = 440 - w / 2;
        return sheet(axis(cx - R - 20, cy, cx + R + 20, cy) + axis(cx, cy - R - 20, cx, cy + R + 20)
          + circle(cx, cy, R, 'url(#h)') + circle(cx, cy, r, '#FFF') + dimH(cx - R, cx + R, cy - R - 18, 'Ø' + p.D) + txt(cx, cy + 5, 'Ø' + p.d, 'middle', 12)
          + rect(x0, cy - R, w, R - r, 'url(#h)') + rect(x0, cy + r, w, R - r, 'url(#h)')
          + axis(x0 - 20, cy, x0 + w + 20, cy) + dimH(x0, x0 + w, cy + R + 28, 'L=' + p.L), o);
      },
      faces: (p, c) => {
        const k = SIZE3D / Math.max(p.D, p.L), R = p.D / 2 * k, r = Math.max(2, p.d / 2 * k), L = Math.max(6, p.L * k);
        return prism(ngon(R, 32), L, c, ring(r, R, null, c), 'circle').concat(prism(ngon(r, 18), L + 0.5, c, null, null, true));
      }
    },
    // Plate / scraper / sheet: L length, W width, T thickness
    plate: {
      fix: (p) => p,
      chips: (p) => [['L', p.L], ['W', p.W], ['T', p.T + ' mm'], ['S', r1(p.L * p.W / 1e6) + ' m²']],
      draw: (p, o) => {
        const k = Math.min(520 / p.L, 170 / p.W), w = p.L * k, h = p.W * k, x0 = 320 - w / 2, y0 = 70;
        const t = Math.max(6, p.T * k), ty = y0 + h + 60;
        return sheet(rect(x0, y0, w, h, '#FFF') + dimH(x0, x0 + w, y0 - 14, 'L=' + p.L) + dimV(x0 - 18, y0, y0 + h, 'W=' + p.W)
          + rect(x0, ty, w, t, 'url(#h)') + dimV(x0 + w + 24, ty, ty + t, 'T=' + p.T), o);
      },
      faces: (p, c) => {
        const k = 300 / Math.max(p.L, p.W), w = p.L * k, h = Math.max(24, p.W * k), t = Math.max(5, p.T * k);
        // Lay the plate flat so the turntable never shows it edge-on.
        return prism([[-w / 2, -h / 2], [w / 2, -h / 2], [w / 2, h / 2], [-w / 2, h / 2]], t, c, c, 'poly')
          .map((f) => ({ style: f.style.replace('transform:', 'transform:rotateX(90deg) ') }));
      }
    },
    // Coupling spider: D outer, d bore, z jaws, B thickness
    spider: {
      fix: (p) => { p.d = clamp(p.d, 3, p.D * 0.55); p.z = Math.round(clamp(p.z, 3, 12)); return p; },
      chips: (p) => [['D', 'Ø' + p.D], ['d', 'Ø' + p.d], ['z', p.z], ['B', p.B + ' mm']],
      draw: (p, o) => {
        const k = 240 / p.D, R = p.D / 2 * k, cx = 170, cy = 190, w = Math.max(6, p.B * k), x0 = 450 - w / 2;
        return sheet(axis(cx - R - 20, cy, cx + R + 20, cy) + axis(cx, cy - R - 20, cx, cy + R + 20)
          + poly(starPts(R, p.z).map((q) => [q[0] + cx, q[1] + cy]), 'url(#h)') + circle(cx, cy, p.d / 2 * k, '#FFF')
          + dimH(cx - R, cx + R, cy - R - 18, 'Ø' + p.D) + txt(cx, cy + 5, 'Ø' + p.d, 'middle', 12)
          + rect(x0, cy - R, w, 2 * R, 'url(#h)') + dimH(x0, x0 + w, cy + R + 28, 'B=' + p.B) + txt(x0 + w + 16, cy, 'z=' + p.z, 'start'), o);
      },
      faces: (p, c) => {
        const k = SIZE3D / p.D, R = p.D / 2 * k, B = Math.max(6, p.B * k);
        return prism(starPts(R, p.z), B, c, ring(Math.max(2, p.d / 2 * k), R, null, c), 'poly');
      }
    }
  };

  return {
    has: (type) => !!shapes[type],
    /** Current values: server defaults, overridden by what the visitor picked, then made consistent. */
    values: (type, params, picked) => {
      const p = {};
      (params || []).forEach((d) => { p[d.key] = clamp(+((picked || {})[d.key] ?? d.default), d.min, d.max); });
      return shapes[type].fix(p);
    },
    chips: (type, p) => shapes[type].chips(p).map(([label, value]) => ({ label, value })),
    draw: (type, p, o) => shapes[type].draw(p, o),
    faces: (type, p, color) => shapes[type].faces(p, color),
    /** Camera pitch: flat parts are seen from above, round parts from the front. */
    pitch: (type) => (type === 'plate' ? -32 : -24)
  };
})();
