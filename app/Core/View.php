<?php

declare(strict_types=1);

class View
{
    public static function render(string $file, array $data = []): void
    {
        // Provide variables to template
        extract($data, EXTR_SKIP);

        // Make common globals available to included templates (header/footer expect them)
        global $site_url, $path, $currentUser, $base_path;

        // Ensure header/footer paths are relative to project root
        $header = __DIR__ . '/../../templates/header.php';
        $footer = __DIR__ . '/../../templates/footer.php';

        if (is_readable($header)) {
            include $header;
        }

        // Prefer views in app/views/ to complete MVC migration. Fall back to original paths (pages/).
        $rel = ltrim($file, '/');
        $appViewPath = __DIR__ . '/../../app/views/' . $rel;
        $fallbackViewPath = __DIR__ . '/../../' . $rel;

        if (is_readable($appViewPath)) {
            include $appViewPath;
        } elseif (is_readable($fallbackViewPath)) {
            include $fallbackViewPath;
        } else {
            echo '<div class="container py-5 text-center">';
            echo '<h1 class="display-1">404</h1>';
            echo '<h2>Página Não Encontrada</h2>';
            echo '</div>';
        }

        if (is_readable($footer)) {
            include $footer;
        }
    }
}
