<?php
// Define o cabeçalho para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Retorna sempre que não há atualização em andamento.
// Removida a lógica de lock/processos para evitar que o site seja bloqueado por avisos.
echo json_encode([
    'updating' => false,
    'progress' => 100,
    'message' => 'Checagem de atualização removida',
    'time_remaining' => 0
]);
