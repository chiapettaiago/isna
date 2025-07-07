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
  <nav class="navbar sticky-top  navbar-expand-lg navbar-dark bg-dark" id="mainNav">
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
            <a class="nav-link" href="<?php echo $site_url; ?>/">Início</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $site_url; ?>/quem-somos">Quem Somos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $site_url; ?>/linha-atuacao">Linha de Atuação</a>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $site_url; ?>/galeria">Projetos em Execução</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $site_url; ?>/parceiros">Parceiros</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo $site_url; ?>/transparencia">Transparência</a></li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $site_url; ?>/titulos-documentos">Títulos e Documentos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-semibold text-warning" href="<?php echo $site_url; ?>/doe">
              <i class="bi bi-heart-fill me-1"></i> Doe
            </a>
          </li>

        </ul>
      </div>
    </div>
  </nav>
