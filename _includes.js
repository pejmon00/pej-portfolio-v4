/**
 * Client-side includes for header and footer
 * Works on static hosting (GoDaddy cPanel)
 * Graceful degradation if fetch fails
 */
(function() {
  'use strict';

  // Determine active nav item based on current path
  function getActivePage() {
    var path = location.pathname;
    if (/^\/experience/.test(path)) return 'experience';
    if (/^\/articles/.test(path)) return 'articles';
    if (/^\/gallery/.test(path)) return 'gallery';
    return 'home';
  }

  // Load and inject header
  function loadHeader() {
    var placeholder = document.getElementById('header-placeholder');
    if (!placeholder) return;

    fetch('/_header.html')
      .then(function(response) {
        if (!response.ok) throw new Error('Header fetch failed');
        return response.text();
      })
      .then(function(html) {
        placeholder.innerHTML = html;
        
        // Set aria-current on active nav link
        var activePage = getActivePage();
        var activeLink = placeholder.querySelector('[data-nav="' + activePage + '"]');
        if (activeLink) {
          activeLink.setAttribute('aria-current', 'page');
        }
      })
      .catch(function(err) {
        console.warn('Header include failed, falling back to static content:', err);
        // Page remains usable with inline header fallback
      });
  }

  // Load and inject footer
  function loadFooter() {
    var placeholder = document.getElementById('footer-placeholder');
    if (!placeholder) return;

    fetch('/_footer.html')
      .then(function(response) {
        if (!response.ok) throw new Error('Footer fetch failed');
        return response.text();
      })
      .then(function(html) {
        placeholder.innerHTML = html;
      })
      .catch(function(err) {
        console.warn('Footer include failed, falling back to static content:', err);
        // Page remains usable with inline footer fallback
      });
  }

  // Cursor light with trail
  function initCursorLight() {
    if (!window.matchMedia('(hover: hover)').matches) return;

    var TRAIL_SIZE = 8;
    var THROTTLE_MS = 35;
    var trail = [];
    var trailIndex = 0;
    var lastSpawn = 0;

    // Main cursor light — always visible
    var main = document.createElement('div');
    main.className = 'cursor-trail';
    main.style.opacity = '0';
    document.body.appendChild(main);

    // Trail dots — fade out after spawning
    for (var i = 0; i < TRAIL_SIZE; i++) {
      var dot = document.createElement('div');
      dot.className = 'cursor-trail';
      dot.style.opacity = '0';
      document.body.appendChild(dot);
      trail.push(dot);
    }

    document.addEventListener('mousemove', function (e) {
      var x = e.clientX;
      var y = e.clientY;

      // Main light follows cursor, always visible
      main.style.left = x + 'px';
      main.style.top = y + 'px';
      main.style.opacity = '1';

      // Spawn trail dots at throttled rate
      var now = Date.now();
      if (now - lastSpawn < THROTTLE_MS) return;
      lastSpawn = now;

      var dot = trail[trailIndex];
      trailIndex = (trailIndex + 1) % TRAIL_SIZE;
      dot.style.left = x + 'px';
      dot.style.top = y + 'px';
      dot.style.opacity = '0.6';

      (function (el) {
        setTimeout(function () { el.style.opacity = '0'; }, 50);
      })(dot);
    });

    document.addEventListener('mouseleave', function () {
      main.style.opacity = '0';
      trail.forEach(function (el) { el.style.opacity = '0'; });
    });
  }

  // Run when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      loadHeader();
      loadFooter();
      initCursorLight();
    });
  } else {
    loadHeader();
    loadFooter();
    initCursorLight();
  }
})();
