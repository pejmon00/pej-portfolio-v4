const GROUPS = {
  "/experience": [
    { path: "/experience", title: "Overview" },
    { path: "/experience/case-study-1", title: "Case Study 1" },
    { path: "/experience/case-study-2", title: "Case Study 2" },
    { path: "/experience/case-study-3", title: "Case Study 3" },
    { path: "/experience/case-study-4", title: "Case Study 4" },
  ],
  "/writing": [
    { path: "/writing", title: "Overview" },
    { path: "/writing/article-1", title: "Article 1" },
    { path: "/writing/article-2", title: "Article 2" },
  ],
};

export function mountSecondaryNav(container = document.body) {
  const path = window.location.pathname;
  const groupKey = Object.keys(GROUPS).find(
    (g) => path === g || path.startsWith(g + "/"),
  );
  if (!groupKey) return; // only render on experience* and writing*

  const nav = document.createElement("nav");
  nav.setAttribute("role", "navigation");
  nav.setAttribute("aria-label", "Secondary");
  const ul = document.createElement("ul");
  ul.style.listStyle = "none";
  ul.style.padding = "0";
  ul.style.margin = "0";

  GROUPS[groupKey].forEach((item) => {
    const li = document.createElement("li");
    const a = document.createElement("a");
    a.href = item.path;
    a.textContent = item.title;
    if (item.path === path) a.setAttribute("aria-current", "page");
    li.appendChild(a);
    ul.appendChild(li);
  });

  nav.appendChild(ul);

  // insert after header if present
  const header = document.querySelector('header[role="banner"]');
  if (header && header.parentNode)
    header.parentNode.insertBefore(nav, header.nextSibling);
  else container.prepend(nav);
}
