<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Security-Policy" content="default-src * 'self' data: 'unsafe-inline' 'unsafe-eval'; script-src * 'self' https: 'unsafe-inline' 'unsafe-eval'; connect-src * 'self' https: blob:; img-src * 'self' data: blob:; frame-src * 'self' https:; style-src * 'self' 'unsafe-inline';">
  <link rel="shortcut icon" href="<?php echo $site_url . cms_attr('global', 'brand.favicon', '/images/favicon.ico'); ?>" type="image/x-icon">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'ISNA - Impacto Social'; ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Plyr CSS (Player de vídeo com UI moderna) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS Personalizado -->
  <?php
    $cssPath = __DIR__ . '/../css/style.css';
    $cssVer  = file_exists($cssPath) ? filemtime($cssPath) : '1';
  ?>
  <link rel="stylesheet" href="<?php echo $site_url; ?>/css/style.css?v=<?php echo $cssVer; ?>">
</head>
<body class="<?php echo $currentUser ? 'admin-layout' : ''; ?>">
  <?php if ($currentUser): ?>
  <?php
    $sidebarUsername = isset($currentUser['username']) ? (string)$currentUser['username'] : '';
    $sidebarName = isset($currentUser['name']) && (string)$currentUser['name'] !== '' ? (string)$currentUser['name'] : $sidebarUsername;
    $sidebarRoles = isset($currentUser['roles']) && is_array($currentUser['roles']) ? $currentUser['roles'] : [];
    $sidebarRolesLabel = empty($sidebarRoles)
      ? 'Padrão'
      : implode(', ', array_map(static function ($role) {
          $role = (string)$role;
          return function_exists('mb_strtoupper') ? mb_strtoupper($role) : strtoupper($role);
        }, $sidebarRoles));
    $sidebarPasswordToken = AuthService::generateCsrfToken('update_sidebar_password');
    $sidebarReturnTo = $path;
    $sidebarQuery = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    if ($sidebarQuery) {
      $sidebarReturnTo .= '?' . $sidebarQuery;
    }
  ?>
  <aside class="admin-sidebar" aria-label="Navegação administrativa">
    <a class="admin-sidebar-brand" href="<?php echo $site_url; ?>/area-restrita" title="Área restrita">
      <img src="<?php echo $site_url . cms_attr('global', 'brand.logo', '/images/logo.png'); ?>" alt="Logo do Instituto">
      <span class="admin-sidebar-text">ISNAPress</span>
    </a>

    <button class="admin-sidebar-user" type="button" title="<?php echo htmlspecialchars($sidebarName, ENT_QUOTES, 'UTF-8'); ?>" data-bs-toggle="modal" data-bs-target="#adminUserModal">
      <i class="bi bi-person-circle" aria-hidden="true"></i>
      <span class="admin-sidebar-text"><?php echo htmlspecialchars($sidebarName, ENT_QUOTES, 'UTF-8'); ?></span>
    </button>

    <nav class="admin-sidebar-nav">
      <a class="admin-sidebar-link<?php echo ($path === '/area-restrita') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/area-restrita" title="Dashboard">
        <i class="bi bi-speedometer2" aria-hidden="true"></i>
        <span class="admin-sidebar-text">Dashboard</span>
      </a>
      <a class="admin-sidebar-link<?php echo ($path === '/relatorios-acesso') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/relatorios-acesso" title="Relatórios">
        <i class="bi bi-graph-up" aria-hidden="true"></i>
        <span class="admin-sidebar-text">Relatórios</span>
      </a>
      <a class="admin-sidebar-link<?php echo ($path === '/gestao-usuarios') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/gestao-usuarios" title="Usuários">
        <i class="bi bi-person-gear" aria-hidden="true"></i>
        <span class="admin-sidebar-text">Usuários</span>
      </a>
      <a class="admin-sidebar-link<?php echo ($path === '/sobre') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/sobre" title="Sobre o sistema">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span class="admin-sidebar-text">Sobre</span>
      </a>
      <a class="admin-sidebar-link<?php echo ($path === '/gestao-galeria') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/gestao-galeria" title="Gerenciar galeria">
        <i class="bi bi-images" aria-hidden="true"></i>
        <span class="admin-sidebar-text">Gerenciar galeria</span>
      </a>
      <a class="admin-sidebar-link<?php echo ($path === '/gestao-blog') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/gestao-blog" title="Gerenciar blog">
        <i class="bi bi-journal-text" aria-hidden="true"></i>
        <span class="admin-sidebar-text">Gerenciar blog</span>
      </a>
      <a class="admin-sidebar-link<?php echo ($path === '/gestao-cms') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/gestao-cms" title="CMS do site">
        <i class="bi bi-layout-text-window-reverse" aria-hidden="true"></i>
        <span class="admin-sidebar-text">CMS do site</span>
      </a>
    </nav>

    <a class="admin-sidebar-link admin-sidebar-logout" href="<?php echo $site_url; ?>/logout" title="Sair">
      <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
      <span class="admin-sidebar-text">Sair</span>
    </a>
  </aside>

  <div class="modal fade" id="adminUserModal" tabindex="-1" aria-labelledby="adminUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h2 class="modal-title h5" id="adminUserModalLabel">Minha conta</h2>
            <div class="small text-muted">Informações do usuário autenticado</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <dl class="row mb-4">
            <dt class="col-sm-4">Nome</dt>
            <dd class="col-sm-8"><?php echo htmlspecialchars($sidebarName, ENT_QUOTES, 'UTF-8'); ?></dd>
            <dt class="col-sm-4">Usuário</dt>
            <dd class="col-sm-8"><code><?php echo htmlspecialchars($sidebarUsername, ENT_QUOTES, 'UTF-8'); ?></code></dd>
            <dt class="col-sm-4">Perfil</dt>
            <dd class="col-sm-8"><?php echo htmlspecialchars($sidebarRolesLabel, ENT_QUOTES, 'UTF-8'); ?></dd>
          </dl>

          <form method="post" action="<?php echo $site_url; ?>/gestao-usuarios" autocomplete="off">
            <input type="hidden" name="action" value="update_sidebar_password">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($sidebarPasswordToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($sidebarReturnTo, ENT_QUOTES, 'UTF-8'); ?>">

            <h3 class="h6 fw-semibold mb-3">Alterar senha</h3>

            <div class="mb-3">
              <label class="form-label" for="sidebar_current_password">Senha atual</label>
              <input class="form-control" type="password" id="sidebar_current_password" name="current_password" autocomplete="current-password" required>
            </div>

            <div class="mb-3">
              <label class="form-label" for="sidebar_new_password">Nova senha</label>
              <input class="form-control" type="password" id="sidebar_new_password" name="new_password" autocomplete="new-password" minlength="8" required>
              <div class="form-text">Use pelo menos 8 caracteres.</div>
            </div>

            <div class="mb-4">
              <label class="form-label" for="sidebar_new_password_confirmation">Confirme a nova senha</label>
              <input class="form-control" type="password" id="sidebar_new_password_confirmation" name="new_password_confirmation" autocomplete="new-password" minlength="8" required>
            </div>

            <button class="btn btn-primary w-100" type="submit">
              <i class="bi bi-key-fill me-1"></i> Atualizar senha
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php else: ?>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="mainNav">
    <div class="container">
      <a class="navbar-brand position-relative" href="<?php echo $site_url; ?>/">
        <img src="<?php echo $site_url . cms_attr('global', 'brand.logo', '/images/logo.png'); ?>" alt="Logo do Instituto" class="position-absolute top-50 start-0 translate-middle-y" style="height: 80px;">
      </a>


      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false" aria-label="Alternar navegação">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/quem-somos') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/quem-somos" data-label="Sobre">
              <i class="bi bi-people-fill"></i> Quem Somos
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/linha-atuacao') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/linha-atuacao" data-label="Atuação">
              <i class="bi bi-bullseye"></i> Linha de Atuação
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/galeria') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/galeria" data-label="Galeria">
              <i class="bi bi-images"></i> Projetos em Execução
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/parceiros') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/parceiros" data-label="Parceiros">
              <i class="bi bi-building"></i> Parceiros
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/transparencia') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/transparencia" data-label="Balanço">
              <i class="bi bi-graph-up"></i> Transparência
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/titulos-documentos') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/titulos-documentos" data-label="Títulos">
              <i class="bi bi-file-earmark-check-fill"></i> Títulos e Documentos
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/contato') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/contato" data-label="Contato">
              <i class="bi bi-chat-dots-fill"></i> Contato
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-semibold text-warning<?php echo ($path === '/doe' || $path === '/doacoes-bancarias') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/doe" data-label="Doe">
              <i class="bi bi-gift-fill me-1"></i> Doe
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/login') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/login" data-label="Entrar">
              <i class="bi bi-box-arrow-in-right"></i> <span class="nav-text-entrar">Entrar</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <?php endif; ?>

  <?php
    $flashBanners = [];
    $flashTypes = [
        'success' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
    ];

    foreach ($flashTypes as $bucket => $cssClass) {
      $messages = AuthService::flashMessages($bucket);

      if (!empty($messages)) {
        $flashBanners[] = ['class' => $cssClass, 'messages' => $messages];
      }
    }
  ?>

  <?php if (!empty($flashBanners)): ?>
    <div class="container mt-3">
      <?php foreach ($flashBanners as $flash): ?>
        <?php foreach ($flash['messages'] as $message): ?>
          <div class="alert alert-<?php echo $flash['class']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
