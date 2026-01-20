// secondaryNavMobile.js
// Mobile-only collapsible behavior for nav[data-secondary-nav]
// Progressive enhancement: without JS the <ul> remains visible.

(function () {
  "use strict";

  var MOBILE_QUERY = "(max-width: 640px)";

  function createToggleButton(id) {
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "secnav__toggle";
    btn.setAttribute("aria-controls", id);
    btn.setAttribute("aria-expanded", "false");
    btn.innerHTML =
      "" +
      '<svg aria-hidden="true" width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">' +
      '<path d="M1 1.5h16M1 7h16M1 12.5h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>' +
      "</svg>" +
      '<span class="secnav__label">Sections</span>';
    return btn;
  }

  function isMobile(mql) {
    return mql.matches;
  }

  function setupNav(nav, index, mql) {
    var ul = nav.querySelector("ul");
    if (!ul) return;

    if (!ul.id) ul.id = "secnav-menu-" + (index + 1);
    var btn = createToggleButton(ul.id);

    // Insert button before the list
    nav.insertBefore(btn, ul);

    // State helpers
    function open() {
      nav.classList.add("is-open");
      ul.hidden = false;
      btn.setAttribute("aria-expanded", "true");
    }

    function close() {
      nav.classList.remove("is-open");
      ul.hidden = true;
      btn.setAttribute("aria-expanded", "false");
    }

    function resetWhenDesktop() {
      if (!isMobile(mql)) {
        // desktop: ensure menu visible and not marked open
        nav.classList.remove("is-open");
        ul.hidden = false;
        btn.setAttribute("aria-expanded", "false");
      } else {
        // mobile: collapsed by default
        nav.classList.remove("is-open");
        ul.hidden = true;
        btn.setAttribute("aria-expanded", "false");
      }
    }

    // Initialize according to current viewport
    resetWhenDesktop();

    // Toggle click
    btn.addEventListener("click", function (ev) {
      ev.stopPropagation();
      if (nav.classList.contains("is-open")) {
        close();
      } else {
        open();
      }
    });

    // Click outside closes when open
    document.addEventListener("click", function (ev) {
      if (!nav.classList.contains("is-open")) return;
      if (!nav.contains(ev.target)) close();
    });

    // Escape closes
    document.addEventListener("keydown", function (ev) {
      if (ev.key === "Escape" || ev.key === "Esc") {
        if (nav.classList.contains("is-open")) {
          close();
          // return focus to the toggle for discoverability
          btn.focus();
        }
      }
    });

    // Clicking any link in the menu closes (mobile only)
    ul.addEventListener("click", function (ev) {
      var a = ev.target.closest && ev.target.closest("a");
      if (a && isMobile(mql)) {
        close();
      }
    });

    // Listen for viewport changes
    if (typeof mql.addEventListener === "function") {
      mql.addEventListener("change", resetWhenDesktop);
    } else if (typeof mql.addListener === "function") {
      mql.addListener(resetWhenDesktop);
    }
  }

  // DOM ready
  function init() {
    // Indicate JS is present for CSS hooks
    document.documentElement.classList.add("js-secnav");
    // Mark body so CSS can add spacing for fixed primary and secondary navs
    document.body.classList.add("has-primary-nav");
    if (document.querySelector("nav[data-secondary-nav]")) {
      document.body.classList.add("has-secondary-nav");
    }

    var mql = window.matchMedia(MOBILE_QUERY);
    var navs = document.querySelectorAll("nav[data-secondary-nav]");
    Array.prototype.forEach.call(navs, function (nav, idx) {
      try {
        setupNav(nav, idx, mql);
      } catch (err) {
        // fail silently to preserve progressive enhancement
        // eslint-disable-next-line no-console
        console.error("secondaryNavMobile setup error", err);
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
