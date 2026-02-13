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

      // Create an observer to mark the link active when the section is mostly visible
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            var target = targets.find(function (t) {
              return t.el === entry.target;
            });
            if (!target) return;

            if (entry.isIntersecting && entry.intersectionRatio > 0.5) {
              // set this link active
              setActiveLink("#" + target.el.id, nav);
            }
          });
        },
        {
          root: null,
          threshold: [0.5],
          rootMargin: "0px 0px -30% 0px",
        },
      );

      targets.forEach(function (t) {
        if (t.el) observer.observe(t.el);
      });

      // also respond to hashchange and load
      function checkHash() {
        var h = location.hash || "";
        if (!h) return;
        // if the hash matches one of our targets, mark it active
        if (
          targets.some(function (t) {
            return "#" + t.el.id === h;
          })
        ) {
          setActiveLink(h, nav);
        }
      }

      window.addEventListener("hashchange", checkHash);
      window.addEventListener("load", function () {
        // mark based on initial hash
        checkHash();
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
