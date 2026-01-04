<?php

declare(strict_types=1);

class BlogModel
{
    public static function configPath(): string
    {
        return __DIR__ . '/../../config/blog.php';
    }

    public static function defaultConfig(): array
    {
        return ['posts' => []];
    }

    public static function slug(string $title): string
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

    public static function sanitizeConfig(array $config): array
    {
        $posts = [];

        if (isset($config['posts']) && is_array($config['posts'])) {
            foreach ($config['posts'] as $post) {
                if (!is_array($post)) continue;

                $title = isset($post['title']) && is_string($post['title']) ? trim($post['title']) : '';
                $summary = isset($post['summary']) && is_string($post['summary']) ? trim($post['summary']) : '';
                $content = isset($post['content']) && is_string($post['content']) ? trim($post['content']) : '';
                $author = isset($post['author']) && is_string($post['author']) ? trim($post['author']) : '';
                $publishedAt = isset($post['published_at']) && is_string($post['published_at']) ? trim($post['published_at']) : '';

                if ($title === '' || $content === '') continue;

                $id = isset($post['id']) && is_string($post['id']) ? trim($post['id']) : '';
                if ($id === '') $id = self::slug($title);
                if (!preg_match('/^[a-z0-9\-]+$/', $id)) $id = self::slug($id);
                if ($publishedAt === '') $publishedAt = date('c');

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

    public static function load(): array
    {
        $path = self::configPath();
        if (is_readable($path)) {
            $data = include $path;
            if (is_array($data)) return self::sanitizeConfig($data);
        }
        return self::defaultConfig();
    }

    public static function renderConfig(array $config): string
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

    public static function save(array $config): bool
    {
        $sanitized = self::sanitizeConfig($config);
        $content = self::renderConfig($sanitized);

        $path = self::configPath();
        $tempPath = $path . '.' . bin2hex(random_bytes(8));

        if (file_put_contents($tempPath, $content) === false) return false;
        if (!@rename($tempPath, $path)) { @unlink($tempPath); return false; }
        return true;
    }
}
