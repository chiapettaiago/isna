<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Core/View.php';

class PageController extends Controller
{
    public function show(array $meta): void
    {
        $title = $meta['title'] ?? 'ISNA';
        $file = $meta['file'] ?? null;

        $data = ['pageTitle' => $title];

        if ($file) {
            $rel = ltrim($file, '/');
            $appViewPath = __DIR__ . '/../../app/views/' . $rel;
            $fallbackViewPath = __DIR__ . '/../../' . $rel;

            if (is_readable($appViewPath) || is_readable($fallbackViewPath)) {
                $this->view($file, $data);
                return;
            }
        }

        http_response_code($meta['status'] ?? 404);
        echo '<div class="container py-5 text-center">';
        echo '<h1 class="display-1">404</h1>';
        echo '<h2>Página Não Encontrada</h2>';
        echo '</div>';
    }
}
