<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/CmsModel.php';

function cms_value(string $page, string $key, string $default = ''): string
{
    return trim(CmsModel::get($page, $key, $default));
}

function cms_text(string $page, string $key, string $default = ''): string
{
    return htmlspecialchars(cms_value($page, $key, $default), ENT_QUOTES, 'UTF-8');
}

function cms_paragraph(string $page, string $key, string $default = ''): string
{
    return nl2br(htmlspecialchars(cms_value($page, $key, $default), ENT_QUOTES, 'UTF-8'));
}

function cms_attr(string $page, string $key, string $default = ''): string
{
    return htmlspecialchars(cms_value($page, $key, $default), ENT_QUOTES, 'UTF-8');
}

function cms_render_custom_sections(string $viewFileOrPage): void
{
    $pageSlug = CmsModel::pageSlugFromView($viewFileOrPage);
    $sections = CmsModel::customSections($pageSlug, true);
    if (empty($sections)) return;

    echo '<section class="cms-extra-sections py-5">';
    echo '<div class="container">';
    foreach ($sections as $section) {
        $title = isset($section['title']) ? (string)$section['title'] : '';
        $content = isset($section['content']) ? (string)$section['content'] : '';
        if ($title === '' && $content === '') continue;

        echo '<article class="cms-extra-section mb-5">';
        if ($title !== '') {
            echo '<h2 class="cms-extra-section-title h3 fw-bold mb-3">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
        }
        if ($content !== '') {
            echo '<div class="cms-extra-section-content">' . nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) . '</div>';
        }
        echo '</article>';
    }
    echo '</div>';
    echo '</section>';
}
