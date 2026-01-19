// scripts/server.js
const http = require("http");
const fs = require("fs");
const path = require("path");
// use WHATWG URL API instead of legacy `url.parse`

const PORT = process.env.PORT || 3000;
const PUBLIC_DIR = path.join(__dirname, "..", "public");

const MIME = {
  ".html": "text/html; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".js": "text/javascript; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".png": "image/png",
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".svg": "image/svg+xml",
  ".ico": "image/x-icon",
  ".webp": "image/webp",
  ".txt": "text/plain; charset=utf-8",
};

function safeJoin(base, requestPath) {
  // normalize requestPath so absolute paths don't escape the base
  let rp = requestPath;
  if (path.isAbsolute(rp)) rp = '.' + rp;
  const joined = path.normalize(path.join(base, rp));
  if (!joined.startsWith(base)) return null; // prevent path traversal
  return joined;
}

function send(res, status, body, type = "text/plain; charset=utf-8") {
  res.writeHead(status, { "Content-Type": type });
  res.end(body);
}

function sendFile(res, filePath) {
  const ext = path.extname(filePath).toLowerCase();
  const type = MIME[ext] || "application/octet-stream";

  fs.readFile(filePath, (err, data) => {
    if (err) return send(res, 500, "Server error");
    res.writeHead(200, { "Content-Type": type });
    res.end(data);
  });
}

const server = http.createServer((req, res) => {
  let pathname = "/";
  try {
    const full = new URL(req.url, `http://${req.headers.host}`);
    pathname = decodeURIComponent(full.pathname || "/");
  } catch (e) {
    // fallback for unexpected input
    pathname = decodeURIComponent(req.url.split('?')[0] || "/");
  }

  // map "/" to "/index.html"
  if (pathname === "/") pathname = "/index.html";

  // try exact file first
  const filePath = safeJoin(PUBLIC_DIR, pathname);
  if (!filePath) return send(res, 400, "Bad request");

  fs.stat(filePath, (err, stat) => {
    if (!err && stat.isFile()) return sendFile(res, filePath);

    // support directory routes: "/experience" -> "/experience/index.html"
    if (!path.extname(pathname)) {
      const dirIndex = safeJoin(PUBLIC_DIR, path.join(pathname, "index.html"));
      if (!dirIndex) return send(res, 400, "Bad request");

      return fs.stat(dirIndex, (e2, s2) => {
        if (!e2 && s2.isFile()) return sendFile(res, dirIndex);
        return send(res, 404, "Not found");
      });
    }

    return send(res, 404, "Not found");
  });
});

server.listen(PORT, "127.0.0.1", () => {
  console.log(`Server running on http://localhost:${PORT}`);
});
