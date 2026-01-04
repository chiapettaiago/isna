<?php
$csrfToken = AuthService::generateCsrfToken('login');
$oldUsername = AuthService::flashPullValue('old_username', '');
?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card shadow-sm overflow-hidden">
          <div class="row g-0">
            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center bg-primary text-white p-4">
              <div class="text-center">
                <img src="<?php echo $site_url; ?>/images/logo.png" alt="Logo" style="max-height:90px;" class="mb-3">
                <h2 class="h5 fw-semibold">Acesso Restrito</h2>
                <p class="small mb-0">Área administrativa do site. Faça login para gerenciar conteúdo e configurações.</p>
              </div>
            </div>
            <div class="col-md-6 p-4">
              <?php if (!empty($currentUser)): ?>
                <div class="text-center">
                  <h3 class="h6">Você já está autenticado</h3>
                  <p class="small text-muted">Use os botões abaixo para continuar.</p>
                  <a class="btn btn-primary w-100 mb-2" href="<?php echo $site_url; ?>/area-restrita">
                    <i class="bi bi-person-badge-fill me-1"></i> Área Restrita
                  </a>
                  <a class="btn btn-outline-secondary w-100" href="<?php echo $site_url; ?>/logout">
                    <i class="bi bi-box-arrow-right me-1"></i> Sair
                  </a>
                </div>
              <?php else: ?>
                <h3 class="h6 text-center mb-3">Entrar na área administrativa</h3>
                <form method="post" action="<?php echo $site_url; ?>/login" novalidate>
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                  <div class="mb-3">
                    <label class="form-label" for="username">Usuário</label>
                    <input
                      class="form-control form-control-lg"
                      type="text"
                      id="username"
                      name="username"
                      value="<?php echo htmlspecialchars($oldUsername, ENT_QUOTES, 'UTF-8'); ?>"
                      autocomplete="username"
                      required
                      autofocus
                    >
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="password">Senha</label>
                    <input
                      class="form-control form-control-lg"
                      type="password"
                      id="password"
                      name="password"
                      autocomplete="current-password"
                      required
                    >
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="<?php echo $site_url; ?>/" class="small">Voltar ao site</a>
                    <a href="<?php echo $site_url; ?>/" class="small text-muted">Esqueci minha senha</a>
                  </div>

                  <button class="btn btn-primary w-100 btn-lg" type="submit">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                  </button>
                </form>
                <p class="small text-muted text-center mt-3 mb-0">Dica: altere a senha padrão após o primeiro login.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
