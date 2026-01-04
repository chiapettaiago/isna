<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Controller.php';

class UserController extends Controller
{
    public function handlePost(): void
    {
        if (!AuthService::check()) {
            AuthService::redirect('login');
        }

        $action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

        if ($action === 'update_password') {
            $token = $_POST['csrf_token'] ?? '';

            if (!AuthService::validateCsrfToken('update_password', $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('gestao-usuarios');
            }

            $currentPassword = isset($_POST['current_password']) ? (string) $_POST['current_password'] : '';
            $newPassword = isset($_POST['new_password']) ? (string) $_POST['new_password'] : '';
            $confirmPassword = isset($_POST['new_password_confirmation']) ? (string) $_POST['new_password_confirmation'] : '';

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                AuthService::flashMessage('error', 'Preencha todos os campos para alterar a senha.');
                AuthService::redirect('gestao-usuarios');
            }

            if ($newPassword !== $confirmPassword) {
                AuthService::flashMessage('error', 'A confirmação da nova senha não confere.');
                AuthService::redirect('gestao-usuarios');
            }

            if (strlen($newPassword) < 8) {
                AuthService::flashMessage('error', 'A nova senha deve conter pelo menos 8 caracteres.');
                AuthService::redirect('gestao-usuarios');
            }

            $username = AuthService::userUsername();

            if ($username === null) {
                AuthService::flashMessage('error', 'Não foi possível identificar o usuário autenticado.');
                AuthService::redirect('gestao-usuarios');
            }

            $users = AuthService::users();

            if (!isset($users[$username])) {
                AuthService::flashMessage('error', 'Registro de usuário não encontrado.');
                AuthService::redirect('gestao-usuarios');
            }

            $currentHash = $users[$username]['password'] ?? '';

            if (!is_string($currentHash) || $currentHash === '' || !password_verify($currentPassword, $currentHash)) {
                AuthService::flashMessage('error', 'Senha atual incorreta.');
                AuthService::redirect('gestao-usuarios');
            }

            $users[$username]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);

            if (!AuthService::usersSave($users)) {
                AuthService::flashMessage('error', 'Não foi possível atualizar a senha. Verifique as permissões do arquivo.');
                AuthService::redirect('gestao-usuarios');
            }

            AuthService::flashMessage('success', 'Senha atualizada com sucesso.');
            AuthService::redirect('gestao-usuarios');
        } elseif ($action === 'create_user') {
            if (!AuthService::userIsAdmin()) {
                AuthService::flashMessage('error', 'Você não tem permissão para criar usuários.');
                AuthService::redirect('gestao-usuarios');
            }

            $token = $_POST['csrf_token'] ?? '';

            if (!AuthService::validateCsrfToken('create_user', $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect('gestao-usuarios');
            }

            $usernameInput = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
            $displayName = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
            $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
            $passwordConfirmation = isset($_POST['password_confirmation']) ? (string) $_POST['password_confirmation'] : '';
            $isAdminRequested = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';

            $preserveFormState = static function () use ($usernameInput, $displayName, $isAdminRequested): void {
                AuthService::flashValue('create_user_username', $usernameInput);
                AuthService::flashValue('create_user_name', $displayName);
                AuthService::flashValue('create_user_is_admin', $isAdminRequested ? '1' : '0');
            };

            if ($usernameInput === '') {
                $preserveFormState();
                AuthService::flashMessage('error', 'Informe um nome de usuário.');
                AuthService::redirect('gestao-usuarios');
            }

            $usernameNormalized = AuthService::normalizeUsername($usernameInput);

            if ($usernameNormalized === '') {
                $preserveFormState();
                AuthService::flashMessage('error', 'O nome de usuário informado é inválido.');
                AuthService::redirect('gestao-usuarios');
            }

            if (!preg_match('/^[a-z0-9._-]{4,}$/', $usernameNormalized)) {
                $preserveFormState();
                AuthService::flashMessage('error', 'Use pelo menos 4 caracteres (letras minúsculas, números, ponto, hífen ou sublinhado).');
                AuthService::redirect('gestao-usuarios');
            }

            if ($password === '' || $passwordConfirmation === '') {
                $preserveFormState();
                AuthService::flashMessage('error', 'Defina uma senha e confirme-a.');
                AuthService::redirect('gestao-usuarios');
            }

            if ($password !== $passwordConfirmation) {
                $preserveFormState();
                AuthService::flashMessage('error', 'A confirmação da senha não confere.');
                AuthService::redirect('gestao-usuarios');
            }

            if (strlen($password) < 8) {
                $preserveFormState();
                AuthService::flashMessage('error', 'A senha deve conter pelo menos 8 caracteres.');
                AuthService::redirect('gestao-usuarios');
            }

            // If DB remote is configured, try to create user there first
            $dbCfg = AuthService::dbConfig();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            if ($dbCfg !== null) {
                // check if user already exists in DB
                $dbUser = AuthService::userFromDb($usernameNormalized);
                if (is_array($dbUser)) {
                    $preserveFormState();
                    AuthService::flashMessage('error', 'Já existe um usuário com esse login (banco de dados).');
                    AuthService::redirect('gestao-usuarios');
                }

                $created = AuthService::createUserInDb($usernameNormalized, $passwordHash, $displayName !== '' ? $displayName : $usernameInput, $isAdminRequested ? ['admin'] : []);
                if ($created) {
                    AuthService::flashMessage('success', 'Usuário criado com sucesso no banco remoto.');
                    AuthService::redirect('gestao-usuarios');
                }
                // If DB create failed, fall back to file-based users
                AuthService::flashMessage('warning', 'Não foi possível criar o usuário no banco remoto; tentando fallback para arquivo local.');
            }

            $users = AuthService::users();

            if (isset($users[$usernameNormalized])) {
                $preserveFormState();
                AuthService::flashMessage('error', 'Já existe um usuário com esse login.');
                AuthService::redirect('gestao-usuarios');
            }

            $users[$usernameNormalized] = [
                'name' => $displayName !== '' ? $displayName : $usernameInput,
                'password' => $passwordHash,
                'roles' => $isAdminRequested ? ['admin'] : [],
            ];

            if (!AuthService::usersSave($users)) {
                $preserveFormState();
                AuthService::flashMessage('error', 'Não foi possível criar o usuário. Verifique as permissões do arquivo.');
                AuthService::redirect('gestao-usuarios');
            }

            AuthService::flashMessage('success', 'Usuário criado com sucesso.');
            AuthService::redirect('gestao-usuarios');
        }
    }
}
