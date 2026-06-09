<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Controller.php';

class AuthController extends Controller
{
    public function login(): void
    {
        $step = isset($_POST['login_step']) ? (string) $_POST['login_step'] : 'credentials';
        $username = isset($_POST['username']) ? (string) $_POST['username'] : '';
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        $captchaAnswer = isset($_POST['captcha_answer']) ? (string) $_POST['captcha_answer'] : '';

        if ($step === 'captcha') {
            if (!AuthService::validateCsrfToken('login_captcha', $csrfToken)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('login');
            }

            if (!AuthService::hasPendingLogin()) {
                AuthService::flashMessage('error', 'Informe usuário e senha antes do desafio de segurança.');
                AuthService::redirect('login');
            }

            if (!AuthService::validateCaptchaAnswer('login', $captchaAnswer)) {
                AuthService::flashMessage('error', 'Confirme o desafio de segurança para continuar.');
                AuthService::redirect('login');
            }

            if (!AuthService::completePendingLogin()) {
                AuthService::flashMessage('error', 'Não foi possível concluir o login. Tente novamente.');
                AuthService::redirect('login');
            }

            $redirectTo = AuthService::takeIntended(url('area-restrita'));
            AuthService::flashMessage('success', 'Login realizado com sucesso.');
            header('Location: ' . $redirectTo);
            exit;
        }

        if (!AuthService::validateCsrfToken('login', $csrfToken)) {
            AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
            AuthService::flashValue('old_username', $username);
            AuthService::redirect('login');
        }

        if (AuthService::beginPendingLogin($username, $password)) {
            AuthService::redirect('login');
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
