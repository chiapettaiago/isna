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

        if ($action === 'update_password' || $action === 'update_sidebar_password') {
            $token = $_POST['csrf_token'] ?? '';
            $csrfForm = $action === 'update_sidebar_password' ? 'update_sidebar_password' : 'update_password';
            $redirectTo = $action === 'update_sidebar_password'
                ? $this->safeReturnPath(isset($_POST['return_to']) ? (string)$_POST['return_to'] : 'area-restrita')
                : 'gestao-usuarios';

            if (!AuthService::validateCsrfToken($csrfForm, $token)) {
                AuthService::flashMessage('error', 'Token de segurança inválido. Por favor, tente novamente.');
                AuthService::redirect($redirectTo);
            }

            $currentPassword = isset($_POST['current_password']) ? (string) $_POST['current_password'] : '';
            $newPassword = isset($_POST['new_password']) ? (string) $_POST['new_password'] : '';
            $confirmPassword = isset($_POST['new_password_confirmation']) ? (string) $_POST['new_password_confirmation'] : '';

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                AuthService::flashMessage('error', 'Preencha todos os campos para alterar a senha.');
                AuthService::redirect($redirectTo);
            }

            if ($newPassword !== $confirmPassword) {
                AuthService::flashMessage('error', 'A confirmação da nova senha não confere.');
                AuthService::redirect($redirectTo);
            }

            if (strlen($newPassword) < 8) {
                AuthService::flashMessage('error', 'A nova senha deve conter pelo menos 8 caracteres.');
                AuthService::redirect($redirectTo);
            }

            $username = AuthService::userUsername();

            if ($username === null) {
                AuthService::flashMessage('error', 'Não foi possível identificar o usuário autenticado.');
                AuthService::redirect($redirectTo);
            }

            $user = AuthService::userFromDb($username);

            if (!is_array($user)) {
                AuthService::flashMessage('error', 'Registro de usuário não encontrado.');
                AuthService::redirect($redirectTo);
            }

            $currentHash = $user['password'] ?? '';

            if (!is_string($currentHash) || $currentHash === '' || !password_verify($currentPassword, $currentHash)) {
                AuthService::flashMessage('error', 'Senha atual incorreta.');
                AuthService::redirect($redirectTo);
            }

            if (!AuthService::updateUserInDb($username, ['password' => password_hash($newPassword, PASSWORD_DEFAULT)])) {
                AuthService::flashMessage('error', 'Não foi possível atualizar a senha no banco MySQL.');
                AuthService::redirect($redirectTo);
            }

            AuthService::flashMessage('success', 'Senha atualizada com sucesso.');
            AuthService::redirect($redirectTo);
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

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            if (AuthService::userFromDb($usernameNormalized)) {
                $preserveFormState();
                AuthService::flashMessage('error', 'Já existe um usuário com esse login.');
                AuthService::redirect('gestao-usuarios');
            }

            if (!AuthService::createUserInDb($usernameNormalized, $passwordHash, $displayName !== '' ? $displayName : $usernameInput, $isAdminRequested ? ['admin'] : [])) {
                $preserveFormState();
                AuthService::flashMessage('error', 'Não foi possível criar o usuário no banco MySQL.');
                AuthService::redirect('gestao-usuarios');
            }

            AuthService::flashMessage('success', 'Usuário criado com sucesso no banco MySQL.');
            AuthService::redirect('gestao-usuarios');
        }
    }

    private function safeReturnPath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || filter_var($path, FILTER_VALIDATE_URL)) {
            return 'area-restrita';
        }

        $pathOnly = parse_url($path, PHP_URL_PATH);
        $query = parse_url($path, PHP_URL_QUERY);

        if (!is_string($pathOnly) || $pathOnly === '' || $pathOnly[0] !== '/') {
            return 'area-restrita';
        }

        $returnPath = $pathOnly;
        if (is_string($query) && $query !== '') {
            $returnPath .= '?' . $query;
        }

        return $returnPath;
    }
}
