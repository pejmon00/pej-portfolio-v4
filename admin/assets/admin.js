/* Admin Panel JavaScript */
(function () {
  'use strict';

  // ---- Tabs ----
  function initTabs() {
    var btns = document.querySelectorAll('.tab-btn');
    var panels = document.querySelectorAll('.tab-panel');
    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        btns.forEach(function (b) { b.classList.remove('active'); });
        panels.forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        var target = document.getElementById(btn.dataset.tab);
        if (target) target.classList.add('active');
      });
    });
  }

  // ---- Expandable Panels ----
  function initPanels() {
    document.querySelectorAll('.panel-toggle').forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        toggle.classList.toggle('open');
        var body = toggle.nextElementSibling;
        if (body) body.classList.toggle('open');
      });
    });
  }

  // ---- Status Bar ----
  var statusTimer;
  function showStatus(message, type) {
    var bar = document.getElementById('status-bar');
    if (!bar) return;
    bar.textContent = message;
    bar.className = 'status-bar visible ' + (type || '');
    clearTimeout(statusTimer);
    statusTimer = setTimeout(function () {
      bar.classList.remove('visible');
    }, 4000);
  }

  // ---- AJAX Helper ----
  function apiCall(action, data, callback) {
    var formData = new FormData();
    formData.append('action', action);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

    if (data) {
      Object.keys(data).forEach(function (key) {
        if (data[key] instanceof File) {
          formData.append(key, data[key]);
        } else if (typeof data[key] === 'object') {
          formData.append(key, JSON.stringify(data[key]));
        } else {
          formData.append(key, data[key]);
        }
      });
    }

    fetch('/admin/api.php', {
      method: 'POST',
      body: formData
    })
    .then(function (r) { return r.json(); })
    .then(function (result) {
      if (result.success) {
        showStatus(result.message || 'Saved successfully.', 'success');
      } else {
        showStatus(result.message || 'An error occurred.', 'error');
      }
      if (callback) callback(result);
    })
    .catch(function (err) {
      showStatus('Network error: ' + err.message, 'error');
      if (callback) callback({ success: false, message: err.message });
    });
  }

  // ---- Image Picker ----
  var _pickerTargetId = null; // which hidden input to fill
  var _allImages = [];        // cached image list

  function loadAllImages(callback) {
    apiCall('list_images', null, function (res) {
      if (!res.success) return;
      _allImages = res.data;
      if (callback) callback(_allImages);
    });
  }

  function openImagePicker(targetInputId) {
    _pickerTargetId = targetInputId;
    var grid = document.getElementById('picker-grid');
    grid.innerHTML = '<p style="color:var(--admin-text-secondary);">Loading...</p>';
    showModal('image-picker-modal');

    loadAllImages(function (images) {
      grid.innerHTML = '';
      var currentVal = document.getElementById(targetInputId).value;
      images.forEach(function (path) {
        var div = document.createElement('div');
        div.className = 'image-grid-item' + (path === currentVal ? ' selected' : '');
        div.innerHTML = '<img src="' + escHtml(path) + '" alt="" loading="lazy">' +
          '<span class="image-path">' + escHtml(path.split('/').pop()) + '</span>';
        div.title = path;
        div.addEventListener('click', function () {
          selectImage(path);
        });
        grid.appendChild(div);
      });
    });
  }

  function selectImage(path) {
    if (!_pickerTargetId) return;
    setImageFieldValue(_pickerTargetId, path);
    hideModal('image-picker-modal');
    _pickerTargetId = null;
  }

  function pickerUpload() {
    var input = document.getElementById('picker-upload-input');
    if (!input.files.length) {
      showStatus('Please select a file.', 'error');
      return;
    }
    apiCall('upload_image', { image: input.files[0] }, function (res) {
      if (res.success) {
        input.value = '';
        // Select the newly uploaded image
        selectImage(res.data);
        // Also refresh the Images tab grid
        loadImages();
      }
    });
  }

  // Set value on a hidden input and update its sibling preview + path label
  function setImageFieldValue(inputId, path) {
    var input = document.getElementById(inputId);
    if (!input) return;
    input.value = path;

    var field = input.closest('.image-field');
    if (field) {
      var preview = field.querySelector('.image-field__preview');
      var pathLabel = field.querySelector('.image-field__path');
      if (preview) {
        preview.src = path;
        preview.style.display = path ? 'block' : 'none';
      }
      if (pathLabel) pathLabel.textContent = path;
    }
  }

  // Clear an image field (remove button)
  function clearImageField(inputId) {
    setImageFieldValue(inputId, '');
  }

  // Sync all image fields on page load (after data is populated)
  function syncAllImageFields() {
    document.querySelectorAll('.image-field').forEach(function (field) {
      var input = field.querySelector('input[type="hidden"]');
      var preview = field.querySelector('.image-field__preview');
      var pathLabel = field.querySelector('.image-field__path');
      if (input && input.value) {
        if (preview) { preview.src = input.value; preview.style.display = 'block'; }
        if (pathLabel) pathLabel.textContent = input.value;
      }
    });
  }

  // ---- Load Content Lists ----
  function loadArticles() {
    apiCall('list_articles', null, function (res) {
      if (!res.success) return;
      var list = document.getElementById('articles-list');
      if (!list) return;
      list.innerHTML = '';
      res.data.forEach(function (article) {
        var li = document.createElement('li');
        li.innerHTML =
          '<span class="item-title">' + escHtml(article.title) + '</span>' +
          '<span class="item-actions">' +
            '<button class="btn btn-sm btn-secondary" onclick="Admin.editArticle(' + article.id + ')">Edit</button>' +
            '<button class="btn btn-sm btn-danger" onclick="Admin.deleteArticle(' + article.id + ')">Delete</button>' +
          '</span>';
        list.appendChild(li);
      });
    });
  }

  function loadCaseStudies() {
    apiCall('list_case_studies', null, function (res) {
      if (!res.success) return;
      var list = document.getElementById('case-studies-list');
      if (!list) return;
      list.innerHTML = '';
      res.data.forEach(function (cs) {
        var li = document.createElement('li');
        li.innerHTML =
          '<span class="item-title">' + escHtml(cs.title) + '</span>' +
          '<span class="item-actions">' +
            '<button class="btn btn-sm btn-secondary" onclick="Admin.editCaseStudy(' + cs.id + ')">Edit</button>' +
            '<button class="btn btn-sm btn-danger" onclick="Admin.deleteCaseStudy(' + cs.id + ')">Delete</button>' +
          '</span>';
        list.appendChild(li);
      });
    });
  }

  function loadPages() {
    apiCall('get_pages', null, function (res) {
      if (!res.success) return;
      window._pagesData = res.data;
      populatePageForms(res.data);
      syncAllImageFields();
    });
  }

  function loadImages() {
    loadAllImages(function (images) {
      var grid = document.getElementById('images-grid');
      if (!grid) return;
      grid.innerHTML = '';
      images.forEach(function (path) {
        var div = document.createElement('div');
        div.className = 'image-grid-item';
        div.innerHTML = '<img src="' + escHtml(path) + '" alt="" loading="lazy">' +
          '<button class="image-delete-btn" title="Delete image">&times;</button>' +
          '<span class="image-path">' + escHtml(path.split('/').pop()) + '</span>';
        div.title = path;
        div.querySelector('img').addEventListener('click', function () {
          copyToClipboard(path);
          showStatus('Path copied: ' + path, 'success');
        });
        div.querySelector('.image-delete-btn').addEventListener('click', function (e) {
          e.stopPropagation();
          deleteImage(path);
        });
        grid.appendChild(div);
      });
    });
  }

  // ---- Populate Page Edit Forms ----
  function populatePageForms(pages) {
    Object.keys(pages).forEach(function (pageId) {
      var fields = pages[pageId];
      Object.keys(fields).forEach(function (key) {
        var el = document.getElementById('page-' + pageId + '-' + key);
        if (el) el.value = fields[key];
      });
    });
  }

  // ---- Article CRUD ----
  function editArticle(id) {
    apiCall('get_article', { id: id }, function (res) {
      if (!res.success) return;
      var a = res.data;
      document.getElementById('article-form-title').textContent = 'Edit Article';
      document.getElementById('article-id').value = a.id;
      document.getElementById('article-title').value = a.title;
      document.getElementById('article-subtitle').value = a.subtitle;
      document.getElementById('article-slug').value = a.slug;
      document.getElementById('article-navLabel').value = a.navLabel;
      document.getElementById('article-heroAlt').value = a.heroAlt;
      document.getElementById('article-body').value = a.body;
      document.getElementById('article-externalLink').value = a.externalLink || '';
      document.getElementById('article-order').value = a.order;
      setImageFieldValue('article-heroImage', a.heroImage);
      showModal('article-modal');
    });
  }

  function newArticle() {
    document.getElementById('article-form-title').textContent = 'New Article';
    document.getElementById('article-form').reset();
    document.getElementById('article-id').value = '';
    setImageFieldValue('article-heroImage', '');
    showModal('article-modal');
  }

  function saveArticle() {
    var form = document.getElementById('article-form');
    var data = {
      id: form.querySelector('#article-id').value,
      title: form.querySelector('#article-title').value,
      subtitle: form.querySelector('#article-subtitle').value,
      slug: form.querySelector('#article-slug').value,
      navLabel: form.querySelector('#article-navLabel').value,
      heroImage: form.querySelector('#article-heroImage').value,
      heroAlt: form.querySelector('#article-heroAlt').value,
      body: form.querySelector('#article-body').value,
      externalLink: form.querySelector('#article-externalLink').value,
      order: form.querySelector('#article-order').value
    };
    var action = data.id ? 'update_article' : 'create_article';
    apiCall(action, { article: data }, function (res) {
      if (res.success) {
        hideModal('article-modal');
        loadArticles();
      }
    });
  }

  function deleteArticle(id) {
    if (!confirm('Delete this article? This will remove the published page.')) return;
    apiCall('delete_article', { id: id }, function (res) {
      if (res.success) loadArticles();
    });
  }

  // ---- Case Study CRUD ----
  function editCaseStudy(id) {
    apiCall('get_case_study', { id: id }, function (res) {
      if (!res.success) return;
      var cs = res.data;
      document.getElementById('cs-form-title').textContent = 'Edit Case Study';
      document.getElementById('cs-id').value = cs.id;
      document.getElementById('cs-title').value = cs.title;
      document.getElementById('cs-subtitle').value = cs.subtitle;
      document.getElementById('cs-slug').value = cs.slug;
      document.getElementById('cs-navLabel').value = cs.navLabel;
      document.getElementById('cs-heroAlt').value = cs.heroAlt;
      document.getElementById('cs-order').value = cs.order;
      setImageFieldValue('cs-heroImage', cs.heroImage);
      // Populate sections
      var container = document.getElementById('cs-sections');
      container.innerHTML = '';
      cs.sections.forEach(function (sec) {
        addSectionEditor(container, sec);
      });
      showModal('cs-modal');
    });
  }

  function newCaseStudy() {
    document.getElementById('cs-form-title').textContent = 'New Case Study';
    document.getElementById('cs-form').reset();
    document.getElementById('cs-id').value = '';
    setImageFieldValue('cs-heroImage', '');
    var container = document.getElementById('cs-sections');
    container.innerHTML = '';
    var defaults = [
      { id: 'context', heading: 'Overview', body: '' },
      { id: 'problem', heading: 'Role', body: '' },
      { id: 'approach', heading: 'System / Work', body: '' },
      { id: 'design', heading: 'Outcome', body: '' }
    ];
    defaults.forEach(function (sec) { addSectionEditor(container, sec); });
    showModal('cs-modal');
  }

  function addSectionEditor(container, sec) {
    var div = document.createElement('div');
    div.className = 'section-editor';
    var imgId = 'sec-image-' + (sec.id || Math.random().toString(36).substr(2, 6));
    div.innerHTML =
      '<h4>' + escHtml(sec.heading || 'Section') + '</h4>' +
      '<input type="hidden" class="sec-id" value="' + escHtml(sec.id) + '">' +
      '<div class="form-group"><label>Heading</label><input type="text" class="sec-heading" value="' + escHtml(sec.heading) + '"></div>' +
      '<div class="form-group"><label>Body (HTML)</label><textarea class="sec-body">' + escHtml(sec.body) + '</textarea></div>' +
      '<div class="form-group"><label>Image (optional)</label>' +
        '<div class="image-field">' +
          '<input type="hidden" class="sec-image" id="' + imgId + '" value="' + escHtml(sec.image || '') + '">' +
          '<img class="image-field__preview" src="' + escHtml(sec.image || '') + '" alt="Preview" style="' + (sec.image ? 'display:block' : 'display:none') + '">' +
          '<div class="image-field__actions">' +
            '<button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker(\'' + imgId + '\')">Choose Image</button>' +
            '<button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField(\'' + imgId + '\')">Remove</button>' +
            '<span class="image-field__path">' + escHtml(sec.image || '') + '</span>' +
          '</div>' +
        '</div>' +
      '</div>';
    container.appendChild(div);
  }

  function saveCaseStudy() {
    var form = document.getElementById('cs-form');
    var sections = [];
    form.querySelectorAll('.section-editor').forEach(function (el) {
      sections.push({
        id: el.querySelector('.sec-id').value,
        heading: el.querySelector('.sec-heading').value,
        body: el.querySelector('.sec-body').value,
        image: el.querySelector('.sec-image') ? el.querySelector('.sec-image').value : ''
      });
    });
    var data = {
      id: form.querySelector('#cs-id').value,
      title: form.querySelector('#cs-title').value,
      subtitle: form.querySelector('#cs-subtitle').value,
      slug: form.querySelector('#cs-slug').value,
      navLabel: form.querySelector('#cs-navLabel').value,
      heroImage: form.querySelector('#cs-heroImage').value,
      heroAlt: form.querySelector('#cs-heroAlt').value,
      order: form.querySelector('#cs-order').value,
      sections: sections
    };
    var action = data.id ? 'update_case_study' : 'create_case_study';
    apiCall(action, { case_study: data }, function (res) {
      if (res.success) {
        hideModal('cs-modal');
        loadCaseStudies();
      }
    });
  }

  function deleteCaseStudy(id) {
    if (!confirm('Delete this case study? This will remove the published page.')) return;
    apiCall('delete_case_study', { id: id }, function (res) {
      if (res.success) loadCaseStudies();
    });
  }

  // ---- Page Saves ----
  function savePage(pageId) {
    var container = document.getElementById('page-' + pageId);
    if (!container) return;
    var fields = {};
    container.querySelectorAll('[id^="page-' + pageId + '-"]').forEach(function (el) {
      var key = el.id.replace('page-' + pageId + '-', '');
      fields[key] = el.value;
    });
    apiCall('update_page', { pageId: pageId, fields: fields }, function (res) {
      // status shown by apiCall
    });
  }

  // ---- Gallery Project Management ----
  var _projectImages = []; // temp image list for the project being edited

  function loadGallery() {
    apiCall('list_gallery', null, function (res) {
      if (!res.success) return;
      var list = document.getElementById('gallery-projects-list');
      if (!list) return;
      list.innerHTML = '';
      if (!res.data.length) {
        list.innerHTML = '<li><span class="item-title" style="color:var(--admin-text-secondary);">No projects yet. Click "+ New Project" to get started.</span></li>';
        return;
      }
      res.data.forEach(function (project) {
        var imgCount = (project.images || []).length;
        var li = document.createElement('li');
        li.innerHTML =
          '<span class="item-title">' + escHtml(project.title) + ' <small style="color:var(--admin-text-secondary);">(' + imgCount + ' image' + (imgCount !== 1 ? 's' : '') + ')</small></span>' +
          '<span class="item-actions">' +
            '<button class="btn btn-sm btn-secondary" onclick="Admin.editGalleryProject(' + project.id + ')">Edit</button>' +
            '<button class="btn btn-sm btn-danger" onclick="Admin.deleteGalleryProject(' + project.id + ')">Delete</button>' +
          '</span>';
        list.appendChild(li);
      });
    });
  }

  function newGalleryProject() {
    document.getElementById('gallery-project-form-title').textContent = 'New Gallery Project';
    document.getElementById('gallery-project-form').reset();
    document.getElementById('gp-id').value = '';
    _projectImages = [];
    renderProjectImagesGrid();
    showModal('gallery-project-modal');
  }

  function editGalleryProject(id) {
    apiCall('get_gallery_project', { id: id }, function (res) {
      if (!res.success) return;
      var p = res.data;
      document.getElementById('gallery-project-form-title').textContent = 'Edit Gallery Project';
      document.getElementById('gp-id').value = p.id;
      document.getElementById('gp-title').value = p.title;
      document.getElementById('gp-order').value = p.order;
      _projectImages = (p.images || []).slice(); // clone
      renderProjectImagesGrid();
      showModal('gallery-project-modal');
    });
  }

  function renderProjectImagesGrid() {
    var grid = document.getElementById('gp-images-grid');
    if (!grid) return;
    grid.innerHTML = '';
    if (!_projectImages.length) {
      grid.innerHTML = '<p style="color:var(--admin-text-secondary);font-size:13px;">No images yet. Click "+ Add Image" below.</p>';
      return;
    }
    _projectImages.forEach(function (img, i) {
      var div = document.createElement('div');
      div.className = 'gallery-admin-item';
      div.innerHTML =
        '<img src="' + escHtml(img.src) + '" alt="' + escHtml(img.alt) + '" loading="lazy">' +
        '<button type="button" class="gallery-remove-btn" title="Remove" data-index="' + i + '">&times;</button>' +
        '<span class="gallery-order">' + (i === 0 ? 'Cover' : (i + 1)) + '</span>' +
        '<input type="text" class="gallery-img-desc" placeholder="Description..." value="' + escHtml(img.description || '') + '" data-index="' + i + '">';
      div.querySelector('.gallery-remove-btn').addEventListener('click', function () {
        _projectImages.splice(i, 1);
        renderProjectImagesGrid();
      });
      div.querySelector('.gallery-img-desc').addEventListener('input', function (e) {
        _projectImages[i].description = e.target.value;
      });
      grid.appendChild(div);
    });
  }

  function addProjectImage() {
    _pickerTargetId = '__project_image_add__';
    var grid = document.getElementById('picker-grid');
    grid.innerHTML = '<p style="color:var(--admin-text-secondary);">Loading...</p>';
    showModal('image-picker-modal');

    loadAllImages(function (images) {
      grid.innerHTML = '';
      images.forEach(function (path) {
        var div = document.createElement('div');
        div.className = 'image-grid-item';
        div.innerHTML = '<img src="' + escHtml(path) + '" alt="" loading="lazy">' +
          '<span class="image-path">' + escHtml(path.split('/').pop()) + '</span>';
        div.title = path;
        div.addEventListener('click', function () {
          hideModal('image-picker-modal');
          _pickerTargetId = null;
          var filename = path.split('/').pop().replace(/\.[^.]+$/, '').replace(/[-_]/g, ' ');
          _projectImages.push({ src: path, alt: filename, description: '' });
          renderProjectImagesGrid();
        });
        grid.appendChild(div);
      });
    });
  }

  function saveGalleryProject() {
    var form = document.getElementById('gallery-project-form');
    var data = {
      id: form.querySelector('#gp-id').value,
      title: form.querySelector('#gp-title').value,
      order: form.querySelector('#gp-order').value,
      images: _projectImages,
    };
    var action = data.id ? 'update_gallery_project' : 'create_gallery_project';
    apiCall(action, { project: data }, function (res) {
      if (res.success) {
        hideModal('gallery-project-modal');
        loadGallery();
      }
    });
  }

  function deleteGalleryProject(id) {
    if (!confirm('Delete this gallery project? This will remove it from the published page.')) return;
    apiCall('delete_gallery_project', { id: id }, function (res) {
      if (res.success) loadGallery();
    });
  }

  // ---- Image Upload (Images tab) ----
  function uploadImage() {
    var input = document.getElementById('image-upload-input');
    if (!input.files.length) {
      showStatus('Please select a file.', 'error');
      return;
    }
    var files = Array.from(input.files);
    var total = files.length;
    var done = 0;
    var failed = 0;

    showStatus('Uploading ' + total + ' file' + (total > 1 ? 's' : '') + '...', '');

    files.forEach(function (file) {
      apiCall('upload_image', { image: file }, function (res) {
        if (res.success) {
          done++;
        } else {
          failed++;
        }
        if (done + failed === total) {
          input.value = '';
          loadImages();
          if (failed) {
            showStatus(done + ' uploaded, ' + failed + ' failed.', 'error');
          } else {
            showStatus(done + ' image' + (done > 1 ? 's' : '') + ' uploaded.', 'success');
          }
        }
      });
    });
  }

  // ---- Delete Image ----
  function deleteImage(path) {
    if (!confirm('Delete this image? This cannot be undone.')) return;
    apiCall('delete_image', { path: path }, function (res) {
      if (res.success) loadImages();
    });
  }

  // ---- Modals ----
  function showModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('visible');
  }
  function hideModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('visible');
  }

  // ---- Utilities ----
  function escHtml(str) {
    var div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  function copyToClipboard(text) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text);
    } else {
      var ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
    }
  }

  // ---- Auto-slug from title ----
  document.addEventListener('input', function (e) {
    if (e.target.id === 'article-title' && !document.getElementById('article-id').value) {
      var slug = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
      document.getElementById('article-slug').value = slug;
    }
    if (e.target.id === 'cs-title' && !document.getElementById('cs-id').value) {
      var slug = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
      document.getElementById('cs-slug').value = slug;
    }
  });

  // ---- Init ----
  document.addEventListener('DOMContentLoaded', function () {
    initTabs();
    initPanels();
    loadArticles();
    loadCaseStudies();
    loadPages();
    loadGallery();
    loadImages();
  });

  // ---- Public API ----
  window.Admin = {
    editArticle: editArticle,
    newArticle: newArticle,
    saveArticle: saveArticle,
    deleteArticle: deleteArticle,
    editCaseStudy: editCaseStudy,
    newCaseStudy: newCaseStudy,
    saveCaseStudy: saveCaseStudy,
    deleteCaseStudy: deleteCaseStudy,
    savePage: savePage,
    newGalleryProject: newGalleryProject,
    editGalleryProject: editGalleryProject,
    saveGalleryProject: saveGalleryProject,
    deleteGalleryProject: deleteGalleryProject,
    addProjectImage: addProjectImage,
    uploadImage: uploadImage,
    openImagePicker: openImagePicker,
    clearImageField: clearImageField,
    pickerUpload: pickerUpload,
    showModal: showModal,
    hideModal: hideModal,
    showStatus: showStatus,
  };
})();
