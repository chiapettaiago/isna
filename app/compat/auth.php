<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AuthService.php';

function auth_start_session(): void { AuthService::startSession(); }
function auth_normalize_username(string $username): string { return AuthService::normalizeUsername($username); }
function auth_users(bool $refresh = false): array { return AuthService::users($refresh); }
function auth_users_path(): string { return AuthService::usersPath(); }
function auth_render_users_file(array $users): string { return AuthService::renderUsersFile($users); }
function auth_users_save(array $users): bool { return AuthService::usersSave($users); }
function auth_user_username(): ?string { return AuthService::userUsername(); }
function auth_user_roles(): array { return AuthService::userRoles(); }
function auth_has_role(string $role): bool { return AuthService::hasRole($role); }
function auth_user_is_admin(): bool { return AuthService::userIsAdmin(); }
function auth_attempt(string $username, string $password): bool { return AuthService::attempt($username, $password); }
function auth_check(): bool { return AuthService::check(); }
function auth_user(): ?array { return AuthService::user(); }
function auth_logout(): void { AuthService::logout(); }
function auth_flash_message(string $type, string $message): void { AuthService::flashMessage($type, $message); }
function auth_flash_messages(string $type): array { return AuthService::flashMessages($type); }
function auth_flash_value(string $key, $value): void { AuthService::flashValue($key, $value); }
function auth_flash_pull_value(string $key, $default = null) { return AuthService::flashPullValue($key, $default); }
function auth_remember_intended(string $uri): void { AuthService::rememberIntended($uri); }
function auth_take_intended(string $default): string { return AuthService::takeIntended($default); }
function auth_redirect(string $path = ''): void { AuthService::redirect($path); }
function auth_require(): void { AuthService::requireAuth(); }
function auth_generate_csrf_token(string $form = 'default'): string { return AuthService::generateCsrfToken($form); }
function auth_validate_csrf_token(string $form, ?string $token): bool { return AuthService::validateCsrfToken($form, $token); }
