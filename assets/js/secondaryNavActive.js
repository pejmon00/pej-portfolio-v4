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
    var links = qsAll("a", nav);
    links = nav ? Array.prototype.slice.call(nav.querySelectorAll("a")) : [];
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

      // For each anchor target, find the next sibling section to observe
      // (anchor divs are zero-height, so observe the real section instead)
      var sectionTargets = targets.map(function (t) {
        // Walk forward from the anchor to find the nearest section with content
        var el = t.el;
        var section = null;
        while (el && !section) {
          el = el.nextElementSibling;
          if (el && (el.tagName === "SECTION" || el.tagName === "HR")) {
            // Skip dividers, find the section
            if (el.tagName === "SECTION") section = el;
          }
        }
        return { hash: t.hash, el: t.el, section: section, link: t.link };
      });

      // Create an observer on the actual sections
      var activeHash = null;
      var observer = new IntersectionObserver(
        function (entries) {
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

      // Default: set first link active on load if no hash
      function checkHash() {
        var h = location.hash || "";
        if (!h) {
          // No hash — activate first link
          setActiveLink(targets[0].hash, nav);
          return;
        }
        if (
          targets.some(function (t) {
            return "#" + t.el.id === h;
          })
        ) {
          setActiveLink(h, nav);
        }
      }

      window.addEventListener("hashchange", checkHash);
      window.addEventListener("load", checkHash);
      // Also set default immediately
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
