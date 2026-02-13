/**
 * gallery-carousel.js
 * Project-based gallery: each project card has a cover image + thumbnail strip.
 * Clicking a cover or thumbnail opens a lightbox cycling through that project's images.
 * Supports keyboard (arrows, escape), touch swipe, and prev/next buttons.
 */
(function () {
  'use strict';

  var SWIPE_THRESHOLD = 50;

  function init() {
    var grids = document.querySelectorAll('.gallery-grid');
    var lightbox = document.getElementById('lightbox');
    if (!grids.length || !lightbox) return;

    var projects = document.querySelectorAll('.gallery-project');
    if (!projects.length) return;

    var img = lightbox.querySelector('.lightbox__img');
    var caption = lightbox.querySelector('.lightbox__caption');
    var counter = lightbox.querySelector('.lightbox__counter');
    var closeBtn = lightbox.querySelector('.lightbox__close');
    var prevBtn = lightbox.querySelector('.lightbox__btn--prev');
    var nextBtn = lightbox.querySelector('.lightbox__btn--next');

    var currentImages = [];
    var current = 0;
    var touchStartX = 0;
    var touchDeltaX = 0;

    function show(images, index) {
      currentImages = images;
      current = index;
      img.src = currentImages[current].src;
      img.alt = currentImages[current].alt;
      caption.textContent = currentImages[current].description || '';
      counter.textContent = (current + 1) + ' / ' + currentImages.length;
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function hide() {
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      currentImages = [];
    }

    function next() {
      if (!currentImages.length) return;
      show(currentImages, current + 1 >= currentImages.length ? 0 : current + 1);
    }

    function prev() {
      if (!currentImages.length) return;
      show(currentImages, current - 1 < 0 ? currentImages.length - 1 : current - 1);
    }

    // Build per-project image arrays and wire events
    projects.forEach(function (project) {
      var images = [];
      var thumbs = project.querySelectorAll('.gallery-project__thumb');
      var cover = project.querySelector('.gallery-project__cover img');

      // Build images array from thumbnails (each thumb represents an image)
      thumbs.forEach(function (thumb) {
        var thumbImg = thumb.querySelector('img');
        if (thumbImg) {
          images.push({ src: thumbImg.src, alt: thumbImg.alt || '', description: thumb.dataset.description || '' });
        }
      });

      // If no thumbs (single-image project), use cover image
      var coverWrap = project.querySelector('.gallery-project__cover');
      if (!images.length && cover) {
        images.push({ src: cover.src, alt: cover.alt || '', description: coverWrap ? (coverWrap.dataset.description || '') : '' });
      }

      // Cover click — open lightbox at index 0
      if (coverWrap) {
        coverWrap.addEventListener('click', function () {
          show(images, 0);
        });
      }

      // Thumbnail clicks — update cover preview + open lightbox
      thumbs.forEach(function (thumb, i) {
        thumb.addEventListener('click', function () {
          // Update active state
          thumbs.forEach(function (t) { t.classList.remove('active'); });
          thumb.classList.add('active');
          // Update cover to show selected image
          if (cover) {
            cover.src = images[i].src;
            cover.alt = images[i].alt;
          }
          // Open lightbox at this index
          show(images, i);
        });
      });
    });

    // Close
    closeBtn.addEventListener('click', hide);
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) hide();
    });

    // Prev / Next buttons
    prevBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      prev();
    });
    nextBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      next();
    });

    // Keyboard
    document.addEventListener('keydown', function (e) {
      if (lightbox.getAttribute('aria-hidden') !== 'false') return;
      if (e.key === 'Escape') { hide(); }
      else if (e.key === 'ArrowLeft') { prev(); }
      else if (e.key === 'ArrowRight') { next(); }
    });

    // Touch swipe
    lightbox.addEventListener('touchstart', function (e) {
      touchStartX = e.touches[0].clientX;
      touchDeltaX = 0;
    }, { passive: true });

    lightbox.addEventListener('touchmove', function (e) {
      touchDeltaX = e.touches[0].clientX - touchStartX;
    }, { passive: true });

    lightbox.addEventListener('touchend', function () {
      if (Math.abs(touchDeltaX) > SWIPE_THRESHOLD) {
        if (touchDeltaX < 0) { next(); }
        else { prev(); }
      }
      touchDeltaX = 0;
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
