<?php
// Script de teste: inicia sessão com usuário autenticado e inclui index.php
chdir(__DIR__ . '/../');
session_start();
// Ajuste do usuário de teste — use um usuário existente com permissões
$_SESSION['auth_user'] = [
    'username' => 'iago_chiapetta',
    'name' => 'Iago Chiapetta',
    'roles' => ['admin'],
];

// Simula requisição
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/access-stats';
$_GET = [];

require 'index.php';
