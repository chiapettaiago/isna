<?php

declare(strict_types=1);

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
            'id' => 'recital-emc-252',
            'title' => 'Recital EMC 25.2',
            'type' => 'directory',
            'background' => 'bg-light',
            'description' => '',
            'directory' => 'images/recital_emc_252',
            'caption_prefix' => 'Recital EMC 25.2',
        ],
        [
            'id' => 'recital-isna-252',
            'title' => 'Recital ISNA 25.2',
            'type' => 'directory',
            'background' => 'bg-white',
            'description' => '',
            'directory' => 'images/recital_isna_252',
            'caption_prefix' => 'Recital ISNA 25.2',
        ],
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
