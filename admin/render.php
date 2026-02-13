<?php
/**
 * render.php — Static HTML generator
 *
 * Builds static pages from templates + JSON data.
 * Called by api.php after saves.
 */

require_once __DIR__ . '/config.php';

class Renderer {

    // ---- Data loaders ----

    public static function loadArticles() {
        $path = DATA_PATH . '/articles.json';
        if (!file_exists($path)) return [];
        $data = json_decode(file_get_contents($path), true);
        usort($data, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });
        return $data;
    }

    public static function loadCaseStudies() {
        $path = DATA_PATH . '/case-studies.json';
        if (!file_exists($path)) return [];
        $data = json_decode(file_get_contents($path), true);
        usort($data, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });
        return $data;
    }

    public static function loadPages() {
        $path = DATA_PATH . '/pages.json';
        if (!file_exists($path)) return [];
        return json_decode(file_get_contents($path), true);
    }

    public static function loadGallery() {
        $path = DATA_PATH . '/gallery.json';
        if (!file_exists($path)) return [];
        $data = json_decode(file_get_contents($path), true);
        usort($data, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });
        return $data;
    }

    // ---- Article rendering ----

    public static function renderArticle($article, $allArticles) {
        $tpl = file_get_contents(TEMPLATES_PATH . '/article.html');

        // Secondary nav
        $navHtml = '';
        foreach ($allArticles as $a) {
            $current = ($a['id'] === $article['id']) ? ' aria-current="page"' : '';
            $navHtml .= '        <li><a href="/articles/' . self::esc($a['slug']) . '"' . $current . '>' . self::esc($a['navLabel']) . '</a></li>' . "\n";
        }

        // External link section
        $extSection = '';
        if (!empty($article['externalLink'])) {
            $extSection = '      <section aria-label="External publication">' . "\n" .
                          '        <p>' . "\n" .
                          '          <a href="' . self::esc($article['externalLink']) . '" target="_blank" rel="noopener noreferrer">' . "\n" .
                          '            Read the full article on Medium' . "\n" .
                          '          </a>' . "\n" .
                          '        </p>' . "\n" .
                          '      </section>';
        }

        $replacements = [
            '{{pageTitle}}'            => self::esc($article['title']),
            '{{title}}'                => self::esc($article['title']),
            '{{subtitle}}'             => self::esc($article['subtitle']),
            '{{heroImage}}'            => self::esc($article['heroImage']),
            '{{heroAlt}}'              => self::esc($article['heroAlt']),
            '{{body}}'                 => $article['body'],
            '{{secondaryNav}}'         => $navHtml,
            '{{externalLinkSection}}'  => $extSection,
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $tpl);

        // Write to disk
        $dir = BASE_PATH . '/articles/' . $article['slug'];
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . '/index.html', $html);
    }

    public static function renderAllArticles() {
        $articles = self::loadArticles();
        foreach ($articles as $article) {
            self::renderArticle($article, $articles);
        }
        self::renderArticlesIndex($articles);
    }

    public static function renderArticlesIndex($articles = null) {
        if ($articles === null) $articles = self::loadArticles();
        $pages = self::loadPages();
        $pageData = $pages['articles'] ?? [];

        $tpl = file_get_contents(TEMPLATES_PATH . '/articles-index.html');

        // Secondary nav
        $navHtml = '';
        foreach ($articles as $a) {
            $navHtml .= '        <li><a href="/articles/' . self::esc($a['slug']) . '">' . self::esc($a['navLabel']) . '</a></li>' . "\n";
        }

        // Article list items
        $listHtml = '';
        foreach ($articles as $a) {
            $listHtml .= '          <li><a href="/articles/' . self::esc($a['slug']) . '/">' . self::esc($a['title']) . '</a></li>' . "\n";
        }

        $replacements = [
            '{{heroTitle}}'      => self::esc($pageData['heroTitle'] ?? 'Articles'),
            '{{heroSubtitle}}'   => self::esc($pageData['heroSubtitle'] ?? ''),
            '{{heroText}}'       => self::esc($pageData['heroText'] ?? ''),
            '{{heroImageSlot}}'  => self::heroImageSlot($pageData),
            '{{overviewBody}}'   => self::esc($pageData['overviewBody'] ?? ''),
            '{{secondaryNav}}'   => $navHtml,
            '{{articlesList}}'   => $listHtml,
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $tpl);
        file_put_contents(BASE_PATH . '/articles/index.html', $html);
    }

    // ---- Case Study rendering ----

    public static function renderCaseStudy($cs, $allCaseStudies) {
        $tpl = file_get_contents(TEMPLATES_PATH . '/case-study.html');

        // Secondary nav
        $navHtml = '';
        foreach ($allCaseStudies as $item) {
            $current = ($item['id'] === $cs['id']) ? ' aria-current="page"' : '';
            $navHtml .= '        <li><a href="/experience/' . self::esc($item['slug']) . '"' . $current . '>' . self::esc($item['navLabel']) . '</a></li>' . "\n";
        }

        // Sections
        $sectionsHtml = '';
        foreach ($cs['sections'] as $i => $sec) {
            $class = ($i === 0) ? ' class="section-hero-title"' : '';
            $sectionsHtml .= '      <!-- ' . ucfirst($sec['id']) . ' -->' . "\n";
            $sectionsHtml .= '      <section aria-labelledby="' . self::esc($sec['id']) . '">' . "\n";
            $sectionsHtml .= '        <h2 id="' . self::esc($sec['id']) . '"' . $class . '>' . self::esc($sec['heading']) . '</h2>' . "\n";
            $sectionsHtml .= '        ' . $sec['body'] . "\n";
            // Optional section image
            if (!empty($sec['image'])) {
                $sectionsHtml .= '        <div class="section-image" aria-label="Section image">' . "\n";
                $sectionsHtml .= '          <img src="' . self::esc($sec['image']) . '" alt="' . self::esc($sec['heading']) . '" />' . "\n";
                $sectionsHtml .= '        </div>' . "\n";
            }
            $sectionsHtml .= '      </section>' . "\n";
        }

        // Hero media (video or image)
        $heroMediaHtml = '';
        if (!empty($cs['heroVideo'])) {
            $heroMediaHtml = '          <video src="' . self::esc($cs['heroVideo']) . '" autoplay loop muted playsinline aria-label="' . self::esc($cs['heroAlt']) . '"></video>';
        } elseif (!empty($cs['heroImage'])) {
            $heroMediaHtml = '          <img src="' . self::esc($cs['heroImage']) . '" alt="' . self::esc($cs['heroAlt']) . '" />';
        }

        // Next case study navigation
        $nextNavHtml = '';
        $currentIndex = -1;
        foreach ($allCaseStudies as $idx => $item) {
            if ($item['id'] === $cs['id']) { $currentIndex = $idx; break; }
        }
        if ($currentIndex !== -1) {
            $nextIndex = ($currentIndex + 1) % count($allCaseStudies);
            $nextCs = $allCaseStudies[$nextIndex];
            $nextNavHtml .= '      <nav class="study-nav" aria-label="Case study navigation">' . "\n";
            $nextNavHtml .= '        <a class="study-nav__link" href="/experience/' . self::esc($nextCs['slug']) . '/">' . "\n";
            $nextNavHtml .= '          Next' . "\n";
            $nextNavHtml .= '          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7 4l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>' . "\n";
            $nextNavHtml .= '        </a>' . "\n";
            $nextNavHtml .= '      </nav>' . "\n";
        }

        $replacements = [
            '{{pageTitle}}'     => self::esc($cs['subtitle']),
            '{{title}}'         => self::esc($cs['title']),
            '{{subtitle}}'      => self::esc($cs['subtitle']),
            '{{heroMedia}}'     => $heroMediaHtml,
            '{{secondaryNav}}'  => $navHtml,
            '{{sections}}'      => $sectionsHtml,
            '{{nextStudyNav}}'  => $nextNavHtml,
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $tpl);

        $dir = BASE_PATH . '/experience/' . $cs['slug'];
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . '/index.html', $html);
    }

    public static function renderAllCaseStudies() {
        $caseStudies = self::loadCaseStudies();
        foreach ($caseStudies as $cs) {
            self::renderCaseStudy($cs, $caseStudies);
        }
        self::renderExperienceIndex($caseStudies);
    }

    public static function renderExperienceIndex($caseStudies = null) {
        if ($caseStudies === null) $caseStudies = self::loadCaseStudies();
        $pages = self::loadPages();
        $pageData = $pages['experience'] ?? [];

        $tpl = file_get_contents(TEMPLATES_PATH . '/experience-index.html');

        // Secondary nav
        $navHtml = '';
        foreach ($caseStudies as $cs) {
            $navHtml .= '        <li><a href="/experience/' . self::esc($cs['slug']) . '">' . self::esc($cs['navLabel']) . '</a></li>' . "\n";
        }

        // Case study list items
        $listHtml = '';
        foreach ($caseStudies as $cs) {
            $listHtml .= '          <li><a href="/experience/' . self::esc($cs['slug']) . '/">' . self::esc($cs['title']) . '</a></li>' . "\n";
        }

        $replacements = [
            '{{heroTitle}}'        => self::esc($pageData['heroTitle'] ?? 'Experience'),
            '{{heroSubtitle}}'     => self::esc($pageData['heroSubtitle'] ?? ''),
            '{{heroText}}'         => self::esc($pageData['heroText'] ?? ''),
            '{{heroImageSlot}}'    => self::heroImageSlot($pageData),
            '{{overviewBody}}'     => self::esc($pageData['overviewBody'] ?? ''),
            '{{secondaryNav}}'     => $navHtml,
            '{{caseStudiesList}}'  => $listHtml,
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $tpl);
        file_put_contents(BASE_PATH . '/experience/index.html', $html);
    }

    // ---- Page text editing (editable regions in existing HTML) ----

    public static function updatePageRegions($pageId) {
        $pages = self::loadPages();
        $pageData = $pages[$pageId] ?? [];
        if (empty($pageData)) return false;

        // Site settings — update logo in _header.html
        if ($pageId === 'site') {
            self::updateSiteLogo($pageData['logo'] ?? '');
            return true;
        }

        $fileMap = [
            'home'       => BASE_PATH . '/index.html',
            'articles'   => BASE_PATH . '/articles/index.html',
            'experience' => BASE_PATH . '/experience/index.html',
            'gallery'    => BASE_PATH . '/gallery/index.html',
        ];

        $file = $fileMap[$pageId] ?? null;
        if (!$file || !file_exists($file)) return false;

        // For articles, experience, and gallery — re-render from template
        if ($pageId === 'articles') {
            self::renderArticlesIndex();
            return true;
        }
        if ($pageId === 'experience') {
            self::renderExperienceIndex();
            return true;
        }
        if ($pageId === 'gallery') {
            self::renderGalleryPage();
            return true;
        }

        // For home and gallery, use editable region markers
        // Each field needs its proper HTML wrapper so we don't strip tags
        $wrappers = [
            'home' => [
                'heroTitle'         => '<h1 id="hero-title">%s</h1>',
                'heroSubtitle'      => '<h4>%s</h4>',
                'heroText'          => '<p>%s</p>',
                'heroImage'         => '<img src="%s" alt="Portrait of Pej Vosooghi" />',
                'servicesImage'     => '%s', // handled specially below
                'logo1'             => '%s', // handled specially below
                'logo2'             => '%s',
                'logo3'             => '%s',
                'heroCards'         => '%s', // handled specially below
                'aboutTitle'        => '<h2 id="about-title" class="section-hero-title">%s</h2>',
                'aboutBody'         => '%s', // already HTML
            ],
            'gallery' => [
                'heroTitle'    => '<h1 id="hero-title">%s</h1>',
                'heroSubtitle' => '<h4>%s</h4>',
                'heroText'     => '<p>%s</p>',
            ],
        ];

        $pageWrappers = $wrappers[$pageId] ?? [];
        $html = file_get_contents($file);

        foreach ($pageData as $key => $value) {
            $pattern = '/<!-- editable:' . preg_quote($key, '/') . ' -->.*?<!-- \/editable:' . preg_quote($key, '/') . ' -->/s';
            // Skip individual CTA fields — they're composed into heroCards
            if (in_array($key, ['cta1Title', 'cta1Desc', 'cta1Link', 'cta2Title', 'cta2Desc', 'cta2Link'])) {
                continue;
            }

            // Special handling for CTA cards
            if ($key === 'heroCards') {
                $chevron = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7 4l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                $cards = '';
                $c1t = self::esc($pageData['cta1Title'] ?? '');
                $c1d = self::esc($pageData['cta1Desc'] ?? '');
                $c1l = self::esc($pageData['cta1Link'] ?? '');
                $c2t = self::esc($pageData['cta2Title'] ?? '');
                $c2d = self::esc($pageData['cta2Desc'] ?? '');
                $c2l = self::esc($pageData['cta2Link'] ?? '');
                if ($c1t || $c2t) {
                    $cards = '<div class="hero-cards">';
                    if ($c1t) {
                        $cards .= '<a href="' . $c1l . '" class="hero-card"><span class="hero-card__title">' . $c1t . '</span><span class="hero-card__desc">' . $c1d . '</span>' . $chevron . '</a>';
                    }
                    if ($c2t) {
                        $cards .= '<a href="' . $c2l . '" class="hero-card"><span class="hero-card__title">' . $c2t . '</span><span class="hero-card__desc">' . $c2d . '</span>' . $chevron . '</a>';
                    }
                    $cards .= '</div>';
                }
                $wrapped = $cards;
                $replacement = '<!-- editable:heroCards -->' . $wrapped . '<!-- /editable:heroCards -->';
                $html = preg_replace($pattern, $replacement, $html);
                continue;
            }

            // Special handling for image-only fields
            if (in_array($key, ['logo1', 'logo2', 'logo3'])) {
                $wrapped = !empty($value)
                    ? '<img src="' . self::esc($value) . '" alt="Company logo" />'
                    : '';
            } elseif ($key === 'servicesImage') {
                $wrapped = !empty($value)
                    ? '<div aria-label="Services image"><img src="' . self::esc($value) . '" alt="Services" /></div>'
                    : '';
            } elseif (isset($pageWrappers[$key])) {
                $wrapped = sprintf($pageWrappers[$key], $value);
            } else {
                $wrapped = $value;
            }
            $replacement = '<!-- editable:' . $key . ' -->' . $wrapped . '<!-- /editable:' . $key . ' -->';
            $html = preg_replace($pattern, $replacement, $html);
        }

        file_put_contents($file, $html);
        return true;
    }

    // ---- Gallery rendering ----

    public static function renderGalleryPage() {
        $pages = self::loadPages();
        $pageData = $pages['gallery'] ?? [];
        $gallery = self::loadGallery();

        $tpl = file_get_contents(TEMPLATES_PATH . '/gallery.html');

        // Load sections (default to product + information if none defined)
        $sections = $pageData['sections'] ?? [
            ['id' => 'product', 'label' => 'Product Design', 'order' => 1],
            ['id' => 'information', 'label' => 'Information Design', 'order' => 2],
        ];
        usort($sections, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });

        // Group projects by category
        $projectsByCategory = [];
        foreach ($gallery as $project) {
            $cat = $project['category'] ?? 'product';
            $projectsByCategory[$cat][] = $project;
        }

        // Build secondary nav
        $navHtml = '';
        foreach ($sections as $i => $sec) {
            $slug = self::slugify($sec['id']);
            $navHtml .= '        <li><a href="#' . $slug . '-top">' . self::esc($sec['label']) . '</a></li>' . "\n";
        }

        // Build gallery sections
        $sectionsHtml = '';
        foreach ($sections as $i => $sec) {
            $slug = self::slugify($sec['id']);
            $projects = $projectsByCategory[$sec['id']] ?? [];

            // First section anchor goes inside header (for tight divider spacing)
            if ($i === 0) {
                // Handled via heroImageSlot replacement — add anchor after header
            }

            // Anchor div (first section uses different placement)
            if ($i > 0) {
                $sectionsHtml .= '      <div id="' . $slug . '-top" aria-hidden="true"></div>' . "\n";
            }
            $sectionsHtml .= '      <hr class="section-divider" aria-hidden="true" />' . "\n";
            $sectionsHtml .= '      <section class="gallery-section" aria-labelledby="' . $slug . '">' . "\n";
            $sectionsHtml .= '        <h2 id="' . $slug . '" class="section-hero-title">' . self::esc($sec['label']) . '</h2>' . "\n";
            $sectionsHtml .= '        <div class="gallery-grid">' . "\n";
            $sectionsHtml .= self::buildGalleryProjectsHtml($projects);
            $sectionsHtml .= '        </div>' . "\n";
            $sectionsHtml .= '      </section>' . "\n\n";
        }

        // First section anchor — append inside heroImageSlot area
        $firstSlug = !empty($sections) ? self::slugify($sections[0]['id']) : 'gallery';
        $heroSlot = self::heroImageSlot($pageData);
        $heroSlot .= "\n" . '        <div id="' . $firstSlug . '-top" aria-hidden="true"></div>';

        $replacements = [
            '{{heroTitle}}'        => self::esc($pageData['heroTitle'] ?? 'Gallery'),
            '{{heroSubtitle}}'     => self::esc($pageData['heroSubtitle'] ?? ''),
            '{{heroText}}'         => self::esc($pageData['heroText'] ?? ''),
            '{{heroImageSlot}}'    => $heroSlot,
            '{{secondaryNav}}'     => $navHtml,
            '{{gallerySections}}'  => $sectionsHtml,
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $tpl);
        file_put_contents(BASE_PATH . '/gallery/index.html', $html);
    }

    private static function buildGalleryProjectsHtml($projects) {
        $html = '';
        foreach ($projects as $project) {
            $images = $project['images'] ?? [];
            if (empty($images)) continue;

            $coverImg = $images[0];
            $html .= '          <div class="gallery-project" data-project-id="' . intval($project['id']) . '">' . "\n";
            $html .= '            <h3 class="gallery-project__title">' . self::esc($project['title']) . '</h3>' . "\n";
            $html .= '            <div class="gallery-project__cover" data-description="' . self::esc($coverImg['description'] ?? '') . '">' . "\n";
            $html .= '              <img src="' . self::esc($coverImg['src']) . '" alt="' . self::esc($coverImg['alt']) . '" loading="lazy" />' . "\n";
            $html .= '            </div>' . "\n";

            if (count($images) > 1) {
                $html .= '            <div class="gallery-project__thumbs">' . "\n";
                foreach ($images as $i => $img) {
                    $active = ($i === 0) ? ' active' : '';
                    $html .= '              <button class="gallery-project__thumb' . $active . '" data-index="' . $i . '" data-description="' . self::esc($img['description'] ?? '') . '" aria-label="View image ' . ($i + 1) . '">' . "\n";
                    $html .= '                <img src="' . self::esc($img['src']) . '" alt="" loading="lazy" />' . "\n";
                    $html .= '              </button>' . "\n";
                }
                $html .= '            </div>' . "\n";
            }

            $html .= '          </div>' . "\n";
        }
        return $html;
    }

    // ---- Delete article directory ----

    public static function deleteArticleDir($slug) {
        $dir = BASE_PATH . '/articles/' . $slug;
        if (is_dir($dir)) {
            $file = $dir . '/index.html';
            if (file_exists($file)) unlink($file);
            rmdir($dir);
        }
    }

    // ---- Delete case study directory ----

    public static function deleteCaseStudyDir($slug) {
        $dir = BASE_PATH . '/experience/' . $slug;
        if (is_dir($dir)) {
            $file = $dir . '/index.html';
            if (file_exists($file)) unlink($file);
            rmdir($dir);
        }
    }

    // ---- Update site logo in _header.html ----

    public static function updateSiteLogo($logoPath) {
        if (empty($logoPath)) return false;

        $headerFile = BASE_PATH . '/_header.html';
        if (!file_exists($headerFile)) return false;

        $html = file_get_contents($headerFile);
        // Replace the logo img src
        $html = preg_replace(
            '/<a class="nav-logo"[^>]*>[\s]*<img src="[^"]*"/s',
            '<a class="nav-logo" href="/" aria-label="Home">' . "\n" . '      <img src="' . self::esc($logoPath) . '"',
            $html
        );
        file_put_contents($headerFile, $html);
        return true;
    }

    // ---- Hero image slot helper ----

    private static function heroImageSlot($pageData) {
        $src = $pageData['heroImage'] ?? '';
        if (empty($src)) return '';
        return '        <div aria-label="Hero image">' . "\n" .
               '          <img src="' . self::esc($src) . '" alt="Hero image" />' . "\n" .
               '        </div>';
    }

    // ---- Slug helper ----

    private static function slugify($str) {
        $str = strtolower(trim($str ?? ''));
        $str = preg_replace('/[^a-z0-9]+/', '-', $str);
        return trim($str, '-');
    }

    // ---- HTML-escape helper ----

    private static function esc($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}
