<?php 
    require_once __DIR__ . '/../services/DocumentCrypt.php';

    $token = $_GET['token'] ?? '';

    $file = DocumentCrypt::decrypt($token);

    if (!$file) {
        http_response_code(404);
        exit('Documento inválido');
    }

    $path = __DIR__ . '/../../docs/' . $file;

    if (!file_exists($path)) {
        http_response_code(404);
        exit('Documento não encontrado');
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="documento.pdf"');

    readfile($path);

    exit;
?>