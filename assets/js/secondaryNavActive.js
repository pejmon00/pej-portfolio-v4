// secondaryNavActive.js
// Observes sections referenced by nav[data-secondary-nav] and updates
// aria-current="page" on the corresponding nav links based on visibility.

(function () {
  "use strict";

  function qsAll(selector) {
    return Array.prototype.slice.call(document.querySelectorAll(selector));
  }

  function normalizeHash(href) {
    try {
      var url = new URL(href, location.href);
      return url.hash || (href[0] === "#" ? href : "");
    } catch (e) {
      return href && href[0] === "#" ? href : "";
    }
  }

  function setActiveLink(hash, nav) {
    var links = nav ? Array.prototype.slice.call(nav.querySelectorAll("a")) : [];
    links.forEach(function (a) {
      if (normalizeHash(a.getAttribute("href")) === hash) {
        a.setAttribute("aria-current", "page");
      } else {
        a.removeAttribute("aria-current");
      }
    });
  }

  function init() {
    var navs = qsAll("nav[data-secondary-nav]");
    if (!navs.length) return;

    navs.forEach(function (nav) {
      var links = Array.prototype.slice.call(nav.querySelectorAll("a"));
      var targets = links
        .map(function (a) {
          var hash = normalizeHash(a.getAttribute("href"));
          return hash
            ? {
                hash: hash,
                el: document.getElementById(hash.slice(1)),
                link: a,
              }
            : null;
        })
        .filter(Boolean);

      if (!targets.length) return;

      // For each anchor target, find the section to observe.
      // Two cases: (1) standalone anchor div — walk siblings to find next section,
      // (2) element inside a section (e.g. an h2 id) — use the parent section.
      var sectionTargets = targets.map(function (t) {
        var el = t.el;
        var section = null;

        // If the element is already inside a section, use that section
        var parentSection = el.closest ? el.closest("section") : null;
        if (parentSection) {
          section = parentSection;
        } else {
          // Walk forward from the anchor to find the nearest sibling section
          while (el && !section) {
            el = el.nextElementSibling;
            if (el && (el.tagName === "SECTION" || el.tagName === "HR")) {
              if (el.tagName === "SECTION") section = el;
            }
          }
        }
        return { hash: t.hash, el: t.el, section: section, link: t.link };
      });

      // Suppress observer updates during smooth-scroll navigation
      var scrollLocked = false;
      var scrollTimer = null;

      function lockScroll(hash) {
        scrollLocked = true;
        setActiveLink(hash, nav);
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function () {
          scrollLocked = false;
        }, 1000);
      }

      // Create an observer on the actual sections
      var activeHash = null;
      var observer = new IntersectionObserver(
        function (entries) {
          if (scrollLocked) return;
          entries.forEach(function (entry) {
            var target = sectionTargets.find(function (t) {
              return t.section === entry.target;
            });
            if (!target) return;

            if (entry.isIntersecting) {
              activeHash = target.hash;
              setActiveLink(target.hash, nav);
            }
          });
        },
        {
          root: null,
          threshold: [0.1],
          rootMargin: "-10% 0px -60% 0px",
        },
      );

      sectionTargets.forEach(function (t) {
        if (t.section) observer.observe(t.section);
      });

      // Listen for clicks on nav links to lock during scroll
      links.forEach(function (a) {
        a.addEventListener("click", function () {
          var hash = normalizeHash(a.getAttribute("href"));
          if (hash) lockScroll(hash);
        });
      });

      // Default: set first link active on load if no hash
      function checkHash() {
        var h = location.hash || "";
        if (!h) {
          setActiveLink(targets[0].hash, nav);
          return;
        }
        if (
          targets.some(function (t) {
            return "#" + t.el.id === h;
          })
        ) {
          lockScroll(h);
        }
      }

      window.addEventListener("hashchange", checkHash);
      window.addEventListener("load", checkHash);
      if (!location.hash) {
        setActiveLink(targets[0].hash, nav);
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
