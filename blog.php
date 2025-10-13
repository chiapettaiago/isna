<?php

declare(strict_types=1);

function blog_config_path(): string
{
    return __DIR__ . '/config/blog.php';
}

function blog_default_config(): array
{
    return [
        'posts' => [],
    ];
}

function blog_slug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? '';
    $slug = preg_replace('/[\s-]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    if ($slug === '') {
        $slug = 'post-' . bin2hex(random_bytes(4));
    }

    return $slug;
}

function blog_sanitize_config(array $config): array
{
    $posts = [];

    if (isset($config['posts']) && is_array($config['posts'])) {
        foreach ($config['posts'] as $post) {
            if (!is_array($post)) {
                continue;
            }

            $title = isset($post['title']) && is_string($post['title']) ? trim($post['title']) : '';
            $summary = isset($post['summary']) && is_string($post['summary']) ? trim($post['summary']) : '';
            $content = isset($post['content']) && is_string($post['content']) ? trim($post['content']) : '';
            $author = isset($post['author']) && is_string($post['author']) ? trim($post['author']) : '';
            $publishedAt = isset($post['published_at']) && is_string($post['published_at']) ? trim($post['published_at']) : '';

            if ($title === '' || $content === '') {
                continue;
            }

            $id = isset($post['id']) && is_string($post['id']) ? trim($post['id']) : '';
            if ($id === '') {
                $id = blog_slug($title);
            }

            if (!preg_match('/^[a-z0-9\-]+$/', $id)) {
                $id = blog_slug($id);
            }

            if ($publishedAt === '') {
                $publishedAt = date('c');
            }

            $posts[] = [
                'id' => $id,
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'author' => $author !== '' ? $author : 'Equipe ISNA',
                'published_at' => $publishedAt,
            ];
        }
    }

    usort($posts, static function (array $a, array $b): int {
        return strcmp($b['published_at'], $a['published_at']);
    });

    return ['posts' => $posts];
}

function blog_load(): array
{
    $path = blog_config_path();

    if (is_readable($path)) {
        $data = include $path;

        if (is_array($data)) {
            return blog_sanitize_config($data);
        }
    }

    return blog_default_config();
}

function blog_render_config(array $config): string
{
    $lines = [
        '<?php',
        '',
        'declare(strict_types=1);',
        '',
        'return [',
        "    'posts' => [",
    ];

    foreach ($config['posts'] as $post) {
        $lines[] = '        [';
        $lines[] = "            'id' => '" . addslashes($post['id']) . "',";
        $lines[] = "            'title' => '" . addslashes($post['title']) . "',";
        $lines[] = "            'summary' => '" . addslashes($post['summary']) . "',";
        $lines[] = "            'content' => '" . addslashes($post['content']) . "',";
        $lines[] = "            'author' => '" . addslashes($post['author']) . "',";
        $lines[] = "            'published_at' => '" . addslashes($post['published_at']) . "',";
        $lines[] = '        ],';
    }

    $lines[] = '    ],';
    $lines[] = '];';
    $lines[] = '';

    return implode("\n", $lines);
}

function blog_save(array $config): bool
{
    $sanitized = blog_sanitize_config($config);
    $content = blog_render_config($sanitized);

    $path = blog_config_path();
    $tempPath = $path . '.' . bin2hex(random_bytes(8));

    if (file_put_contents($tempPath, $content) === false) {
        return false;
    }

    if (!@rename($tempPath, $path)) {
        @unlink($tempPath);
        return false;
    }

    return true;
}
