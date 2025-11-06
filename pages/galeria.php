<?php
$galleryConfig = gallery_load();
$hero = $galleryConfig['hero'];
$rawSections = $galleryConfig['sections'];
$gallerySections = [];
$projectRoot = dirname(__DIR__);

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
</style>

<section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('<?php echo htmlspecialchars($hero['background'], ENT_QUOTES, 'UTF-8'); ?>'); height: <?php echo (int) $hero['height']; ?>px; background-size: cover; background-position: center;">
  <div class="container text-center">
    <h1 class="display-4"><?php echo htmlspecialchars($hero['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
  </div>
</section>

<!-- Seção Outubro Rosa -->
<section class="py-5 bg-light" id="outubro-rosa">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="mb-3" style="color: #d63384;">Outubro Rosa</h2>
      <p class="text-muted mb-4">Conscientização sobre a prevenção do câncer de mama</p>
    </div>
    
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="outubro-rosa-player">
          <video
            class="outubro-rosa-video"
            controls
            preload="metadata"
            src="https://api.chiapetta.dev/v/ItV-Nx6UsanFr8DH"
            poster="/videos/outubro_rosa_poster.jpg"
            data-src-desktop="https://api.chiapetta.dev/v/ItV-Nx6UsanFr8DH"
            data-src-mobile="https://api.chiapetta.dev/v/KfyfXHINHWwqZ_Bk"
            data-poster-desktop="/videos/outubro_rosa_poster.jpg"
            data-poster-mobile="/videos/outubro_rosa_poster.jpg"
          >
            Seu navegador não suporta o elemento de vídeo.
          </video>
        </div>
      </div>
    </div>
  </div>
</section>

<?php foreach ($gallerySections as $section): ?>
  <section class="py-5 <?php echo htmlspecialchars($section['background'], ENT_QUOTES, 'UTF-8'); ?>" id="galeria-<?php echo htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8'); ?>">
    <div class="container">
      <div class="text-center mb-4">
        <h2 class="mb-3"><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php if (!empty($section['description'])): ?>
          <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($section['description'], ENT_QUOTES, 'UTF-8')); ?></p>
        <?php endif; ?>
      </div>

      <?php if ($section['count'] === 0): ?>
        <p class="text-center text-muted">Nenhum item cadastrado para esta seção no momento.</p>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($section['items'] as $index => $item): ?>
            <div class="col-sm-6 col-md-4 col-lg-4">
              <div class="ratio ratio-1x1">
                <img
                  src="<?php echo htmlspecialchars($item['src'], ENT_QUOTES, 'UTF-8'); ?>"
                  class="w-100 h-100 rounded shadow-sm galeria-img"
                  alt="<?php echo htmlspecialchars($item['alt'], ENT_QUOTES, 'UTF-8'); ?>"
                  style="object-fit: cover; cursor: pointer;"
                  data-bs-toggle="modal"
                  data-bs-target="#<?php echo htmlspecialchars($section['modal_id'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-modal-target="#<?php echo htmlspecialchars($section['modal_id'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-gallery-index="<?php echo $index; ?>"
                  loading="lazy"
                >
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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
                  <img src="<?php echo htmlspecialchars($item['src'], ENT_QUOTES, 'UTF-8'); ?>" class="d-block mx-auto img-fluid carousel-img" alt="<?php echo htmlspecialchars($item['alt'], ENT_QUOTES, 'UTF-8'); ?>">
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap === 'undefined') {
      return;
    }

    const counters = new Map();

    document.querySelectorAll('.gallery-modal').forEach(function(modalElement) {
      modalElement.addEventListener('shown.bs.modal', function(event) {
        const carouselElement = modalElement.querySelector('.carousel');

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
  });
</script>
