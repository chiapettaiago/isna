<?php
  $nomesMeses = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
    '04' => 'Abril',   '05' => 'Maio',      '06' => 'Junho',
    '07' => 'Julho',   '08' => 'Agosto',    '09' => 'Setembro',
    '10' => 'Outubro', '11' => 'Novembro',  '12' => 'Dezembro',
  ];

  // Imagens gerenciadas pelo CMS em "Mural Informativo"
  $muralImgs = [];
  $muralMonthGroups = [];
  $muralImageCountValue = cms_value('mural', 'images.count', '2');
  $muralImageCount = is_numeric($muralImageCountValue) ? (int)$muralImageCountValue : 2;
  $muralImageCount = max(0, min(6, $muralImageCount));
  $muralImageDefaults = [
    1 => '/images/mural/anuncio1.jpeg',
    2 => '/images/mural/anuncio2.jpeg',
  ];

  for ($muralImageIndex = 1; $muralImageIndex <= $muralImageCount; $muralImageIndex++) {
    $imgPath = cms_value(
      'mural',
      'images.item' . $muralImageIndex,
      $muralImageDefaults[$muralImageIndex] ?? '/images/imagem.jpg'
    );

    if ($imgPath !== '') {
      $dateValue = cms_value('mural', 'images.item' . $muralImageIndex . '.date', date('Y-m-d'));
      $timestamp = strtotime($dateValue);
      if ($timestamp === false) {
        $timestamp = time();
        $dateValue = date('Y-m-d', $timestamp);
      }

      $muralImgs[] = [
        'path' => $imgPath,
        'date' => $dateValue,
        'timestamp' => $timestamp,
        'original_index' => $muralImageIndex,
      ];
    }
  }

  usort($muralImgs, static function (array $a, array $b): int {
    return ($b['timestamp'] <=> $a['timestamp']) ?: ($a['original_index'] <=> $b['original_index']);
  });

  foreach ($muralImgs as $idx => $muralItem) {
    $monthKey = date('Y-m', (int)$muralItem['timestamp']);
    $monthNumber = date('m', (int)$muralItem['timestamp']);
    $monthLabel = ($nomesMeses[$monthNumber] ?? 'Mês') . ' de ' . date('Y', (int)$muralItem['timestamp']);
    if (!isset($muralMonthGroups[$monthKey])) {
      $muralMonthGroups[$monthKey] = [
        'label' => $monthLabel,
        'items' => [],
      ];
    }
    $muralItem['modal_index'] = $idx;
    $muralMonthGroups[$monthKey]['items'][] = $muralItem;
  }
?>

<!-- Hero -->
<section class="py-5 text-white d-flex align-items-center" style="background-color: #1a1a2e; min-height: 220px;">
  <div class="container text-center">
    <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
      <i class="bi bi-journal-richtext fs-1 text-warning"></i>
      <h1 class="display-5 mb-0"><?php echo cms_text('mural', 'hero.title', 'Mural Informativo'); ?></h1>
    </div>
    <p class="lead text-warning-emphasis mb-0">Avisos organizados por mês</p>
    <p class="text-white-50 mt-1"><?php echo cms_paragraph('mural', 'hero.subtitle', 'Avisos e novidades do Instituto Social Novo Amanhecer'); ?></p>
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
        <h3 class="text-muted">Nenhum aviso publicado</h3>
        <p class="text-muted">Volte em breve para conferir as novidades do mural.</p>
        <a href="<?php echo $site_url; ?>/" class="btn btn-warning mt-2">
          <i class="bi bi-house-fill me-1"></i> Ir para o início
        </a>
      </div>
    <?php else: ?>

      <p class="text-muted mb-4">
        <i class="bi bi-info-circle me-1"></i>
        <?php echo count($muralImgs); ?> aviso<?php echo count($muralImgs) !== 1 ? 's' : ''; ?> publicado<?php echo count($muralImgs) !== 1 ? 's' : ''; ?> no mural.
        Clique em qualquer imagem para ampliar.
      </p>

      <?php foreach ($muralMonthGroups as $monthGroup): ?>
        <?php
          $monthItems = isset($monthGroup['items']) && is_array($monthGroup['items']) ? $monthGroup['items'] : [];
          $monthTotal = count($monthItems);
          if ($monthTotal === 1)      $colClass = 'col-sm-10 col-md-8 col-lg-6';
          elseif ($monthTotal === 2)  $colClass = 'col-sm-10 col-md-6';
          else                        $colClass = 'col-sm-10 col-md-6 col-lg-4';
        ?>
        <div class="mb-5">
          <h2 class="h4 fw-semibold mb-4"><?php echo htmlspecialchars((string)$monthGroup['label'], ENT_QUOTES, 'UTF-8'); ?></h2>
          <div class="row g-4 justify-content-center">
            <?php foreach ($monthItems as $muralItem): ?>
              <div class="<?php echo $colClass; ?>">
                <div class="card h-100 border-0 shadow overflow-hidden" style="border-top: 4px solid #ffc107 !important;">
                  <a href="#"
                     data-bs-toggle="modal"
                     data-bs-target="#modalMural"
                     data-mural-index="<?php echo (int)$muralItem['modal_index']; ?>"
                     title="Clique para ampliar">
                    <img
                      src="<?php echo htmlspecialchars((string)$muralItem['path'], ENT_QUOTES, 'UTF-8'); ?>"
                      alt="Aviso do mural de <?php echo htmlspecialchars((string)$monthGroup['label'], ENT_QUOTES, 'UTF-8'); ?>"
                      class="card-img-top w-100"
                      style="object-fit: contain; max-height: 520px; background: #f5f5f5;"
                      loading="lazy"
                    >
                  </a>
                  <div class="card-footer bg-transparent border-0 text-center py-2">
                    <small class="text-muted">
                      <i class="bi bi-calendar-event me-1"></i>
                      <?php echo date('d/m/Y', (int)$muralItem['timestamp']); ?>
                    </small>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

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
            <?php foreach ($muralImgs as $idx => $muralItem): ?>
              <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                <img
                  src="<?php echo htmlspecialchars((string)$muralItem['path'], ENT_QUOTES, 'UTF-8'); ?>"
                  class="d-block mx-auto img-fluid carousel-img"
                  alt="Aviso <?php echo $idx + 1; ?> do mural"
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
