<?php
$galleryConfig = gallery_load();
$hero = $galleryConfig['hero'];
$rawSections = $galleryConfig['sections'];
$gallerySections = [];
$projectRoot = dirname(__DIR__, 2);

foreach ($rawSections as $section) {
    $id = $section['id'];
    $title = $section['title'];
    $type = $section['type'];
    $background = isset($section['background']) && $section['background'] !== '' ? $section['background'] : '';
    $description = isset($section['description']) ? $section['description'] : '';
    $items = [];

    if ($type === 'directory') {
        $relativeDir = isset($section['directory']) ? trim($section['directory'], '/') : '';

        if ($relativeDir !== '') {
            $absoluteDir = $projectRoot . '/' . $relativeDir;
            $webBase = '/' . $relativeDir;

            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $priority = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
            $byBase = [];

            if (is_dir($absoluteDir)) {
                foreach (scandir($absoluteDir) as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    if (!in_array($ext, $allowedExt, true)) {
                        continue;
                    }

                    $base = pathinfo($file, PATHINFO_FILENAME);
                    $byBase[$base][$ext] = $file;
                }
            }

            foreach ($byBase as $base => $extMap) {
                foreach ($priority as $preferred) {
                    if (isset($extMap[$preferred])) {
                        $file = $extMap[$preferred];
                        $items[] = [
                            'type' => 'image',
                            'src' => $webBase . '/' . $file,
                            'alt' => ($section['caption_prefix'] ?? $title) . ' ' . (count($items) + 1),
                            'caption' => ($section['caption_prefix'] ?? $title) . ' ' . (count($items) + 1),
                        ];
                        break;
                    }
                }
            }
        }
    } else {
        if (isset($section['items']) && is_array($section['items'])) {
            foreach ($section['items'] as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $src = isset($item['src']) ? trim((string) $item['src']) : '';

                if ($src === '') {
                    continue;
                }

                $items[] = [
                    'type' => 'image',
                    'src' => $src,
                    'alt' => isset($item['alt']) && $item['alt'] !== '' ? (string) $item['alt'] : $title,
                    'caption' => isset($item['caption']) ? (string) $item['caption'] : '',
                ];
            }
        }
    }

    $modalId = 'gallery-modal-' . $id;
    $carouselId = 'gallery-carousel-' . $id;

    $gallerySections[] = [
        'id' => $id,
        'title' => $title,
        'background' => $background,
        'description' => $description,
        'items' => $items,
        'count' => count($items),
        'modal_id' => $modalId,
        'carousel_id' => $carouselId,
    ];
}
?>

<style>
    .modal-body img {
      max-height: 90vh;
      max-width: 100%;
      width: auto;
      display: block;
      margin: 0 auto;
    }

    .dark-theme h2 {
      color: #e0e0e0 !important;
    }

    .dark-theme .text-center h2 {
      color: #e0e0e0 !important;
    }

    .dark-theme section.py-5:not(.bg-light):not(.bg-white) {
      background-color: #1e1e1e !important;
    }

    .dark-theme section.py-5.bg-light {
      background-color: #252525 !important;
    }

    .dark-theme section.py-5.bg-white {
      background-color: #1e1e1e !important;
    }

    /* Modal da galeria com fundo escuro */
    .gallery-modal .modal-dialog {
      max-width: 100%;
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
    }

    .gallery-modal .modal-content {
      background-color: rgba(0, 0, 0, 0.85) !important;
      border-radius: 0 !important;
      height: 100vh;
      width: 100vw;
    }

    .gallery-modal .modal-header {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      z-index: 10;
      background: transparent !important;
      padding: 1rem !important;
    }

    .gallery-modal .contador-imagens {
      font-size: 1.1rem;
      font-weight: 500;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
    }

    .gallery-modal .btn-close-white {
      opacity: 1;
      filter: invert(1) brightness(2);
      width: 1.5rem;
      height: 1.5rem;
    }

    .gallery-modal .btn-close-white:hover {
      opacity: 0.8;
    }

    .gallery-modal .modal-body {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      padding: 0 !important;
    }

    .gallery-modal .carousel {
      width: 100%;
      height: 100%;
    }

    .gallery-modal .carousel-inner {
      height: 100%;
    }

    .gallery-modal .carousel-item {
      height: 100%;
      display: flex !important;
      align-items: center;
      justify-content: center;
    }

    .gallery-modal .carousel-item img {
      max-height: 85vh;
      max-width: 90vw;
      width: auto;
      height: auto;
      object-fit: contain;
    }

    .gallery-modal .carousel-control-prev,
    .gallery-modal .carousel-control-next {
      width: 60px;
      opacity: 0.8;
      z-index: 10;
    }

    .gallery-modal .carousel-control-prev:hover,
    .gallery-modal .carousel-control-next:hover {
      opacity: 1;
    }

    .gallery-modal .carousel-control-prev-icon,
    .gallery-modal .carousel-control-next-icon {
      width: 2.5rem;
      height: 2.5rem;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.8));
    }

    .gallery-modal .carousel-caption {
      display: none !important;
    }

    .gallery-section-toggle .bi {
      transition: transform 0.2s ease;
    }

    .gallery-section-toggle[aria-expanded="true"] .bi {
      transform: rotate(180deg);
    }

    .gallery-section-card {
      box-shadow: 0 0.75rem 1.75rem rgba(0, 0, 0, 0.12) !important;
    }

    #retorno-atividades-2026 .featured-video-player {
      position: relative;
      width: 100%;
      border-radius: 12px;
      overflow: hidden;
      background: #000;
    }

    #retorno-atividades-2026 .featured-video-player::before {
      content: "";
      display: block;
      padding-top: 56.25%;
    }

    @media (max-width: 767.98px) {
      #retorno-atividades-2026 .featured-video-player::before {
        padding-top: 177.7778%;
      }
    }

    #retorno-atividades-2026 .featured-video-player > video {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
</style>

<section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('<?php echo htmlspecialchars($hero['background'], ENT_QUOTES, 'UTF-8'); ?>'); height: <?php echo (int) $hero['height']; ?>px; background-size: cover; background-position: center;">
  <div class="container text-center">
    <h1 class="display-4"><?php echo htmlspecialchars($hero['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
  </div>
</section>

<!-- Seção Retorno às Atividades 2026 -->
<section class="py-5 bg-white" id="retorno-atividades-2026">
  <div class="container">
    <div class="card border-0 shadow-sm gallery-section-card">
      <div class="card-body">
        <div class="text-center mb-4">
          <h2 class="mb-3">Retorno às Atividades 2026</h2>
          <button
            type="button"
            class="btn btn-outline-secondary btn-sm gallery-section-toggle"
            data-bs-toggle="collapse"
            data-bs-target="#retorno-atividades-2026-collapse"
            aria-expanded="false"
            aria-controls="retorno-atividades-2026-collapse"
          >
            <i class="bi bi-chevron-down" aria-hidden="true"></i>
            <span class="visually-hidden">Mostrar ou ocultar seção Retorno às Atividades 2026</span>
          </button>
          <p class="text-muted mb-4">Confira o vídeo do retorno das atividades em 2026</p>
        </div>

        <div id="retorno-atividades-2026-collapse" class="collapse">
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="featured-video-player">
                <video
                  controls
                  preload="none"
                  src="https://api.chiapetta.dev/v/S3jJpk0G114IeNn5"
                  poster="/videos/retorno_atividades_2026_poster.jpg"
                  data-poster-desktop="/videos/retorno_atividades_2026_poster.jpg"
                  data-poster-mobile="/videos/retorno_atividades_2026_poster_vertical.jpg"
                >
                  Seu navegador não suporta o elemento de vídeo.
                </video>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php foreach ($gallerySections as $section): ?>
  <?php $collapseId = 'section-collapse-' . $section['id']; ?>
  <section class="py-5 <?php echo htmlspecialchars($section['background'], ENT_QUOTES, 'UTF-8'); ?>" id="galeria-<?php echo htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8'); ?>">
    <div class="container">
      <div class="card border-0 shadow-sm gallery-section-card">
        <div class="card-body">
          <div class="text-center mb-4">
            <h2 class="mb-3"><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <button
              type="button"
              class="btn btn-outline-secondary btn-sm gallery-section-toggle"
              data-bs-toggle="collapse"
              data-bs-target="#<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
              aria-expanded="false"
              aria-controls="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>"
            >
              <i class="bi bi-chevron-down" aria-hidden="true"></i>
              <span class="visually-hidden">Mostrar ou ocultar seção</span>
            </button>
            <?php if (!empty($section['description'])): ?>
              <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($section['description'], ENT_QUOTES, 'UTF-8')); ?></p>
            <?php endif; ?>
          </div>

          <div id="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>" class="collapse">
            <?php if ($section['count'] === 0): ?>
              <p class="text-center text-muted">Nenhum item cadastrado para esta seção no momento.</p>
            <?php else: ?>
              <div class="row g-4">
                <?php foreach ($section['items'] as $index => $item): ?>
                  <div class="col-sm-6 col-md-4 col-lg-4">
                    <div class="ratio ratio-1x1">
                      <img
                        src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                        data-src="<?php echo htmlspecialchars($item['src'], ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-100 h-100 rounded shadow-sm galeria-img"
                        alt="<?php echo htmlspecialchars($item['alt'], ENT_QUOTES, 'UTF-8'); ?>"
                        style="object-fit: cover; cursor: pointer;"
                        data-bs-toggle="modal"
                        data-bs-target="#<?php echo htmlspecialchars($section['modal_id'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-modal-target="#<?php echo htmlspecialchars($section['modal_id'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-gallery-index="<?php echo $index; ?>"
                        loading="lazy"
                        decoding="async"
                      >
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php endforeach; ?>

<?php foreach ($gallerySections as $section): ?>
  <?php if ($section['count'] === 0) { continue; } ?>
  <div class="modal fade gallery-modal" id="<?php echo htmlspecialchars($section['modal_id'], ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-dark bg-opacity-75 border-0 rounded">
        <div class="modal-header border-0 p-2">
          <span class="text-white contador-imagens me-auto ms-2 js-gallery-counter">1/<?php echo $section['count']; ?></span>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-0">
          <div id="<?php echo htmlspecialchars($section['carousel_id'], ENT_QUOTES, 'UTF-8'); ?>" class="carousel slide carousel-fade" data-bs-ride="false" data-bs-touch="true">
            <div class="carousel-inner">
              <?php foreach ($section['items'] as $index => $item): ?>
                <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
                  <img
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                    data-src="<?php echo htmlspecialchars($item['src'], ENT_QUOTES, 'UTF-8'); ?>"
                    class="d-block mx-auto img-fluid carousel-img"
                    alt="<?php echo htmlspecialchars($item['alt'], ENT_QUOTES, 'UTF-8'); ?>"
                    loading="lazy"
                    decoding="async"
                  >
                  <?php if (!empty($item['caption'])): ?>
                    <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                      <h5 class="mb-0"><?php echo htmlspecialchars($item['caption'], ENT_QUOTES, 'UTF-8'); ?></h5>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo htmlspecialchars($section['carousel_id'], ENT_QUOTES, 'UTF-8'); ?>" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span><span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo htmlspecialchars($section['carousel_id'], ENT_QUOTES, 'UTF-8'); ?>" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span><span class="visually-hidden">Próximo</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<!-- Seção Outubro Rosa -->
<section class="py-5 bg-light" id="outubro-rosa">
  <div class="container">
    <div class="card border-0 shadow-sm gallery-section-card">
      <div class="card-body">
        <div class="text-center mb-4">
          <h2 class="mb-3" style="color: #d63384;">Outubro Rosa</h2>
          <button
            type="button"
            class="btn btn-outline-secondary btn-sm gallery-section-toggle"
            data-bs-toggle="collapse"
            data-bs-target="#outubro-rosa-collapse"
            aria-expanded="false"
            aria-controls="outubro-rosa-collapse"
          >
            <i class="bi bi-chevron-down" aria-hidden="true"></i>
            <span class="visually-hidden">Mostrar ou ocultar seção Outubro Rosa</span>
          </button>
          <p class="text-muted mb-4">Conscientização sobre a prevenção do câncer de mama</p>
        </div>

        <div id="outubro-rosa-collapse" class="collapse">
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="outubro-rosa-player">
                <video
                  class="outubro-rosa-video"
                  controls
                  preload="none"
                  src="https://api.chiapetta.dev/v/ItV-Nx6UsanFr8DH"
                  poster="/videos/outubro_rosa_poster.jpg"
                  data-src-desktop="https://api.chiapetta.dev/v/ItV-Nx6UsanFr8DH"
                  data-src-mobile="https://api.chiapetta.dev/v/KfyfXHINHWwqZ_Bk"
                  data-poster-desktop="/videos/outubro_rosa_poster.jpg"
                  data-poster-mobile="/videos/outubro_rosa_poster_vertical.jpg"
                >
                  Seu navegador não suporta o elemento de vídeo.
                </video>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap === 'undefined') {
      return;
    }

    function hydrateLazyImages(scope) {
      if (!scope) {
        return;
      }

      scope.querySelectorAll('img[data-src]').forEach(function(img) {
        if (!img.getAttribute('src') || img.getAttribute('src').indexOf('data:image/gif') === 0) {
          img.setAttribute('src', img.getAttribute('data-src'));
        }
      });
    }

    const counters = new Map();

    document.querySelectorAll('[id^="section-collapse-"]').forEach(function(collapseEl) {
      collapseEl.addEventListener('show.bs.collapse', function() {
        hydrateLazyImages(collapseEl);
      });
    });

    document.querySelectorAll('.gallery-modal').forEach(function(modalElement) {
      modalElement.addEventListener('shown.bs.modal', function(event) {
        const carouselElement = modalElement.querySelector('.carousel');

        hydrateLazyImages(modalElement);

        if (!carouselElement) {
          return;
        }

        bootstrap.Carousel.getOrCreateInstance(carouselElement, {
          interval: false,
          ride: false,
          touch: true,
          wrap: true
        });
      });

      modalElement.addEventListener('slid.bs.carousel', function(event) {
        const carouselElement = event.target;
        const counter = modalElement.querySelector('.js-gallery-counter');

        if (!counter) {
          return;
        }

        const activeIndex = event.to !== undefined ? event.to : Array.prototype.indexOf.call(carouselElement.querySelectorAll('.carousel-item'), carouselElement.querySelector('.carousel-item.active'));
        const total = carouselElement.querySelectorAll('.carousel-item').length;
        counter.textContent = (activeIndex + 1) + '/' + total;
      });

      const counter = modalElement.querySelector('.js-gallery-counter');
      const carouselElement = modalElement.querySelector('.carousel');

      if (counter && carouselElement) {
        counters.set(modalElement.id, { counter, carouselElement });
      }
    });

    document.querySelectorAll('[data-modal-target][data-gallery-index]').forEach(function(trigger) {
      trigger.addEventListener('click', function() {
        const modalSelector = trigger.getAttribute('data-modal-target');
        const slideIndex = parseInt(trigger.getAttribute('data-gallery-index') || '0', 10);

        if (!modalSelector) {
          return;
        }

        const modalElement = document.querySelector(modalSelector);

        if (!modalElement) {
          return;
        }

        const carouselElement = modalElement.querySelector('.carousel');

        if (!carouselElement) {
          return;
        }

        const carousel = bootstrap.Carousel.getOrCreateInstance(carouselElement, {
          interval: false,
          ride: false,
          touch: true,
          wrap: true
        });

        carousel.to(slideIndex);

        const info = counters.get(modalElement.id);
        if (info) {
          const total = info.carouselElement.querySelectorAll('.carousel-item').length;
          info.counter.textContent = (slideIndex + 1) + '/' + total;
        }
      });
    });

    const retornoVideo = document.querySelector('#retorno-atividades-2026 video');
    const mqMobile = window.matchMedia('(max-width: 767.98px)');

    function applyRetornoPoster(isMobileView) {
      if (!retornoVideo) {
        return;
      }

      const posterDesktop = retornoVideo.getAttribute('data-poster-desktop') || retornoVideo.getAttribute('poster') || '';
      const posterMobile = retornoVideo.getAttribute('data-poster-mobile') || posterDesktop;
      const targetPoster = isMobileView ? posterMobile : posterDesktop;

      if (targetPoster && retornoVideo.getAttribute('poster') !== targetPoster) {
        retornoVideo.setAttribute('poster', targetPoster);
      }
    }

    applyRetornoPoster(mqMobile.matches);

    if (typeof mqMobile.addEventListener === 'function') {
      mqMobile.addEventListener('change', function(event) {
        applyRetornoPoster(event.matches);
      });
    } else if (typeof mqMobile.addListener === 'function') {
      mqMobile.addListener(function(event) {
        applyRetornoPoster(event.matches);
      });
    }
  });
</script>
