<?php
// Define o cabeçalho para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Função para verificar se há um processo git pull em andamento
function isGitPullRunning() {
    // Verifica arquivo de lock que pode ser criado durante o processo de pull
    $lockFile = __DIR__ . '/.git-pull-in-progress';
    
    if (file_exists($lockFile)) {
        $lockData = json_decode(file_get_contents($lockFile), true);
        
        // Se o arquivo de lock existe e não está expirado
        if ($lockData && isset($lockData['expires']) && time() < $lockData['expires']) {
            return [
                'updating' => true,
                'progress' => isset($lockData['progress']) ? $lockData['progress'] : 50,
                'message' => isset($lockData['message']) ? $lockData['message'] : 'Atualizando...',
                'time_remaining' => $lockData['expires'] - time()
            ];
        } else {
            // Lock expirado, remover arquivo
            @unlink($lockFile);
        }
    }
    
    // Verifica processos de sistema
    $cmd = "ps aux | grep -E 'git pull|git fetch|git merge' | grep -v grep";
    exec($cmd, $output, $return_value);
    
    if (!empty($output) && $return_value === 0) {
        return [
            'updating' => true,
            'progress' => 50, // Progresso genérico
            'message' => 'Executando git pull...',
            'time_remaining' => 30 // Tempo estimado: 30 segundos
        ];
    }
    
    return [
        'updating' => false,
        'progress' => 100,
        'message' => 'Sistema atualizado',
        'time_remaining' => 0
    ];
}

// Retorna o resultado
echo json_encode(isGitPullRunning());
