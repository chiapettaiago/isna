<?php
$users = AuthService::users();
ksort($users);

$currentUsername = AuthService::userUsername();
$isAdmin = AuthService::userIsAdmin();

$updatePasswordToken = AuthService::generateCsrfToken('update_password');
$createUserToken = $isAdmin ? AuthService::generateCsrfToken('create_user') : '';
$updateUserToken = $isAdmin ? AuthService::generateCsrfToken('update_user') : '';
$deleteUserToken = $isAdmin ? AuthService::generateCsrfToken('delete_user') : '';

$createOldUsername = AuthService::flashPullValue('create_user_username', '');
$createOldName = AuthService::flashPullValue('create_user_name', '');
$createOldIsAdmin = AuthService::flashPullValue('create_user_is_admin', '0') === '1';

$totalUsers = count($users);
$adminUsers = 0;
foreach ($users as $userData) {
  $roles = isset($userData['roles']) && is_array($userData['roles']) ? $userData['roles'] : [];
  if (in_array('admin', $roles, true)) {
    $adminUsers++;
  }
}

$formatRoles = static function (array $roles): string {
  if (empty($roles)) {
    return 'Padrão';
  }

  return implode(', ', array_map(static function ($role) {
    $role = (string) $role;
    return function_exists('mb_strtoupper') ? mb_strtoupper($role) : strtoupper($role);
  }, $roles));
};
?>

<section class="user-admin-page py-5">
  <div class="container">
    <div class="user-admin-hero mb-4">
      <div>
        <span class="user-admin-eyebrow">Administração</span>
        <h1 class="display-6 fw-semibold mb-2">Gestão de usuários</h1>
        <p class="lead mb-0">Crie acessos, revise permissões e mantenha as contas internas organizadas.</p>
      </div>
      <div class="user-admin-stats" aria-label="Resumo de usuários">
        <div>
          <strong><?php echo $totalUsers; ?></strong>
          <span><?php echo $totalUsers === 1 ? 'usuário ativo' : 'usuários ativos'; ?></span>
        </div>
        <div>
          <strong><?php echo $adminUsers; ?></strong>
          <span><?php echo $adminUsers === 1 ? 'administrador' : 'administradores'; ?></span>
        </div>
      </div>
    </div>

    <div class="user-admin-actions-panel mb-4">
      <div>
        <h2 class="h5 fw-semibold mb-1">Ações rápidas</h2>
        <p class="text-muted mb-0">Abra os formulários somente quando precisar alterar credenciais ou cadastrar alguém.</p>
      </div>
      <div class="user-admin-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#passwordResetModal">
          <i class="bi bi-key-fill me-1"></i> Redefinir minha senha
        </button>
        <?php if ($isAdmin): ?>
          <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-person-plus-fill me-1"></i> Criar novo usuário
          </button>
        <?php else: ?>
          <span class="user-admin-secure-badge"><i class="bi bi-lock-fill"></i> Criação restrita a administradores</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content user-admin-modal">
          <form method="post" action="<?php echo $site_url; ?>/gestao-usuarios" autocomplete="off">
            <div class="modal-header">
              <div>
                <div class="user-admin-panel-icon">
                  <i class="bi bi-shield-lock"></i>
                </div>
                <h2 class="modal-title h5" id="passwordResetModalLabel">Redefinir minha senha</h2>
                <p class="text-muted mb-0">Informe a senha atual para confirmar a troca.</p>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="action" value="update_password">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($updatePasswordToken, ENT_QUOTES, 'UTF-8'); ?>">

              <div class="mb-3">
                <label class="form-label" for="current_password">Senha atual</label>
                <input class="form-control form-control-lg" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
              </div>

              <div class="mb-3">
                <label class="form-label" for="new_password">Nova senha</label>
                <input class="form-control form-control-lg" type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="8" required>
                <div class="form-text">Use pelo menos 8 caracteres.</div>
              </div>

              <div>
                <label class="form-label" for="new_password_confirmation">Confirme a nova senha</label>
                <input class="form-control form-control-lg" type="password" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password" minlength="8" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button class="btn btn-primary" type="submit">Atualizar senha</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php if ($isAdmin): ?>
      <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content user-admin-modal">
            <form method="post" action="<?php echo $site_url; ?>/gestao-usuarios" autocomplete="off">
              <div class="modal-header">
                <div>
                  <div class="user-admin-panel-icon">
                    <i class="bi bi-person-plus"></i>
                  </div>
                  <h2 class="modal-title h5" id="createUserModalLabel">Criar novo usuário</h2>
                  <p class="text-muted mb-0">Defina credenciais iniciais e o nível de acesso do colaborador.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="action" value="create_user">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($createUserToken, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label" for="new_username">Usuário</label>
                    <input class="form-control form-control-lg" type="text" id="new_username" name="username" value="<?php echo htmlspecialchars($createOldUsername, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off" pattern="[a-z0-9._-]{4,}" required>
                    <div class="form-text">Mínimo de 4 caracteres: letras minúsculas, números, ponto, hífen ou sublinhado.</div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="new_name">Nome completo</label>
                    <input class="form-control form-control-lg" type="text" id="new_name" name="name" value="<?php echo htmlspecialchars($createOldName, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="name">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="new_user_password">Senha temporária</label>
                    <input class="form-control form-control-lg" type="password" id="new_user_password" name="password" autocomplete="new-password" minlength="8" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label" for="new_user_password_confirmation">Confirme a senha</label>
                    <input class="form-control form-control-lg" type="password" id="new_user_password_confirmation" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                  </div>
                </div>

                <div class="user-admin-role-switch mt-4">
                  <div>
                    <strong>Acesso administrativo</strong>
                    <span>Permite editar conteúdo, usuários, blog e galeria.</span>
                  </div>
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="new_user_is_admin" name="is_admin" value="1" <?php echo $createOldIsAdmin ? 'checked' : ''; ?>>
                    <label class="visually-hidden" for="new_user_is_admin">Conceder acesso administrativo</label>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" type="submit">Adicionar usuário</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
      <div class="user-admin-panel mt-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
          <div>
            <h2 class="h5 fw-semibold mb-1">Usuários cadastrados</h2>
            <p class="text-muted mb-0">Edite permissões ou remova contas que não precisam mais de acesso.</p>
          </div>
        </div>

        <div class="table-responsive user-admin-table-wrap">
          <table class="table align-middle mb-0 user-admin-table">
            <thead>
              <tr>
                <th scope="col">Usuário</th>
                <th scope="col">Nome</th>
                <th scope="col">Perfis</th>
                <th scope="col" class="text-center">Situação</th>
                <th scope="col" class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $username => $data): ?>
                <?php
                  $roles = isset($data['roles']) && is_array($data['roles']) ? $data['roles'] : [];
                  $rolesLabel = $formatRoles($roles);
                  $userIsAdmin = in_array('admin', $roles, true);
                  $isCurrent = $currentUsername !== null && $username === $currentUsername;
                  $modalSuffix = substr(hash('sha256', (string)$username), 0, 12);
                  $editModalId = 'edit-user-' . $modalSuffix;
                  $deleteModalId = 'delete-user-' . $modalSuffix;
                  $displayName = (string)($data['name'] ?? $username);
                  $avatarInitial = function_exists('mb_substr') ? mb_substr($displayName, 0, 1) : substr($displayName, 0, 1);
                ?>
                <tr>
                  <td>
                    <div class="user-admin-identity">
                      <span><?php echo htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8'); ?></span>
                      <code><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></code>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></td>
                  <td>
                    <span class="badge rounded-pill <?php echo $userIsAdmin ? 'text-bg-primary' : 'text-bg-light'; ?>">
                      <?php echo htmlspecialchars($rolesLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <?php if ($isCurrent): ?>
                      <span class="badge rounded-pill text-bg-info">Você</span>
                    <?php else: ?>
                      <span class="badge rounded-pill text-bg-success">Ativo</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <div class="btn-group" role="group" aria-label="Ações do usuário <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                      <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="bi bi-pencil-square"></i>
                        <span class="visually-hidden">Editar</span>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#<?php echo htmlspecialchars($deleteModalId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isCurrent ? 'disabled' : ''; ?>>
                        <i class="bi bi-trash3"></i>
                        <span class="visually-hidden">Remover</span>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php foreach ($users as $username => $data): ?>
        <?php
          $roles = isset($data['roles']) && is_array($data['roles']) ? $data['roles'] : [];
          $userIsAdmin = in_array('admin', $roles, true);
          $isCurrent = $currentUsername !== null && $username === $currentUsername;
          $modalSuffix = substr(hash('sha256', (string)$username), 0, 12);
          $editModalId = 'edit-user-' . $modalSuffix;
          $deleteModalId = 'delete-user-' . $modalSuffix;
        ?>
        <div class="modal fade" id="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content user-admin-modal">
              <form method="post" action="<?php echo $site_url; ?>/gestao-usuarios" autocomplete="off">
                <div class="modal-header">
                  <div>
                    <h2 class="modal-title h5">Editar usuário</h2>
                    <p class="text-muted mb-0"><code><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></code></p>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="action" value="update_user">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($updateUserToken, ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">

                  <div class="mb-3">
                    <label class="form-label" for="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>-name">Nome completo</label>
                    <input class="form-control" type="text" id="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>-name" name="name" value="<?php echo htmlspecialchars($data['name'] ?? $username, ENT_QUOTES, 'UTF-8'); ?>">
                  </div>

                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label" for="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>-password">Nova senha</label>
                      <input class="form-control" type="password" id="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>-password" name="password" autocomplete="new-password" minlength="8">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label" for="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>-password-confirmation">Confirmar senha</label>
                      <input class="form-control" type="password" id="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>-password-confirmation" name="password_confirmation" autocomplete="new-password" minlength="8">
                    </div>
                  </div>
                  <div class="form-text mb-3">Preencha a senha apenas se quiser redefini-la.</div>

                  <div class="user-admin-role-switch">
                    <div>
                      <strong>Acesso administrativo</strong>
                      <span><?php echo $isCurrent ? 'Seu próprio acesso administrativo fica preservado.' : 'Habilita permissões completas de gestão.'; ?></span>
                    </div>
                    <div class="form-check form-switch m-0">
                      <input class="form-check-input" type="checkbox" id="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>-admin" name="is_admin" value="1" <?php echo $userIsAdmin ? 'checked' : ''; ?> <?php echo $isCurrent ? 'disabled' : ''; ?>>
                      <label class="visually-hidden" for="<?php echo htmlspecialchars($editModalId, ENT_QUOTES, 'UTF-8'); ?>-admin">Acesso administrativo</label>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Salvar alterações</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <?php if (!$isCurrent): ?>
          <div class="modal fade" id="<?php echo htmlspecialchars($deleteModalId, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content user-admin-modal">
                <form method="post" action="<?php echo $site_url; ?>/gestao-usuarios">
                  <div class="modal-header">
                    <div>
                      <h2 class="modal-title h5">Remover usuário</h2>
                      <p class="text-muted mb-0"><code><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></code></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($deleteUserToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                    <p class="mb-0">Confirme a remoção de <?php echo htmlspecialchars($data['name'] ?? $username, ENT_QUOTES, 'UTF-8'); ?>. Esta ação não pode ser desfeita.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Remover usuário</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
