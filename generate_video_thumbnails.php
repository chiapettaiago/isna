#!/usr/bin/env php
<?php
/**
 * Script para gerar thumbnails automáticas dos vídeos da API
 * 
 * Uso: php generate_video_thumbnails.php
 */

// Configuração dos vídeos
$videos = [
    // Vídeo de Doações
    [
        'url' => 'https://api.chiapetta.dev/v/4N4uRF1EkEjnpxgx',
        'name' => 'ISNA - Doações',
        'thumbnail' => __DIR__ . '/images/donation-thumbnail.jpg',
    ],
    // Outubro Rosa - Horizontal
    [
        'url' => 'https://api.chiapetta.dev/v/ItV-Nx6UsanFr8DH',
        'name' => 'outubro_rosa-horizontal',
        'thumbnail' => __DIR__ . '/videos/outubro_rosa_poster.jpg',
    ],
    // Outubro Rosa - Vertical
    [
        'url' => 'https://api.chiapetta.dev/v/KfyfXHINHWwqZ_Bk',
        'name' => 'outubro_rosa-vertical',
        'thumbnail' => __DIR__ . '/videos/outubro_rosa_poster_vertical.jpg',
    ],
    // Realização 1 - Horizontal
    [
        'url' => 'https://api.chiapetta.dev/v/boKogI2kIyY6fieR',
        'name' => 'realizacao-1-horizontal',
        'thumbnail' => __DIR__ . '/images/realizacoes/realizacao-1-horizontal.jpg',
    ],
    // Realização 1 - Vertical
    [
        'url' => 'https://api.chiapetta.dev/v/DU_q-YUklTb57i2Y',
        'name' => 'realizacao-1-vertical',
        'thumbnail' => __DIR__ . '/images/realizacoes/realizacao-1-vertical.jpg',
    ],
];

/**
 * Gera thumbnail de um vídeo usando FFmpeg
 * 
 * @param string $videoUrl URL do vídeo
 * @param string $outputPath Caminho onde salvar a thumbnail
 * @param int $timeInSeconds Tempo em segundos do frame a capturar (padrão: 2)
 * @return bool
 */
function generateThumbnail($videoUrl, $outputPath, $timeInSeconds = 2) {
    // Cria o diretório se não existir
    $dir = dirname($outputPath);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            echo "❌ Erro ao criar diretório: $dir\n";
            return false;
        }
    }
    
    // Arquivo temporário para o vídeo
    $tempVideo = sys_get_temp_dir() . '/' . uniqid('video_') . '.mp4';
    
    echo "📥 Baixando vídeo de $videoUrl...\n";
    
    // Baixa o vídeo
    $videoContent = @file_get_contents($videoUrl);
    if ($videoContent === false) {
        echo "❌ Erro ao baixar vídeo\n";
        return false;
    }
    
    file_put_contents($tempVideo, $videoContent);
    echo "✓ Vídeo baixado (" . formatBytes(filesize($tempVideo)) . ")\n";
    
    // Comando FFmpeg para extrair frame
    // -ss: posição do tempo
    // -i: arquivo de entrada
    // -vframes 1: captura apenas 1 frame
    // -q:v 2: qualidade (2 = alta qualidade)
    // -vf scale: redimensiona mantendo proporção
    $cmd = sprintf(
        'ffmpeg -ss %d -i %s -vframes 1 -q:v 2 -vf "scale=1280:-1" %s 2>&1',
        $timeInSeconds,
        escapeshellarg($tempVideo),
        escapeshellarg($outputPath)
    );
    
    echo "🎬 Gerando thumbnail...\n";
    exec($cmd, $output, $returnVar);
    
    // Remove arquivo temporário
    @unlink($tempVideo);
    
    if ($returnVar === 0 && file_exists($outputPath)) {
        echo "✓ Thumbnail gerada: $outputPath (" . formatBytes(filesize($outputPath)) . ")\n";
        return true;
    } else {
        echo "❌ Erro ao gerar thumbnail\n";
        if (!empty($output)) {
            echo "Detalhes: " . implode("\n", array_slice($output, -5)) . "\n";
        }
        return false;
    }
}

/**
 * Formata bytes em formato legível
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Verificar se FFmpeg está disponível
exec('which ffmpeg', $ffmpegPath, $returnVar);
if ($returnVar !== 0) {
    echo "❌ FFmpeg não encontrado. Instale com: sudo apt-get install ffmpeg\n";
    exit(1);
}

echo "🎥 Gerador de Thumbnails de Vídeos\n";
echo str_repeat("=", 50) . "\n\n";

$success = 0;
$failed = 0;

foreach ($videos as $video) {
    echo "📹 Processando: {$video['name']}\n";
    echo str_repeat("-", 50) . "\n";
    
    // Verificar se thumbnail já existe
    if (file_exists($video['thumbnail'])) {
        echo "⚠️  Thumbnail já existe. Sobrescrever? (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($line) !== 'y') {
            echo "⏭️  Pulando...\n\n";
            continue;
        }
    }
    
    if (generateThumbnail($video['url'], $video['thumbnail'], 2)) {
        $success++;
    } else {
        $failed++;
    }
    
    echo "\n";
}

echo str_repeat("=", 50) . "\n";
echo "✅ Sucesso: $success\n";
echo "❌ Falhas: $failed\n";
echo "\n";

if ($success > 0) {
    echo "💡 Dica: Você pode ajustar o tempo do frame editando o terceiro parâmetro\n";
    echo "   da função generateThumbnail() (padrão: 2 segundos)\n";
}
