<?php
$blogData = blog_load();
$latestPosts = array_slice($blogData['posts'], 0, 3);
?>

<!-- Seção Hero -->
  <section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 600px;  background-size: cover; /* ou 'contain' ou valores específicos */
  background-position: center; /* Centraliza a imagem no elemento */
  background-repeat: no-repeat; /* Evita a repetição da imagem */">
    <div class="container text-center">
      <h1 class="display-4">Bem-vindo ao Instituto Social Novo Amanhecer</h1>
      <p class="lead">Qualificando pessoas para inclusão no mercado de trabalho.</p>
      <a href="<?php echo $site_url; ?>/quem-somos" class="btn btn-warning btn-lg mt-3">Saiba Mais</a>
    </div>
  </section>

<?php if (!empty($latestPosts)): ?>
  <section id="blog" class="py-5 bg-white">
    <div class="container">
      <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
          <h2 class="mb-2">Últimas do Blog</h2>
          <p class="text-muted mb-0">Confira as novidades e histórias publicadas pela equipe ISNA.</p>
        </div>
        <?php if (auth_user_is_admin()): ?>
          <a class="btn btn-outline-secondary mt-3 mt-lg-0" href="<?php echo $site_url; ?>/gestao-blog">
            <i class="bi bi-pencil-square me-1"></i> Publicar novo conteúdo
          </a>
        <?php endif; ?>
      </div>

      <div class="row g-4">
        <?php foreach ($latestPosts as $post): ?>
          <?php
            $excerptSource = $post['summary'] !== '' ? $post['summary'] : $post['content'];
            if (function_exists('mb_substr')) {
                $excerpt = mb_substr($excerptSource, 0, 200);
                $needsEllipsis = mb_strlen($excerptSource) > mb_strlen($excerpt);
            } else {
                $excerpt = substr($excerptSource, 0, 200);
                $needsEllipsis = strlen($excerptSource) > strlen($excerpt);
            }
            if ($needsEllipsis) {
                $excerpt .= '...';
            }
            $collapseId = 'blog-collapse-' . $post['id'];
          ?>
          <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body d-flex flex-column">
                <p class="text-muted small mb-1"><?php echo date('d/m/Y', strtotime($post['published_at'])); ?></p>
                <h3 class="h5 fw-semibold"><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="text-muted mb-3">por <?php echo htmlspecialchars($post['author'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-3"><?php echo nl2br(htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8')); ?></p>
                <button class="btn btn-link p-0 align-self-start" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="false" aria-controls="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                  <i class="bi bi-journal-text me-1"></i> Ler mais
                </button>
                <div class="collapse mt-3" id="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                  <div class="card card-body border-0 bg-light">
                    <?php echo nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')); ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

  <!-- Seção Quem Somos -->
  <section id="quem-somos" class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Quem Somos</h2>
      <p class="lead text-center">
        O ISNA é uma organização da sociedade civil de interesse público, sem fins lucrativos, que busca, através de seus projetos sociais, qualificar e requalificar pessoas para a inclusão no mercado de trabalho.
      </p>
    </div>
  </section>

  <!-- Seção Linha de Atuação -->
  <section id="linha-atuacao" class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-4">Linha de Atuação</h2>
       <!-- Link Veja Mais -->
    <div class="text-center mb-4">
      <a href="<?php echo $site_url; ?>/linha-atuacao" class="btn btn-outline-secondary">
        Veja mais informações
      </a>
    </div>
      <p class="lead text-center">
        Nossos projetos sociais visam capacitar indivíduos, proporcionando-lhes as habilidades necessárias para ingressar e prosperar no mercado de trabalho.
      </p>
      <!-- Você pode incluir mais detalhes ou até mesmo cards para cada área de atuação -->
      <div class="row text-center mt-4">
        <div class="col-md-4">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <h5 class="card-title">Capacitação Profissional</h5>
              <p class="card-text">Cursos e treinamentos voltados para o desenvolvimento de habilidades práticas.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <h5 class="card-title">Apoio à Empregabilidade</h5>
              <p class="card-text">Orientação e programas de inclusão no mercado de trabalho.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <h5 class="card-title">Projetos Sociais</h5>
              <p class="card-text">Iniciativas que promovem a justiça social e o bem-estar comunitário.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Seção Galeria -->
<section id="galeria" class="py-5">
  <div class="container">
    <h2 class="text-center mb-4">Projetos em Execução</h2>
     <!-- Link Veja Mais -->
     <div class="text-center mb-4">
      <a href="<?php echo $site_url; ?>/galeria" class="btn btn-outline-secondary">
        Veja mais projetos
      </a>
    </div>
    <div class="row g-4">
      <!-- Imagem 1: Projeto de Escola Música e Cidadania -->
      <div class="col-md-4">
        <div class="ratio ratio-1x1">
          <img src="/images/projeto-escola-musica-e-cidadania/1.jpg"
               class="w-100 h-100 rounded shadow-sm galeria-home-img"
               alt="Projeto Escola Música e Cidadania"
               style="object-fit: cover; cursor: pointer;"
               data-bs-target="#modalGaleriaHome"
               data-index="0">
        </div>
      </div>

      <!-- Imagem 2: Projeto Notas Culturais -->
      <div class="col-md-4">
        <div class="ratio ratio-1x1">
          <img src="/images/projeto-notas-culturais/1.jpg"
               class="w-100 h-100 rounded shadow-sm galeria-home-img"
               alt="Projeto Notas Culturais"
               style="object-fit: cover; cursor: pointer;"
               data-bs-target="#modalGaleriaHome"
               data-index="1">
        </div>
      </div>

      <!-- Imagem 3: Projetos Realizados -->
      <div class="col-md-4">
        <div class="ratio ratio-1x1">
          <img src="/images/projetos-realizados/1.jpg"
               class="w-100 h-100 rounded shadow-sm galeria-home-img"
               alt="Projetos Realizados"
               style="object-fit: cover; cursor: pointer;"
               data-bs-target="#modalGaleriaHome"
               data-index="2">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Modal Galeria Home -->
<div class="modal fade" id="modalGaleriaHome" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-dark bg-opacity-75 border-0 rounded">
      <div class="modal-header border-0 p-2">
        <span class="text-white contador-imagens me-auto ms-2"></span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-0">
        <div id="carouselGaleriaHome" class="carousel slide carousel-fade" data-bs-ride="false" data-bs-touch="true">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="/images/projeto-escola-musica-e-cidadania/1.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Projeto Escola Música e Cidadania">
              <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                <h5>Projeto Escola Música e Cidadania</h5>
              </div>
            </div>
            <div class="carousel-item">
              <img src="/images/projeto-notas-culturais/1.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Projeto Notas Culturais">
              <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                <h5>Projeto Notas Culturais</h5>
              </div>
            </div>
            <div class="carousel-item">
              <img src="/images/projetos-realizados/1.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Projetos Realizados">
              <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                <h5>Projetos Realizados</h5>
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselGaleriaHome" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Anterior</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselGaleriaHome" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Próximo</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>