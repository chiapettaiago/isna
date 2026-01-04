<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Controller.php';

class AuthController extends Controller
{
    public function login(): void
    {
        $username = isset($_POST['username']) ? (string) $_POST['username'] : '';
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!AuthService::validateCsrfToken('login', $csrfToken)) {
            AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
            AuthService::flashValue('old_username', $username);
            AuthService::redirect('login');
        }

        if (AuthService::attempt($username, $password)) {
            $redirectTo = AuthService::takeIntended(url('area-restrita'));
            AuthService::flashMessage('success', 'Login realizado com sucesso.');
            header('Location: ' . $redirectTo);
            exit;
        }

        AuthService::flashMessage('error', 'Usuário ou senha inválidos.');
        AuthService::flashValue('old_username', $username);
        AuthService::redirect('login');
    }

    public function logout(): void
    {
        AuthService::logout();
        AuthService::flashMessage('success', 'Sessão encerrada com sucesso.');
        AuthService::redirect('login');
    }
}
