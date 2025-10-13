<?php

declare(strict_types=1);

function gallery_config_path(): string
{
    return __DIR__ . '/config/gallery.php';
}

function gallery_default_config(): array
{
    $notasCulturais = [];
    for ($i = 1; $i <= 9; $i++) {
        $notasCulturais[] = [
            'type' => 'image',
            'src' => "/images/projeto-escola-musica-e-cidadania/{$i}.jpg",
            'alt' => "Notas Culturais {$i}",
            'caption' => "Notas Culturais {$i}",
        ];
    }

    $escolaMusica = [];
    for ($i = 1; $i <= 14; $i++) {
        $escolaMusica[] = [
            'type' => 'image',
            'src' => "/images/projeto-notas-culturais/{$i}.jpg",
            'alt' => "Escola Música e Cidadania {$i}",
            'caption' => "Escola Música e Cidadania {$i}",
        ];
    }

    return [
        'hero' => [
            'title' => 'Projetos em Execução',
            'background' => '/images/imagem.jpg',
            'height' => 600,
        ],
        'sections' => [
            [
                'id' => 'recital',
                'title' => 'Recital',
                'type' => 'directory',
                'background' => 'bg-white',
                'description' => '',
                'directory' => 'images/recital',
                'caption_prefix' => 'Recital',
            ],
            [
                'id' => 'projeto-notas-culturais',
                'title' => 'Projeto Notas Culturais',
                'type' => 'grid',
                'background' => '',
                'description' => '',
                'items' => $notasCulturais,
            ],
            [
                'id' => 'projeto-escola-musica-cidadania',
                'title' => 'Projeto de Escola Música e Cidadania',
                'type' => 'grid',
                'background' => 'bg-light',
                'description' => '',
                'items' => $escolaMusica,
            ],
        ],
    ];
}

function gallery_load(): array
{
    $path = gallery_config_path();

    if (is_readable($path)) {
        $data = include $path;

        if (is_array($data)) {
            return gallery_sanitize_config($data);
        }
    }

    return gallery_default_config();
}

function gallery_sanitize_config(array $config): array
{
    $defaults = gallery_default_config();

    $hero = $config['hero'] ?? [];

    $heroTitle = isset($hero['title']) && is_string($hero['title']) && $hero['title'] !== ''
        ? $hero['title']
        : $defaults['hero']['title'];

    $heroBackground = isset($hero['background']) && is_string($hero['background']) && $hero['background'] !== ''
        ? $hero['background']
        : $defaults['hero']['background'];

    $heroHeight = isset($hero['height']) && is_numeric($hero['height'])
        ? max(200, (int) $hero['height'])
        : $defaults['hero']['height'];

    $sections = [];

    if (isset($config['sections']) && is_array($config['sections'])) {
        foreach ($config['sections'] as $section) {
            if (!is_array($section)) {
                continue;
            }

            $id = isset($section['id']) && is_string($section['id']) ? trim($section['id']) : '';
            $title = isset($section['title']) && is_string($section['title']) ? trim($section['title']) : '';
            $type = isset($section['type']) && is_string($section['type']) ? strtolower(trim($section['type'])) : 'grid';
            $background = isset($section['background']) && is_string($section['background']) ? trim($section['background']) : '';
            $description = isset($section['description']) && is_string($section['description']) ? trim($section['description']) : '';

            if ($title === '') {
                continue;
            }

            if ($id === '') {
                $id = gallery_slug($title);
            }

            if (!preg_match('/^[a-z0-9\-]+$/', $id)) {
                $id = gallery_slug($id);
            }

            $sectionData = [
                'id' => $id,
                'title' => $title,
                'type' => in_array($type, ['grid', 'directory'], true) ? $type : 'grid',
                'background' => $background,
                'description' => $description,
            ];

            if ($sectionData['type'] === 'directory') {
                $directory = isset($section['directory']) && is_string($section['directory']) ? trim($section['directory']) : '';
                $captionPrefix = isset($section['caption_prefix']) && is_string($section['caption_prefix']) ? trim($section['caption_prefix']) : $title;

                if ($directory === '') {
                    continue;
                }

                $sectionData['directory'] = $directory;
                $sectionData['caption_prefix'] = $captionPrefix !== '' ? $captionPrefix : $title;
            } else {
                $items = [];

                if (isset($section['items']) && is_array($section['items'])) {
                    foreach ($section['items'] as $item) {
                        if (!is_array($item)) {
                            continue;
                        }

                        $src = isset($item['src']) && is_string($item['src']) ? trim($item['src']) : '';

                        if ($src === '') {
                            continue;
                        }

                        $items[] = [
                            'type' => 'image',
                            'src' => $src,
                            'alt' => isset($item['alt']) && is_string($item['alt']) && $item['alt'] !== '' ? $item['alt'] : $title,
                            'caption' => isset($item['caption']) && is_string($item['caption']) && $item['caption'] !== '' ? $item['caption'] : '',
                        ];
                    }
                }

                $sectionData['items'] = $items;
            }

            $sections[] = $sectionData;
        }
    }

    if (empty($sections)) {
        $sections = $defaults['sections'];
    }

    return [
        'hero' => [
            'title' => $heroTitle,
            'background' => $heroBackground,
            'height' => $heroHeight,
        ],
        'sections' => $sections,
    ];
}

function gallery_slug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text) ?? '';
    $text = preg_replace('/[\s-]+/', '-', $text) ?? '';
    $text = trim($text, '-');

    return $text !== '' ? $text : 'secao-' . bin2hex(random_bytes(2));
}

function gallery_render_config(array $config): string
{
    $lines = [
        '<?php',
        '',
        'declare(strict_types=1);',
        '',
        'return [',
        "    'hero' => [",
        "        'title' => '" . addslashes($config['hero']['title']) . "',",
        "        'background' => '" . addslashes($config['hero']['background']) . "',",
        "        'height' => " . (int) $config['hero']['height'] . ',',
        '    ],',
        "    'sections' => [",
    ];

    foreach ($config['sections'] as $section) {
        $lines[] = "        [";
        $lines[] = "            'id' => '" . addslashes($section['id']) . "',";
        $lines[] = "            'title' => '" . addslashes($section['title']) . "',";
        $lines[] = "            'type' => '" . addslashes($section['type']) . "',";
        $lines[] = "            'background' => '" . addslashes($section['background'] ?? '') . "',";
        $lines[] = "            'description' => '" . addslashes($section['description'] ?? '') . "',";

        if ($section['type'] === 'directory') {
            $lines[] = "            'directory' => '" . addslashes($section['directory']) . "',";
            $lines[] = "            'caption_prefix' => '" . addslashes($section['caption_prefix'] ?? $section['title']) . "',";
        } else {
            $lines[] = "            'items' => [";
            foreach ($section['items'] as $item) {
                $lines[] = "                [";
                $lines[] = "                    'type' => 'image',";
                $lines[] = "                    'src' => '" . addslashes($item['src']) . "',";
                $lines[] = "                    'alt' => '" . addslashes($item['alt']) . "',";
                $lines[] = "                    'caption' => '" . addslashes($item['caption'] ?? '') . "',";
                $lines[] = '                ],';
            }
            $lines[] = '            ],';
        }

        $lines[] = '        ],';
    }

    $lines[] = '    ],';
    $lines[] = '];';
    $lines[] = '';

    return implode("\n", $lines);
}

function gallery_save(array $config): bool
{
    $sanitized = gallery_sanitize_config($config);
    $content = gallery_render_config($sanitized);

    $path = gallery_config_path();
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
