<?php
  $mesMural = '03';
  $anoMural = date('Y');
  $nomesMeses = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
    '04' => 'Abril',   '05' => 'Maio',      '06' => 'Junho',
    '07' => 'Julho',   '08' => 'Agosto',    '09' => 'Setembro',
    '10' => 'Outubro', '11' => 'Novembro',  '12' => 'Dezembro',
  ];
  $nomeMesMural = $nomesMeses[$mesMural] ?? 'Mês';

  // Lê dinamicamente as imagens da pasta /images/mural
  $muralDir  = $_SERVER['DOCUMENT_ROOT'] . '/images/mural/';
  $muralExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  $muralImgs = [];
  if (is_dir($muralDir)) {
    foreach (scandir($muralDir) as $f) {
      $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
      if (in_array($ext, $muralExts)) {
        $muralImgs[] = '/images/mural/' . $f;
      }
    }
    sort($muralImgs);
  }
?>

<!-- Hero -->
<section class="py-5 text-white d-flex align-items-center" style="background-color: #1a1a2e; min-height: 220px;">
  <div class="container text-center">
    <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
      <i class="bi bi-journal-richtext fs-1 text-warning"></i>
      <h1 class="display-5 mb-0">Mural Informativo</h1>
    </div>
    <p class="lead text-warning-emphasis mb-0"><?php echo $nomeMesMural . ' de ' . $anoMural; ?></p>
    <p class="text-white-50 mt-1">Avisos e novidades do Instituto Social Novo Amanhecer</p>
    <a href="<?php echo $site_url; ?>/" class="btn btn-outline-light btn-sm mt-2">
      <i class="bi bi-arrow-left me-1"></i> Voltar ao início
    </a>
  </div>
</section>

<!-- Conteúdo do Mural -->
<section class="py-5" style="background-color: #fff8e1;">
  <div class="container">

    <?php if (empty($muralImgs)): ?>
      <div class="text-center py-5">
        <i class="bi bi-journal-x fs-1 text-muted mb-3 d-block"></i>
        <h3 class="text-muted">Nenhum aviso publicado este mês</h3>
        <p class="text-muted">Volte em breve para conferir as novidades de <?php echo $nomeMesMural; ?>.</p>
        <a href="<?php echo $site_url; ?>/" class="btn btn-warning mt-2">
          <i class="bi bi-house-fill me-1"></i> Ir para o início
        </a>
      </div>
    <?php else: ?>

      <p class="text-muted mb-4">
        <i class="bi bi-info-circle me-1"></i>
        <?php echo count($muralImgs); ?> aviso<?php echo count($muralImgs) !== 1 ? 's' : ''; ?> publicado<?php echo count($muralImgs) !== 1 ? 's' : ''; ?> em <?php echo $nomeMesMural . ' de ' . $anoMural; ?>.
        Clique em qualquer imagem para ampliar.
      </p>

      <div class="row g-4 justify-content-center">
        <?php
          $total = count($muralImgs);
          if ($total === 1)      $colClass = 'col-sm-10 col-md-8 col-lg-6';
          elseif ($total === 2)  $colClass = 'col-sm-10 col-md-6';
          else                   $colClass = 'col-sm-10 col-md-6 col-lg-4';
        ?>
        <?php foreach ($muralImgs as $idx => $imgPath): ?>
          <div class="<?php echo $colClass; ?>">
            <div class="card h-100 border-0 shadow overflow-hidden" style="border-top: 4px solid #ffc107 !important;">
              <a href="#"
                 data-bs-toggle="modal"
                 data-bs-target="#modalMural"
                 data-mural-index="<?php echo $idx; ?>"
                 title="Clique para ampliar">
                <img
                  src="<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>"
                  alt="Aviso do mural de <?php echo $nomeMesMural . ' de ' . $anoMural; ?> — imagem <?php echo $idx + 1; ?>"
                  class="card-img-top w-100"
                  style="object-fit: contain; max-height: 520px; background: #f5f5f5;"
                  loading="lazy"
                >
              </a>
              <div class="card-footer bg-transparent border-0 text-center py-2">
                <small class="text-muted">
                  <i class="bi bi-zoom-in me-1"></i>
                  Aviso <?php echo $idx + 1; ?> de <?php echo $total; ?>
                </small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </div>
</section>

<!-- Modal para ampliar imagem -->
<?php if (!empty($muralImgs)): ?>
<div class="modal fade gallery-modal" id="modalMural" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-dark bg-opacity-90 border-0 rounded">
      <div class="modal-header border-0 p-2">
        <span class="text-white small me-auto ms-2" id="muralModalCounter"></span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-0">
        <div id="carouselMural" class="carousel slide carousel-fade" data-bs-ride="false" data-bs-touch="true">
          <div class="carousel-inner">
            <?php foreach ($muralImgs as $idx => $imgPath): ?>
              <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                <img
                  src="<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>"
                  class="d-block mx-auto img-fluid carousel-img"
                  alt="Aviso <?php echo $idx + 1; ?> — Mural <?php echo $nomeMesMural . ' de ' . $anoMural; ?>"
                  style="max-height: 80vh; object-fit: contain;"
                >
              </div>
            <?php endforeach; ?>
          </div>
          <?php if (count($muralImgs) > 1): ?>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselMural" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Anterior</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselMural" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Próximo</span>
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var total = <?php echo count($muralImgs); ?>;
  var modal = document.getElementById('modalMural');
  var carousel = document.getElementById('carouselMural');
  var counter = document.getElementById('muralModalCounter');

  function updateCounter(idx) {
    if (counter) counter.textContent = (idx + 1) + ' / ' + total;
  }

  // Abrir no slide correto ao clicar em uma imagem de card
  document.querySelectorAll('[data-mural-index]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      var idx = parseInt(this.getAttribute('data-mural-index'), 10);
      if (carousel && window.bootstrap) {
        var bsCarousel = bootstrap.Carousel.getOrCreateInstance(carousel, { ride: false });
        bsCarousel.to(idx);
      }
      updateCounter(idx);
    });
  });

  // Atualizar contador ao deslizar
  if (carousel) {
    carousel.addEventListener('slid.bs.carousel', function (e) {
      updateCounter(e.to);
    });
  }

  // Inicializar contador quando modal abrir
  if (modal) {
    modal.addEventListener('shown.bs.modal', function () {
      if (carousel) {
        var active = carousel.querySelector('.carousel-item.active');
        var items = carousel.querySelectorAll('.carousel-item');
        var idx = Array.from(items).indexOf(active);
        updateCounter(idx >= 0 ? idx : 0);
      }
    });
  }
})();
</script>
<?php endif; ?>
