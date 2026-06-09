<?php
$csrfToken = AuthService::generateCsrfToken('login');
$pendingLogin = AuthService::hasPendingLogin();
$pendingLoginName = AuthService::pendingLoginName();
$captchaToken = $pendingLogin ? AuthService::generateCsrfToken('login_captcha') : '';
$captchaChallenge = $pendingLogin ? AuthService::generateCaptchaChallenge('login') : null;
$oldUsername = AuthService::flashPullValue('old_username', '');
?>

<main class="wp-login-page" aria-labelledby="loginTitle">
  <div class="wp-login-wrap">
    <a class="wp-login-logo" href="<?php echo $site_url; ?>/" aria-label="Voltar ao site ISNA">
      <img src="<?php echo $site_url . cms_attr('global', 'brand.favicon', '/images/favicon.ico'); ?>" alt="">
      <span>ISNA</span>
    </a>

    <?php if (!empty($currentUser)): ?>
      <section class="wp-login-card">
        <h1 id="loginTitle" class="wp-login-title">Sessão ativa</h1>
        <p class="wp-login-text">Você já está autenticado na área administrativa.</p>
        <a class="btn btn-primary w-100 mb-2" href="<?php echo $site_url; ?>/area-restrita">
          <i class="bi bi-speedometer2 me-1"></i> Acessar painel
        </a>
        <a class="btn btn-outline-secondary w-100" href="<?php echo $site_url; ?>/logout">
          <i class="bi bi-box-arrow-right me-1"></i> Sair
        </a>
      </section>
    <?php else: ?>
      <form class="wp-login-card" method="post" action="<?php echo $site_url; ?>/login" novalidate>
        <h1 id="loginTitle" class="wp-login-title">Entrar</h1>
        <input type="hidden" name="login_step" value="credentials">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="mb-3">
          <label class="form-label" for="username">Nome de usuário</label>
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

        <div class="mb-3">
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

        <div class="d-flex justify-content-end align-items-center mt-4">
          <button class="btn btn-primary" type="submit">
            Entrar
          </button>
        </div>
      </form>
    <?php endif; ?>

    <nav class="wp-login-links" aria-label="Links de acesso">
      <a href="<?php echo $site_url; ?>/">Voltar para ISNA</a>
    </nav>
  </div>
</main>

<?php if ($pendingLogin && $captchaChallenge !== null): ?>
<div class="modal fade wp-login-captcha-modal" id="loginCaptchaModal" tabindex="-1" aria-labelledby="loginCaptchaModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?php echo $site_url; ?>/login" novalidate>
        <div class="modal-header">
          <div>
            <h2 class="modal-title h5" id="loginCaptchaModalLabel">Confirme que você não é um robô</h2>
            <?php if ($pendingLoginName): ?>
              <div class="small text-muted">Login confirmado para <?php echo htmlspecialchars($pendingLoginName, ENT_QUOTES, 'UTF-8'); ?>.</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-body">
          <input type="hidden" name="login_step" value="captcha">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($captchaToken, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="wp-login-captcha">
            <label class="form-label" for="captcha_answer">
              Desafio de segurança: quanto é <?php echo htmlspecialchars($captchaChallenge['question'], ENT_QUOTES, 'UTF-8'); ?>?
            </label>
            <input
              class="form-control"
              type="text"
              inputmode="numeric"
              pattern="[0-9]*"
              id="captcha_answer"
              name="captcha_answer"
              autocomplete="off"
              required
              autofocus
            >
          </div>
        </div>
        <div class="modal-footer">
          <a class="btn btn-outline-secondary" href="<?php echo $site_url; ?>/logout">Trocar usuário</a>
          <button class="btn btn-primary" type="submit">Confirmar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('loginCaptchaModal');
    if (!modalEl || !window.bootstrap) return;
    var modal = new window.bootstrap.Modal(modalEl);
    modal.show();
    modalEl.addEventListener('shown.bs.modal', function () {
      var input = document.getElementById('captcha_answer');
      if (input) input.focus();
    });
  });
</script>
<?php endif; ?>
