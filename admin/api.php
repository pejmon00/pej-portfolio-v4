<?php
/**
 * api.php — AJAX endpoint for all admin CRUD operations.
 * Every request must include action + csrf_token.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/render.php';
admin_session_start();

header('Content-Type: application/json');

// Auth check
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

// CSRF check (skip for GET-style list/read actions)
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$readActions = ['list_articles', 'list_case_studies', 'get_article', 'get_case_study', 'get_pages', 'list_images', 'list_gallery', 'get_gallery_project'];

if (!in_array($action, $readActions)) {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_verify($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }
}

// --- Helpers ---

function readJson($file) {
    $path = DATA_PATH . '/' . $file;
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function writeJson($file, $data) {
    $path = DATA_PATH . '/' . $file;
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function nextId($items) {
    $maxId = 0;
    foreach ($items as $item) {
        if (($item['id'] ?? 0) > $maxId) $maxId = $item['id'];
    }
    return $maxId + 1;
}

function sanitizeSlug($slug) {
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function respond($success, $message = '', $data = null) {
    $out = ['success' => $success, 'message' => $message];
    if ($data !== null) $out['data'] = $data;
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// --- Route ---

switch ($action) {

    // ==================== ARTICLES ====================

    case 'list_articles':
        $articles = readJson('articles.json');
        usort($articles, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });
        respond(true, '', $articles);
        break;

    case 'get_article':
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        $articles = readJson('articles.json');
        foreach ($articles as $a) {
            if ($a['id'] === $id) respond(true, '', $a);
        }
        respond(false, 'Article not found.');
        break;

    case 'create_article':
        $input = json_decode($_POST['article'] ?? '{}', true);
        if (empty($input['title']) || empty($input['slug'])) {
            respond(false, 'Title and slug are required.');
        }
        $articles = readJson('articles.json');
        $input['id'] = nextId($articles);
        $input['slug'] = sanitizeSlug($input['slug']);
        $input['order'] = intval($input['order'] ?? (count($articles) + 1));
        $articles[] = $input;
        writeJson('articles.json', $articles);
        Renderer::renderAllArticles();
        respond(true, 'Article created and published.');
        break;

    case 'update_article':
        $input = json_decode($_POST['article'] ?? '{}', true);
        $id = intval($input['id'] ?? 0);
        if (!$id) respond(false, 'Invalid article ID.');
        $articles = readJson('articles.json');
        $found = false;
        foreach ($articles as &$a) {
            if ($a['id'] === $id) {
                $oldSlug = $a['slug'];
                $a['title'] = $input['title'] ?? $a['title'];
                $a['subtitle'] = $input['subtitle'] ?? $a['subtitle'];
                $a['slug'] = sanitizeSlug($input['slug'] ?? $a['slug']);
                $a['navLabel'] = $input['navLabel'] ?? $a['navLabel'];
                $a['heroImage'] = $input['heroImage'] ?? $a['heroImage'];
                $a['heroAlt'] = $input['heroAlt'] ?? $a['heroAlt'];
                $a['body'] = $input['body'] ?? $a['body'];
                $a['externalLink'] = $input['externalLink'] ?? $a['externalLink'];
                $a['order'] = intval($input['order'] ?? $a['order']);
                // If slug changed, remove old directory
                if ($oldSlug !== $a['slug']) {
                    Renderer::deleteArticleDir($oldSlug);
                }
                $found = true;
                break;
            }
        }
        unset($a);
        if (!$found) respond(false, 'Article not found.');
        writeJson('articles.json', $articles);
        Renderer::renderAllArticles();
        respond(true, 'Article updated and published.');
        break;

    case 'delete_article':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) respond(false, 'Invalid article ID.');
        $articles = readJson('articles.json');
        $slug = '';
        $articles = array_values(array_filter($articles, function ($a) use ($id, &$slug) {
            if ($a['id'] === $id) { $slug = $a['slug']; return false; }
            return true;
        }));
        if ($slug) Renderer::deleteArticleDir($slug);
        writeJson('articles.json', $articles);
        Renderer::renderAllArticles();
        respond(true, 'Article deleted.');
        break;

    // ==================== CASE STUDIES ====================

    case 'list_case_studies':
        $cs = readJson('case-studies.json');
        usort($cs, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });
        respond(true, '', $cs);
        break;

    case 'get_case_study':
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        $cs = readJson('case-studies.json');
        foreach ($cs as $item) {
            if ($item['id'] === $id) respond(true, '', $item);
        }
        respond(false, 'Case study not found.');
        break;

    case 'create_case_study':
        $input = json_decode($_POST['case_study'] ?? '{}', true);
        if (empty($input['title']) || empty($input['slug'])) {
            respond(false, 'Title and slug are required.');
        }
        $cs = readJson('case-studies.json');
        $input['id'] = nextId($cs);
        $input['slug'] = sanitizeSlug($input['slug']);
        $input['order'] = intval($input['order'] ?? (count($cs) + 1));
        if (is_string($input['sections'] ?? null)) {
            $input['sections'] = json_decode($input['sections'], true) ?: [];
        }
        $cs[] = $input;
        writeJson('case-studies.json', $cs);
        Renderer::renderAllCaseStudies();
        respond(true, 'Case study created and published.');
        break;

    case 'update_case_study':
        $input = json_decode($_POST['case_study'] ?? '{}', true);
        $id = intval($input['id'] ?? 0);
        if (!$id) respond(false, 'Invalid case study ID.');
        $cs = readJson('case-studies.json');
        $found = false;
        foreach ($cs as &$item) {
            if ($item['id'] === $id) {
                $oldSlug = $item['slug'];
                $item['title'] = $input['title'] ?? $item['title'];
                $item['subtitle'] = $input['subtitle'] ?? $item['subtitle'];
                $item['slug'] = sanitizeSlug($input['slug'] ?? $item['slug']);
                $item['navLabel'] = $input['navLabel'] ?? $item['navLabel'];
                $item['heroImage'] = $input['heroImage'] ?? $item['heroImage'];
                $item['heroAlt'] = $input['heroAlt'] ?? $item['heroAlt'];
                $item['order'] = intval($input['order'] ?? $item['order']);
                if (isset($input['sections'])) {
                    $sections = $input['sections'];
                    if (is_string($sections)) {
                        $sections = json_decode($sections, true) ?: [];
                    }
                    $item['sections'] = $sections;
                }
                if ($oldSlug !== $item['slug']) {
                    Renderer::deleteCaseStudyDir($oldSlug);
                }
                $found = true;
                break;
            }
        }
        unset($item);
        if (!$found) respond(false, 'Case study not found.');
        writeJson('case-studies.json', $cs);
        Renderer::renderAllCaseStudies();
        respond(true, 'Case study updated and published.');
        break;

    case 'delete_case_study':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) respond(false, 'Invalid case study ID.');
        $cs = readJson('case-studies.json');
        $slug = '';
        $cs = array_values(array_filter($cs, function ($item) use ($id, &$slug) {
            if ($item['id'] === $id) { $slug = $item['slug']; return false; }
            return true;
        }));
        if ($slug) Renderer::deleteCaseStudyDir($slug);
        writeJson('case-studies.json', $cs);
        Renderer::renderAllCaseStudies();
        respond(true, 'Case study deleted.');
        break;

    // ==================== PAGES ====================

    case 'get_pages':
        $pages = Renderer::loadPages();
        respond(true, '', $pages);
        break;

    case 'update_page':
        $pageId = $_POST['pageId'] ?? '';
        $fields = json_decode($_POST['fields'] ?? '{}', true);
        if (!$pageId || empty($fields)) {
            respond(false, 'Page ID and fields are required.');
        }
        $pages = Renderer::loadPages();
        if (!isset($pages[$pageId])) {
            $pages[$pageId] = [];
        }
        foreach ($fields as $key => $value) {
            $pages[$pageId][$key] = $value;
        }
        writeJson('pages.json', $pages);
        Renderer::updatePageRegions($pageId);
        respond(true, ucfirst($pageId) . ' page updated and published.');
        break;

    // ==================== IMAGES ====================

    case 'list_images':
        $images = [];
        // Scan main assets/images
        $mainDir = BASE_PATH . '/assets/images';
        if (is_dir($mainDir)) {
            $files = scandir($mainDir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..' || is_dir($mainDir . '/' . $f)) continue;
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if (in_array($ext, ALLOWED_EXTENSIONS)) {
                    $images[] = '/assets/images/' . $f;
                }
            }
        }
        // Scan uploads
        $uploadDir = BASE_PATH . UPLOAD_DIR;
        if (is_dir($uploadDir)) {
            $files = scandir($uploadDir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if (in_array($ext, ALLOWED_EXTENSIONS)) {
                    $images[] = UPLOAD_DIR . '/' . $f;
                }
            }
        }
        respond(true, '', $images);
        break;

    case 'upload_image':
        if (empty($_FILES['image'])) {
            respond(false, 'No file uploaded.');
        }
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            respond(false, 'Upload error code: ' . $file['error']);
        }
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            respond(false, 'File exceeds maximum size of 5 MB.');
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            respond(false, 'File type not allowed. Use: ' . implode(', ', ALLOWED_EXTENSIONS));
        }
        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ALLOWED_MIMES)) {
            respond(false, 'Invalid file MIME type: ' . $mime);
        }
        // Sanitize filename
        $basename = pathinfo($file['name'], PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '-', $basename);
        $basename = substr($basename, 0, 60);
        $newName = $basename . '-' . time() . '.' . $ext;

        $uploadDir = BASE_PATH . UPLOAD_DIR;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $dest = $uploadDir . '/' . $newName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            respond(false, 'Failed to save uploaded file.');
        }
        $webPath = UPLOAD_DIR . '/' . $newName;
        respond(true, 'Image uploaded: ' . $newName, $webPath);
        break;

    case 'delete_image':
        $path = $_POST['path'] ?? '';
        if (!$path) respond(false, 'No image path provided.');
        // Only allow deleting from uploads directory
        if (strpos($path, UPLOAD_DIR . '/') !== 0) {
            respond(false, 'Only uploaded images can be deleted.');
        }
        $fullPath = BASE_PATH . '/' . ltrim($path, '/');
        if (!file_exists($fullPath)) {
            respond(false, 'File not found.');
        }
        if (!unlink($fullPath)) {
            respond(false, 'Failed to delete file.');
        }
        respond(true, 'Image deleted.');
        break;

    // ==================== GALLERY (project-based) ====================

    case 'list_gallery':
        $gallery = readJson('gallery.json');
        usort($gallery, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });
        respond(true, '', $gallery);
        break;

    case 'get_gallery_project':
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        $gallery = readJson('gallery.json');
        foreach ($gallery as $project) {
            if ($project['id'] === $id) respond(true, '', $project);
        }
        respond(false, 'Gallery project not found.');
        break;

    case 'create_gallery_project':
        $input = json_decode($_POST['project'] ?? '{}', true);
        if (empty($input['title'])) respond(false, 'Project title is required.');
        $gallery = readJson('gallery.json');
        $newProject = [
            'id' => nextId($gallery),
            'title' => $input['title'],
            'images' => $input['images'] ?? [],
            'order' => intval($input['order'] ?? (count($gallery) + 1)),
            'category' => $input['category'] ?? 'product',
        ];
        $gallery[] = $newProject;
        writeJson('gallery.json', $gallery);
        Renderer::renderGalleryPage();
        respond(true, 'Gallery project created and published.');
        break;

    case 'update_gallery_project':
        $input = json_decode($_POST['project'] ?? '{}', true);
        $id = intval($input['id'] ?? 0);
        if (!$id) respond(false, 'Invalid project ID.');
        $gallery = readJson('gallery.json');
        $found = false;
        foreach ($gallery as &$project) {
            if ($project['id'] === $id) {
                $project['title'] = $input['title'] ?? $project['title'];
                $project['order'] = intval($input['order'] ?? $project['order']);
                $project['category'] = $input['category'] ?? $project['category'] ?? 'product';
                if (isset($input['images'])) {
                    $project['images'] = $input['images'];
                }
                $found = true;
                break;
            }
        }
        unset($project);
        if (!$found) respond(false, 'Gallery project not found.');
        writeJson('gallery.json', $gallery);
        Renderer::renderGalleryPage();
        respond(true, 'Gallery project updated and published.');
        break;

    case 'reorder_gallery_project':
        $id = intval($_POST['id'] ?? 0);
        $direction = intval($_POST['direction'] ?? 0); // -1 = up, 1 = down
        if (!$id || !$direction) respond(false, 'Invalid reorder request.');
        $gallery = readJson('gallery.json');
        // Group by category, reorder within category
        $byCategory = [];
        foreach ($gallery as $project) {
            $cat = $project['category'] ?? 'product';
            $byCategory[$cat][] = $project;
        }
        foreach ($byCategory as $cat => &$items) {
            usort($items, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });
            // Find the project in this group
            $idx = -1;
            foreach ($items as $i => $item) {
                if ($item['id'] === $id) { $idx = $i; break; }
            }
            if ($idx === -1) continue;
            $newIdx = $idx + $direction;
            if ($newIdx < 0 || $newIdx >= count($items)) continue;
            // Swap
            $temp = $items[$idx];
            $items[$idx] = $items[$newIdx];
            $items[$newIdx] = $temp;
            // Re-assign order values
            foreach ($items as $i => &$item) { $item['order'] = $i + 1; }
            unset($item);
        }
        unset($items);
        // Flatten back
        $gallery = [];
        foreach ($byCategory as $items) {
            foreach ($items as $item) { $gallery[] = $item; }
        }
        writeJson('gallery.json', $gallery);
        Renderer::renderGalleryPage();
        respond(true, 'Gallery order updated.');
        break;

    case 'delete_gallery_project':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) respond(false, 'Invalid project ID.');
        $gallery = readJson('gallery.json');
        $gallery = array_values(array_filter($gallery, function ($project) use ($id) {
            return $project['id'] !== $id;
        }));
        // Re-order
        foreach ($gallery as $i => &$project) { $project['order'] = $i + 1; }
        unset($project);
        writeJson('gallery.json', $gallery);
        Renderer::renderGalleryPage();
        respond(true, 'Gallery project deleted.');
        break;

    default:
        respond(false, 'Unknown action: ' . $action);
}
