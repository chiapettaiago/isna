<style>
    /* Limita a imagem ampliada para não ultrapassar o viewport no desktop */
    .modal-body img {
      max-height: 90vh;   /* até 90% da altura da janela */
      max-width: 100%;    /* não ultrapassa a largura */
      width: auto;        /* mantém proporção */
      display: block;
      margin: 0 auto;     /* centraliza */
    }
</style>

  <!-- Hero -->
  <section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 600px; background-size: cover; background-position: center;">
    <div class="container text-center">
      <h1 class="display-4">Projetos em Execução</h1>
    </div>
  </section>

  <!-- Seção: Recital -->
  <section class="py-5 bg-white">
    <div class="container">
      <h2 class="text-center mb-4">Recital</h2>
      <div class="row g-4">
        <?php
          $recitalDir = __DIR__ . '/../images/recital';
          $recitalUrlBase = '/images/recital';
          // Extensões exibíveis e prioridade (preferir WebP quando existir)
          $allowedExt = ['jpg','jpeg','png','gif','webp','JPG','JPEG','PNG','GIF','WEBP'];
          $priority = ['webp','jpg','jpeg','png','gif'];
          $byBase = [];
          $recitalFiles = [];
          if (is_dir($recitalDir)) {
            foreach (scandir($recitalDir) as $fname) {
              if ($fname === '.' || $fname === '..') continue;
              $ext = pathinfo($fname, PATHINFO_EXTENSION);
              if (!in_array($ext, $allowedExt, true)) continue;
              $base = pathinfo($fname, PATHINFO_FILENAME);
              $lowerExt = strtolower($ext);
              $byBase[$base][$lowerExt] = $fname;
            }
          }
          // Escolhe um arquivo por nome-base com base na prioridade
          foreach ($byBase as $base => $extMap) {
            foreach ($priority as $p) {
              if (isset($extMap[$p])) { $recitalFiles[] = $extMap[$p]; break; }
            }
          }
          natcasesort($recitalFiles);
          $recitalFiles = array_values($recitalFiles);
          if (count($recitalFiles) === 0):
        ?>
          <p class="text-center text-muted">Nenhuma foto disponível no momento.</p>
        <?php else: ?>
          <?php foreach ($recitalFiles as $i => $fname): ?>
            <div class="col-sm-6 col-md-4 col-lg-4">
              <div class="ratio ratio-1x1">
                <img src="<?php echo $recitalUrlBase . '/' . htmlspecialchars($fname); ?>"
                     class="w-100 h-100 rounded shadow-sm galeria-img"
                     alt="Recital <?php echo $i + 1; ?>"
                     style="object-fit: cover; cursor: pointer;"
                     data-bs-target="#imageModalRecital"
                     data-index="<?php echo $i; ?>"
                     loading="lazy">
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Seção 1: Projeto Notas Culturais -->
  <section class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Projeto Notas Culturais</h2>
      <div class="row g-4">
        <!-- 9 fotos quadradas -->
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/1.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 1"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="0"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/2.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 2"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="1"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/3.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 3"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="2"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/4.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 4"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="3"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/5.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 5"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="4"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/6.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 6"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="5"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/7.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 7"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="6"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/8.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 8"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="7"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-escola-musica-e-cidadania/9.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Notas Culturais 9"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal1"
                 data-index="8"
                 loading="lazy">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Seção 2: Projeto de Escola Música e Cidadania -->
  <section class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-5">Projeto de Escola Música e Cidadania</h2>
      <div class="row g-4">
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/1.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 1"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="0"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/2.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 2"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="1"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/3.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 3"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="2"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/4.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 4"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="3"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/5.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 5"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="4"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/6.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 6"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="5"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/7.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 7"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="6"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/8.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 8"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="7"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/9.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 9"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="8"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/10.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 10"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="9"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/11.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 11"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="10"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/12.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 12"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="11"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/13.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 13"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="12"
                 loading="lazy">
          </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-4">
          <div class="ratio ratio-1x1">
            <img src="/images/projeto-notas-culturais/14.jpg"
                 class="w-100 h-100 rounded shadow-sm galeria-img"
                 alt="Escola Música e Cidadania 14"
                 style="object-fit: cover; cursor: pointer;"
                 data-bs-target="#imageModal2"
                 data-index="13"
                 loading="lazy">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Modal & Carousel Seção 1 -->
  <!-- Modal & Carousel Recital -->
  <div class="modal fade" id="imageModalRecital" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-dark bg-opacity-75 border-0 rounded">
        <div class="modal-header border-0 p-2">
          <span class="text-white contador-imagens me-auto ms-2">1/1</span>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-0">
          <div id="carouselRecital" class="carousel slide carousel-fade" data-bs-ride="false" data-bs-touch="true">
            <div class="carousel-inner">
              <?php if (!empty($recitalFiles)): ?>
                <?php foreach ($recitalFiles as $i => $fname): ?>
                  <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo $recitalUrlBase . '/' . htmlspecialchars($fname); ?>" class="d-block mx-auto img-fluid carousel-img" alt="Recital <?php echo $i + 1; ?>">
                    <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                      <h5>Recital <?php echo $i + 1; ?></h5>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselRecital" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span><span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselRecital" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span><span class="visually-hidden">Próximo</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="imageModal1" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-dark bg-opacity-75 border-0 rounded">
        <div class="modal-header border-0 p-2">
          <span class="text-white contador-imagens me-auto ms-2">1/9</span>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-0">
          <div id="carousel1" class="carousel slide carousel-fade" data-bs-ride="false" data-bs-touch="true">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="/images/projeto-escola-musica-e-cidadania/1.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 1">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 1</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-escola-musica-e-cidadania/2.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 2">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 2</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-escola-musica-e-cidadania/3.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 3">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 3</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-escola-musica-e-cidadania/4.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 4">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 4</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-escola-musica-e-cidadania/5.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 5">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 5</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-escola-musica-e-cidadania/6.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 6">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 6</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-escola-musica-e-cidadania/7.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 7">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 7</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-escola-musica-e-cidadania/8.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 8">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 8</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-escola-musica-e-cidadania/9.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Notas Culturais 9">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Notas Culturais 9</h5>
                </div>
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel1" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span><span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel1" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span><span class="visually-hidden">Próximo</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal & Carousel Seção 2 -->
  <div class="modal fade" id="imageModal2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-dark bg-opacity-75 border-0 rounded">
        <div class="modal-header border-0 p-2">
          <span class="text-white contador-imagens me-auto ms-2">1/14</span>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-0">
          <div id="carousel2" class="carousel slide carousel-fade" data-bs-ride="false" data-bs-touch="true">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="/images/projeto-notas-culturais/1.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 1">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 1</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/2.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 2">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 2</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/3.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 3">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 3</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/4.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 4">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 4</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/5.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 5">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 5</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/6.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 6">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 6</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/7.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 7">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 7</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/8.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 8">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 8</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/9.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 9">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 9</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/10.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 10">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 10</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/11.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 11">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 11</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/12.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 12">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 12</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/13.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 13">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 13</h5>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projeto-notas-culturais/14.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Escola Música e Cidadania 14">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Escola Música e Cidadania 14</h5>
                </div>
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel2" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span><span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel2" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span><span class="visually-hidden">Próximo</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
  // Código de inicialização garantido
  document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os carrosséis explicitamente
    if (typeof bootstrap !== 'undefined') {
  const carouselRecital = document.getElementById('carouselRecital');
  const carousel1 = document.getElementById('carousel1');
  const carousel2 = document.getElementById('carousel2');
      
      let lastScrollPosition = 0;
      let lastHash = '';
      let lastModal = null;

  if (carouselRecital) new bootstrap.Carousel(carouselRecital, { interval: false, ride: false, touch: true });
  if (carousel1) new bootstrap.Carousel(carousel1, { interval: false, ride: false, touch: true });
  if (carousel2) new bootstrap.Carousel(carousel2, { interval: false, ride: false, touch: true });

      // Configuração para clicar nas imagens
      document.querySelectorAll('.galeria-img').forEach(function(img) {
        img.addEventListener('click', function() {
          lastScrollPosition = window.scrollY;
          lastHash = window.location.hash;
          const target = img.getAttribute('data-bs-target');
          const index = parseInt(img.getAttribute('data-index'), 10);
          const modalEl = document.querySelector(target);
          lastModal = modalEl;

          if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            let carouselId = 'carouselRecital';
            if (target === '#imageModal1') carouselId = 'carousel1';
            if (target === '#imageModal2') carouselId = 'carousel2';
            const carouselEl = document.getElementById(carouselId);
            if (carouselEl) {
              const carouselInstance = bootstrap.Carousel.getInstance(carouselEl) || 
                                       new bootstrap.Carousel(carouselEl);
              setTimeout(function() {
                carouselInstance.to(index);
              }, 150);
            }
          }
        });
      });

      // Ao fechar o modal, retorna para o scroll anterior e remove backdrop manualmente se necessário
      document.querySelectorAll('.modal').forEach(function(modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
          // Corrige scroll/hash
          if (lastScrollPosition !== null) {
            window.scrollTo({ top: lastScrollPosition, behavior: 'instant' });
            if (lastHash) window.location.hash = lastHash;
          }
          // Remove backdrop caso não seja removido automaticamente
          document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
            backdrop.parentNode.removeChild(backdrop);
          });
          document.body.classList.remove('modal-open');
          document.body.style.overflow = '';
        });
      });

      // Atualizar contadores
      document.querySelectorAll('.carousel').forEach(function(carousel) {
        carousel.addEventListener('slid.bs.carousel', function() {
          const totalItems = carousel.querySelectorAll('.carousel-item').length;
          const activeIndex = Array.from(carousel.querySelectorAll('.carousel-item'))
            .findIndex(item => item.classList.contains('active'));
          const contador = carousel.closest('.modal-content').querySelector('.contador-imagens');
          if (contador) {
            contador.textContent = `${activeIndex + 1}/${totalItems}`;
          }
        });
        // Inicializa o contador
        const totalItems = carousel.querySelectorAll('.carousel-item').length;
        const contador = carousel.closest('.modal-content').querySelector('.contador-imagens');
        if (contador) {
          contador.textContent = `1/${totalItems}`;
        }
      });
    }
  });
</script>
