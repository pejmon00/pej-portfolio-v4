export function mountAppShell(rootEl) {
  if (!rootEl) return;
  // Create banner and primary navigation
  const banner = document.createElement("header");
  banner.setAttribute("role", "banner");
  banner.innerHTML = `
    <div class="site-branding">
      <a href="/">HOME</a>
    </div>
    <nav role="navigation" aria-label="Primary">
      <ul>
        <li><a href="/">HOME</a></li>
        <li><a href="/experience">EXPERIENCE</a></li>
        <li><a href="/writing">WRITING</a></li>
        <li><a href="/services">SERVICES</a></li>
      </ul>
    </nav>
  `;

  // keyboard handling for simple left/right focus control
  banner.addEventListener("keydown", (e) => {
    const keys = ["ArrowLeft", "ArrowRight"];
    if (!keys.includes(e.key)) return;
    const focusable = Array.from(banner.querySelectorAll("a"));
    const idx = focusable.indexOf(document.activeElement);
    if (idx === -1) return;
    if (e.key === "ArrowLeft") {
      const next = focusable[(idx - 1 + focusable.length) % focusable.length];
      next.focus();
    } else {
      const next = focusable[(idx + 1) % focusable.length];
      next.focus();
    }
    e.preventDefault();
  });

  rootEl.prepend(banner);
}
