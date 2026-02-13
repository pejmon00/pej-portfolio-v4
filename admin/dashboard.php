<?php require_once __DIR__ . '/auth.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>">
  <link rel="stylesheet" href="/admin/assets/admin.css">
  <meta name="robots" content="noindex,nofollow">
</head>
<body>

<!-- Header -->
<div class="admin-header">
  <h1>Dashboard</h1>
  <a href="/admin/logout.php">Log out</a>
</div>

<!-- Tabs -->
<div class="tabs">
  <button class="tab-btn active" data-tab="tab-articles">Articles</button>
  <button class="tab-btn" data-tab="tab-case-studies">Case Studies</button>
  <button class="tab-btn" data-tab="tab-pages">Pages</button>
  <button class="tab-btn" data-tab="tab-gallery">Gallery</button>
  <button class="tab-btn" data-tab="tab-images">Images</button>
</div>

<!-- ============ ARTICLES TAB ============ -->
<div id="tab-articles" class="tab-panel active">
  <div class="card">
    <div class="card-header">
      <h3>Articles</h3>
      <button class="btn btn-primary btn-sm" onclick="Admin.newArticle()">+ New Article</button>
    </div>
    <ul class="item-list" id="articles-list">
      <li><span class="item-title">Loading...</span></li>
    </ul>
  </div>
</div>

<!-- ============ CASE STUDIES TAB ============ -->
<div id="tab-case-studies" class="tab-panel">
  <div class="card">
    <div class="card-header">
      <h3>Case Studies</h3>
      <button class="btn btn-primary btn-sm" onclick="Admin.newCaseStudy()">+ New Case Study</button>
    </div>
    <ul class="item-list" id="case-studies-list">
      <li><span class="item-title">Loading...</span></li>
    </ul>
  </div>
</div>

<!-- ============ PAGES TAB ============ -->
<div id="tab-pages" class="tab-panel">

  <!-- Site Settings -->
  <div id="page-site">
    <button class="panel-toggle">Site Settings</button>
    <div class="panel-body">
      <div class="form-group">
        <label>Site Logo</label>
        <div class="image-field">
          <input type="hidden" id="page-site-logo">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-site-logo')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-site-logo')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <button class="btn btn-primary" onclick="Admin.savePage('site')">Save &amp; Publish</button>
    </div>
  </div>

  <!-- Home -->
  <div id="page-home">
    <button class="panel-toggle">Home Page</button>
    <div class="panel-body">
      <div class="form-group">
        <label for="page-home-heroTitle">Hero Title</label>
        <input type="text" id="page-home-heroTitle">
      </div>
      <div class="form-group">
        <label for="page-home-heroSubtitle">Hero Subtitle</label>
        <input type="text" id="page-home-heroSubtitle">
      </div>
      <div class="form-group">
        <label for="page-home-heroText">Hero Text</label>
        <textarea id="page-home-heroText" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label>Hero Image</label>
        <div class="image-field">
          <input type="hidden" id="page-home-heroImage">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-home-heroImage')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-home-heroImage')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <hr style="margin:20px 0;">
      <h3>CTA Cards</h3>
      <div class="form-group">
        <label for="page-home-cta1Title">CTA 1 Title</label>
        <input type="text" id="page-home-cta1Title">
      </div>
      <div class="form-group">
        <label for="page-home-cta1Desc">CTA 1 Description</label>
        <input type="text" id="page-home-cta1Desc">
      </div>
      <div class="form-group">
        <label for="page-home-cta1Link">CTA 1 Link</label>
        <input type="text" id="page-home-cta1Link">
      </div>
      <div class="form-group">
        <label for="page-home-cta2Title">CTA 2 Title</label>
        <input type="text" id="page-home-cta2Title">
      </div>
      <div class="form-group">
        <label for="page-home-cta2Desc">CTA 2 Description</label>
        <input type="text" id="page-home-cta2Desc">
      </div>
      <div class="form-group">
        <label for="page-home-cta2Link">CTA 2 Link</label>
        <input type="text" id="page-home-cta2Link">
      </div>
      <hr style="margin:20px 0;">
      <div class="form-group">
        <label>Services Image</label>
        <div class="image-field">
          <input type="hidden" id="page-home-servicesImage">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-home-servicesImage')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-home-servicesImage')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <hr style="margin:20px 0;">
      <h3>Credibility Logos</h3>
      <div class="form-group">
        <label>Logo 1</label>
        <div class="image-field">
          <input type="hidden" id="page-home-logo1">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-home-logo1')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-home-logo1')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Logo 2</label>
        <div class="image-field">
          <input type="hidden" id="page-home-logo2">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-home-logo2')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-home-logo2')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Logo 3</label>
        <div class="image-field">
          <input type="hidden" id="page-home-logo3">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-home-logo3')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-home-logo3')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <hr style="margin:20px 0;">
      <div class="form-group">
        <label for="page-home-aboutTitle">About Title</label>
        <input type="text" id="page-home-aboutTitle">
      </div>
      <div class="form-group">
        <label for="page-home-aboutBody">About Body (HTML)</label>
        <textarea id="page-home-aboutBody" rows="8"></textarea>
      </div>
      <button class="btn btn-primary" onclick="Admin.savePage('home')">Save &amp; Publish</button>
    </div>
  </div>

  <!-- Articles Overview -->
  <div id="page-articles">
    <button class="panel-toggle">Articles Overview</button>
    <div class="panel-body">
      <div class="form-group">
        <label for="page-articles-heroTitle">Hero Title</label>
        <input type="text" id="page-articles-heroTitle">
      </div>
      <div class="form-group">
        <label for="page-articles-heroSubtitle">Hero Subtitle</label>
        <input type="text" id="page-articles-heroSubtitle">
      </div>
      <div class="form-group">
        <label for="page-articles-heroText">Hero Text</label>
        <textarea id="page-articles-heroText" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label>Hero Image</label>
        <div class="image-field">
          <input type="hidden" id="page-articles-heroImage">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-articles-heroImage')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-articles-heroImage')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="page-articles-overviewBody">Overview Body</label>
        <textarea id="page-articles-overviewBody" rows="6"></textarea>
      </div>
      <button class="btn btn-primary" onclick="Admin.savePage('articles')">Save &amp; Publish</button>
    </div>
  </div>

  <!-- Experience Overview -->
  <div id="page-experience">
    <button class="panel-toggle">Experience Overview</button>
    <div class="panel-body">
      <div class="form-group">
        <label for="page-experience-heroTitle">Hero Title</label>
        <input type="text" id="page-experience-heroTitle">
      </div>
      <div class="form-group">
        <label for="page-experience-heroSubtitle">Hero Subtitle</label>
        <input type="text" id="page-experience-heroSubtitle">
      </div>
      <div class="form-group">
        <label for="page-experience-heroText">Hero Text</label>
        <textarea id="page-experience-heroText" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label>Hero Image</label>
        <div class="image-field">
          <input type="hidden" id="page-experience-heroImage">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-experience-heroImage')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-experience-heroImage')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="page-experience-overviewBody">Overview Body</label>
        <textarea id="page-experience-overviewBody" rows="6"></textarea>
      </div>
      <button class="btn btn-primary" onclick="Admin.savePage('experience')">Save &amp; Publish</button>
    </div>
  </div>

  <!-- Gallery -->
  <div id="page-gallery">
    <button class="panel-toggle">Gallery</button>
    <div class="panel-body">
      <div class="form-group">
        <label for="page-gallery-heroTitle">Hero Title</label>
        <input type="text" id="page-gallery-heroTitle">
      </div>
      <div class="form-group">
        <label for="page-gallery-heroSubtitle">Hero Subtitle</label>
        <input type="text" id="page-gallery-heroSubtitle">
      </div>
      <div class="form-group">
        <label for="page-gallery-heroText">Hero Text</label>
        <textarea id="page-gallery-heroText" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label>Hero Image</label>
        <div class="image-field">
          <input type="hidden" id="page-gallery-heroImage">
          <img class="image-field__preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('page-gallery-heroImage')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('page-gallery-heroImage')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <hr style="margin:20px 0;">
      <h3>Gallery Sections</h3>
      <p style="font-size:13px;color:var(--admin-text-secondary);margin:0 0 12px;">
        Each section appears as a titled gallery grid with its own page navigation link. Gallery projects are assigned to a section via their Category.
      </p>
      <div id="gallery-sections-list"></div>
      <button type="button" class="btn btn-secondary btn-sm" onclick="Admin.addGallerySection()" style="margin-top:8px;">+ Add Section</button>
      <div style="margin-top:16px;">
        <button class="btn btn-primary" onclick="Admin.savePage('gallery')">Save &amp; Publish</button>
      </div>
    </div>
  </div>
</div>

<!-- ============ GALLERY TAB ============ -->
<div id="tab-gallery" class="tab-panel">
  <div class="card">
    <div class="card-header">
      <h3>Gallery Projects</h3>
      <button class="btn btn-primary btn-sm" onclick="Admin.newGalleryProject()">+ New Project</button>
    </div>
    <p style="font-size:13px;color:var(--admin-text-secondary);margin:0 0 12px;">
      Each project appears as a card on the gallery page with a title, cover image, and thumbnail strip.
    </p>
    <ul class="item-list" id="gallery-projects-list">
      <li><span class="item-title">Loading...</span></li>
    </ul>
  </div>
</div>

<!-- ============ GALLERY PROJECT MODAL ============ -->
<div class="modal-overlay" id="gallery-project-modal">
  <div class="modal" style="max-width:680px;">
    <h2 id="gallery-project-form-title">New Gallery Project</h2>
    <form id="gallery-project-form" onsubmit="event.preventDefault(); Admin.saveGalleryProject();">
      <input type="hidden" id="gp-id">
      <div class="form-group">
        <label for="gp-title">Project Title</label>
        <input type="text" id="gp-title" required>
      </div>
      <div class="form-group">
        <label for="gp-category">Category</label>
        <select id="gp-category">
        </select>
      </div>
      <div class="form-group">
        <label for="gp-order">Order</label>
        <input type="number" id="gp-order" value="1" min="1">
      </div>
      <hr style="margin:20px 0;">
      <h3>Images</h3>
      <p style="font-size:13px;color:var(--admin-text-secondary);margin:0 0 12px;">
        The first image is used as the cover. All images appear in the thumbnail strip and lightbox.
      </p>
      <div class="gallery-admin-grid" id="gp-images-grid"></div>
      <button type="button" class="btn btn-secondary btn-sm" onclick="Admin.addProjectImage()" style="margin-top:8px;">+ Add Image</button>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="Admin.hideModal('gallery-project-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save &amp; Publish</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ IMAGES TAB ============ -->
<div id="tab-images" class="tab-panel">
  <div class="card">
    <div class="card-header">
      <h3>Upload Image</h3>
    </div>
    <div style="display:flex;gap:12px;align-items:flex-end;">
      <div class="form-group" style="flex:1;margin-bottom:0;">
        <label for="image-upload-input">Choose files (jpg, png, gif, webp, svg — max 5 MB each)</label>
        <input type="file" id="image-upload-input" accept=".jpg,.jpeg,.png,.gif,.webp,.svg" multiple>
      </div>
      <button class="btn btn-primary" onclick="Admin.uploadImage()" style="margin-bottom:0;">Upload</button>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <h3>Uploaded Images</h3>
    </div>
    <p style="font-size:13px;color:var(--admin-text-secondary);">Click an image to copy its path.</p>
    <div class="image-grid" id="images-grid"></div>
  </div>
</div>

<!-- ============ ARTICLE MODAL ============ -->
<div class="modal-overlay" id="article-modal">
  <div class="modal">
    <h2 id="article-form-title">New Article</h2>
    <form id="article-form" onsubmit="event.preventDefault(); Admin.saveArticle();">
      <input type="hidden" id="article-id">
      <div class="form-group">
        <label for="article-title">Title</label>
        <input type="text" id="article-title" required>
      </div>
      <div class="form-group">
        <label for="article-subtitle">Subtitle</label>
        <input type="text" id="article-subtitle" value="By Pej Vosooghi">
      </div>
      <div class="form-group">
        <label for="article-slug">Slug</label>
        <input type="text" id="article-slug" required>
      </div>
      <div class="form-group">
        <label for="article-navLabel">Nav Label</label>
        <input type="text" id="article-navLabel">
      </div>
      <div class="form-group">
        <label for="article-order">Order</label>
        <input type="number" id="article-order" value="1" min="1">
      </div>
      <div class="form-group">
        <label>Hero Image</label>
        <div class="image-field">
          <input type="hidden" id="article-heroImage">
          <img class="image-field__preview" id="article-hero-preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('article-heroImage')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('article-heroImage')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="article-heroAlt">Hero Image Alt Text</label>
        <input type="text" id="article-heroAlt">
      </div>
      <div class="form-group">
        <label for="article-body">Body (HTML)</label>
        <textarea id="article-body" rows="12"></textarea>
      </div>
      <div class="form-group">
        <label for="article-externalLink">External Link (optional)</label>
        <input type="url" id="article-externalLink">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="Admin.hideModal('article-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save &amp; Publish</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ CASE STUDY MODAL ============ -->
<div class="modal-overlay" id="cs-modal">
  <div class="modal">
    <h2 id="cs-form-title">New Case Study</h2>
    <form id="cs-form" onsubmit="event.preventDefault(); Admin.saveCaseStudy();">
      <input type="hidden" id="cs-id">
      <div class="form-group">
        <label for="cs-title">Title</label>
        <input type="text" id="cs-title" required>
      </div>
      <div class="form-group">
        <label for="cs-subtitle">Subtitle</label>
        <input type="text" id="cs-subtitle">
      </div>
      <div class="form-group">
        <label for="cs-slug">Slug</label>
        <input type="text" id="cs-slug" required>
      </div>
      <div class="form-group">
        <label for="cs-navLabel">Nav Label</label>
        <input type="text" id="cs-navLabel">
      </div>
      <div class="form-group">
        <label for="cs-order">Order</label>
        <input type="number" id="cs-order" value="1" min="1">
      </div>
      <div class="form-group">
        <label>Hero Image</label>
        <div class="image-field">
          <input type="hidden" id="cs-heroImage">
          <img class="image-field__preview" id="cs-hero-preview" src="" alt="Preview">
          <div class="image-field__actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="Admin.openImagePicker('cs-heroImage')">Choose Image</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="Admin.clearImageField('cs-heroImage')">Remove</button>
            <span class="image-field__path"></span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="cs-heroAlt">Hero Image Alt Text</label>
        <input type="text" id="cs-heroAlt">
      </div>
      <hr style="margin:20px 0;">
      <h3>Sections</h3>
      <div id="cs-sections"></div>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="Admin.hideModal('cs-modal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save &amp; Publish</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ IMAGE PICKER MODAL ============ -->
<div class="modal-overlay" id="image-picker-modal">
  <div class="modal" style="max-width:720px;">
    <h2>Choose Image</h2>
    <div style="display:flex;gap:12px;align-items:flex-end;margin-bottom:16px;">
      <div class="form-group" style="flex:1;margin-bottom:0;">
        <label for="picker-upload-input">Upload new image</label>
        <input type="file" id="picker-upload-input" accept=".jpg,.jpeg,.png,.gif,.webp,.svg">
      </div>
      <button class="btn btn-primary btn-sm" onclick="Admin.pickerUpload()">Upload</button>
    </div>
    <hr style="margin:0 0 16px;">
    <p style="font-size:13px;color:var(--admin-text-secondary);margin:0 0 8px;">Select an image:</p>
    <div class="image-grid" id="picker-grid"></div>
    <div class="modal-actions">
      <button type="button" class="btn btn-secondary" onclick="Admin.hideModal('image-picker-modal')">Cancel</button>
    </div>
  </div>
</div>

<!-- Status Bar -->
<div id="status-bar" class="status-bar"></div>

<script src="/admin/assets/admin.js"></script>
</body>
</html>
