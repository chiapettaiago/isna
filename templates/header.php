<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Security-Policy" content="default-src * 'self' data: 'unsafe-inline' 'unsafe-eval'; script-src * 'self' https: 'unsafe-inline' 'unsafe-eval'; connect-src * 'self' https: blob:; img-src * 'self' data: blob:; frame-src * 'self' https:; style-src * 'self' 'unsafe-inline';">
  <link rel="shortcut icon" href="<?php echo $site_url; ?>/images/favicon.ico" type="image/x-icon">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'ISNA - Impacto Social'; ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="<?php echo $site_url; ?>/css/style.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="mainNav">
    <div class="container">
      <a class="navbar-brand position-relative" href="<?php echo $site_url; ?>/">
        <img src="<?php echo $site_url; ?>/images/logo.png" alt="Logo do Instituto" class="position-absolute top-50 start-0 translate-middle-y" style="height: 80px;">
      </a>


      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false" aria-label="Alternar navegação">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/">
              <i class="bi bi-house-fill"></i>Início
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/quem-somos') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/quem-somos">
              <i class="bi bi-people-fill"></i>Quem Somos
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/linha-atuacao') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/linha-atuacao">
              <i class="bi bi-bullseye"></i>Linha de Atuação
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/galeria') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/galeria">
              <i class="bi bi-images"></i>Projetos em Execução
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/parceiros') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/parceiros">
              <i class="bi bi-building"></i>Parceiros
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/transparencia') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/transparencia">
              <i class="bi bi-graph-up"></i>Transparência
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?php echo ($path === '/titulos-documentos') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/titulos-documentos">
              <i class="bi bi-file-earmark-check-fill"></i>Títulos e Documentos
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-semibold text-warning<?php echo ($path === '/doe' || $path === '/doacoes-bancarias') ? ' active' : ''; ?>" href="<?php echo $site_url; ?>/doe">
              <i class="bi bi-gift-fill me-1"></i> Doe
            </a>
          </li>

        </ul>
      </div>
    </div>
  </nav>s
