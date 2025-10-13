<?php
$users = auth_users();
ksort($users);

$currentUsername = auth_user_username();
$isAdmin = auth_user_is_admin();
$currentProfile = $currentUsername !== null && isset($users[$currentUsername]) ? $users[$currentUsername] : null;

$updatePasswordToken = auth_generate_csrf_token('update_password');
$createUserToken = $isAdmin ? auth_generate_csrf_token('create_user') : '';

$createOldUsername = auth_flash_pull_value('create_user_username', '');
$createOldName = auth_flash_pull_value('create_user_name', '');
$createOldIsAdmin = auth_flash_pull_value('create_user_is_admin', '0') === '1';

$totalUsers = count($users);
?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="mb-4">
      <h1 class="display-6 fw-semibold mb-2">Gestão de Usuários</h1>
      <p class="lead mb-0">
        Gerencie seu acesso com segurança e, se necessário, conceda permissões a novos colaboradores.
      </p>
    </div>

    <div class="row g-4">
      <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 fw-semibold">Redefinir minha senha</h2>
            <p class="text-muted">Atualize sua senha atual informando a senha antiga para confirmação.</p>
            <form method="post" action="<?php echo $site_url; ?>/gestao-usuarios" autocomplete="off" class="mt-3">
              <input type="hidden" name="action" value="update_password">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($updatePasswordToken, ENT_QUOTES, 'UTF-8'); ?>">

              <div class="mb-3">
                <label class="form-label" for="current_password">Senha atual</label>
                <input
                  class="form-control"
                  type="password"
                  id="current_password"
                  name="current_password"
                  autocomplete="current-password"
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label" for="new_password">Nova senha</label>
                <input
                  class="form-control"
                  type="password"
                  id="new_password"
                  name="new_password"
                  autocomplete="new-password"
                  minlength="8"
                  required
                >
                <div class="form-text">Use pelo menos 8 caracteres misturando letras, números e símbolos.</div>
              </div>

              <div class="mb-4">
                <label class="form-label" for="new_password_confirmation">Confirme a nova senha</label>
                <input
                  class="form-control"
                  type="password"
                  id="new_password_confirmation"
                  name="new_password_confirmation"
                  autocomplete="new-password"
                  minlength="8"
                  required
                >
              </div>

              <button class="btn btn-primary" type="submit">
                <i class="bi bi-key-fill me-1"></i> Atualizar senha
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <?php if ($isAdmin): ?>
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h2 class="h5 fw-semibold">Criar novo usuário</h2>
              <p class="text-muted">Defina as credenciais iniciais e compartilhe a senha com o novo membro de forma segura.</p>
              <form method="post" action="<?php echo $site_url; ?>/gestao-usuarios" autocomplete="off" class="mt-3">
                <input type="hidden" name="action" value="create_user">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($createUserToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="mb-3">
                  <label class="form-label" for="new_username">Usuário</label>
                  <input
                    class="form-control"
                    type="text"
                    id="new_username"
                    name="username"
                    value="<?php echo htmlspecialchars($createOldUsername, ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="off"
                    pattern="[a-z0-9._-]{4,}"
                    required
                  >
                  <div class="form-text">Somente letras minúsculas, números, ponto, hífen ou sublinhado. Mínimo de 4 caracteres.</div>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="new_name">Nome completo</label>
                  <input
                    class="form-control"
                    type="text"
                    id="new_name"
                    name="name"
                    value="<?php echo htmlspecialchars($createOldName, ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="name"
                  >
                </div>

                <div class="mb-3">
                  <label class="form-label" for="new_user_password">Senha temporária</label>
                  <input
                    class="form-control"
                    type="password"
                    id="new_user_password"
                    name="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                  >
                </div>

                <div class="mb-3">
                  <label class="form-label" for="new_user_password_confirmation">Confirme a senha</label>
                  <input
                    class="form-control"
                    type="password"
                    id="new_user_password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    minlength="8"
                    required
                  >
                </div>

                <div class="form-check form-switch mb-4">
                  <input class="form-check-input" type="checkbox" id="new_user_is_admin" name="is_admin" value="1" <?php echo $createOldIsAdmin ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="new_user_is_admin">Conceder acesso administrativo</label>
                </div>

                <button class="btn btn-success" type="submit">
                  <i class="bi bi-person-plus-fill me-1"></i> Adicionar usuário
                </button>
              </form>
            </div>
          </div>
        <?php else: ?>
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h2 class="h5 fw-semibold">Solicitar novos acessos</h2>
              <p class="text-muted mb-0">
                Apenas administradores podem cadastrar novos logins. Fale com a equipe responsável para conceder acesso a outros colaboradores.
              </p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($isAdmin): ?>
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
            <div>
              <h2 class="h5 fw-semibold mb-1">Usuários cadastrados</h2>
              <p class="text-muted mb-0"><?php echo $totalUsers; ?> <?php echo $totalUsers === 1 ? 'usuário ativo' : 'usuários ativos'; ?>.</p>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th scope="col">Usuário</th>
                  <th scope="col">Nome</th>
                  <th scope="col">Perfis</th>
                  <th scope="col" class="text-center">Situação</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $username => $data): ?>
                  <?php
                    $roles = isset($data['roles']) && is_array($data['roles']) ? $data['roles'] : [];
                    $rolesLabel = empty($roles)
                      ? 'Padrão'
                      : implode(', ', array_map(static function ($role) {
                          $role = (string) $role;
                          return function_exists('mb_strtoupper') ? mb_strtoupper($role) : strtoupper($role);
                        }, $roles));
                    $isCurrent = $currentUsername !== null && $username === $currentUsername;
                  ?>
                  <tr>
                    <td><code><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></code></td>
                    <td><?php echo htmlspecialchars($data['name'] ?? $username, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($rolesLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="text-center">
                      <?php if ($isCurrent): ?>
                        <span class="badge bg-primary">Você</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Ativo</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
