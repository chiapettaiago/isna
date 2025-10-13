<?php
$csrfToken = auth_generate_csrf_token('login');
$oldUsername = auth_flash_pull_value('old_username', '');
?>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-5 col-md-7">
        <?php if (!empty($currentUser)): ?>
          <div class="card shadow-sm">
            <div class="card-body text-center">
              <h1 class="h4 mb-3">Você já está autenticado</h1>
              <p class="mb-4">
                Acesse a página abaixo para continuar a gestão do conteúdo restrito.
              </p>
              <a class="btn btn-primary w-100 mb-2" href="<?php echo $site_url; ?>/area-restrita">
                <i class="bi bi-person-badge-fill me-1"></i> Ir para a Área Restrita
              </a>
              <a class="btn btn-outline-secondary w-100" href="<?php echo $site_url; ?>/logout">
                <i class="bi bi-box-arrow-right me-1"></i> Encerrar sessão
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="card shadow-sm">
            <div class="card-body">
              <h1 class="h4 text-center mb-4">Acesso Restrito</h1>
              <form method="post" action="<?php echo $site_url; ?>/login" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                  <label class="form-label" for="username">Usuário</label>
                  <input
                    class="form-control"
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($oldUsername, ENT_QUOTES, 'UTF-8'); ?>"
                    autocomplete="username"
                    required
                    autofocus
                  >
                </div>
                <div class="mb-4">
                  <label class="form-label" for="password">Senha</label>
                  <input
                    class="form-control"
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                  >
                </div>
                <button class="btn btn-primary w-100" type="submit">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                </button>
              </form>
              <p class="small text-muted mt-3 mb-0">
                Dica: Altere a senha padrão após o primeiro login acessando a área restrita.
              </p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
