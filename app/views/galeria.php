<?php
$galleryConfig = gallery_load();
$hero = $galleryConfig['hero'];
$rawSections = $galleryConfig['sections'];
$gallerySections = [];

foreach ($rawSections as $section) {
    $id = $section['id'];
    $title = $section['title'];
    $background = isset($section['background']) && $section['background'] !== '' ? $section['background'] : '';
    $description = isset($section['description']) ? $section['description'] : '';
    $items = GalleryModel::sectionItems($section);

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
      max-height: 90dvh;
      max-width: 100%;
      width: auto;
      display: block;
      margin: 0 auto;
    }

    .project-gallery-hero {
      min-height: 320px;
      min-height: clamp(260px, 48vh, var(--gallery-hero-height, 600px));
      min-height: clamp(260px, 48dvh, var(--gallery-hero-height, 600px));
      height: clamp(260px, 48vh, var(--gallery-hero-height, 600px));
      height: clamp(260px, 48dvh, var(--gallery-hero-height, 600px));
      padding: 3rem 0;
    }

    .project-gallery-hero h1 {
      font-size: clamp(2rem, 5vw, 3.5rem);
      line-height: 1.1;
      margin: 0;
      overflow-wrap: anywhere;
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
      height: 100dvh;
      width: 100vw;
    }

    .gallery-modal .modal-header {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      z-index: 10;
      background: transparent !important;
      padding: max(0.75rem, env(safe-area-inset-top)) max(0.75rem, env(safe-area-inset-right)) 0.75rem max(0.75rem, env(safe-area-inset-left)) !important;
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
      height: 100vh;
      height: 100dvh;
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
      max-height: calc(100vh - 96px);
      max-height: calc(100dvh - 96px);
      max-width: calc(100vw - 96px);
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

    .gallery-section-card {
      box-shadow: 0 0.75rem 1.75rem rgba(0, 0, 0, 0.12) !important;
      overflow: hidden;
    }

    .project-gallery-section {
      scroll-margin-top: 6rem;
    }

    .project-gallery-section .card-body {
      padding: clamp(1rem, 3vw, 2rem);
    }

    .project-gallery-section h2 {
      font-size: clamp(1.35rem, 3vw, 2rem);
      line-height: 1.2;
      overflow-wrap: anywhere;
    }

    .project-gallery-grid {
      --bs-gutter-x: clamp(0.75rem, 2vw, 1.5rem);
      --bs-gutter-y: clamp(0.75rem, 2vw, 1.5rem);
    }

    .project-gallery-tile {
      background: #f1f3f5;
      border-radius: 0.5rem;
      overflow: hidden;
    }

    .project-gallery-tile .galeria-img {
      display: block;
      background: #f1f3f5;
    }

    .album-selector-panel {
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.05);
    }

    .album-selector-panel .form-select {
      min-height: 48px;
      font-size: 1rem;
      text-overflow: ellipsis;
    }

    .dark-theme .album-selector-panel {
      background-color: #1e1e1e !important;
      border-color: rgba(255, 255, 255, 0.12);
    }

    .dark-theme .album-selector-panel .form-select {
      background-color: #252525;
      border-color: rgba(255, 255, 255, 0.2);
      color: #e0e0e0;
    }

    @media (hover: none) {
      .project-gallery-tile .galeria-img:hover {
        transform: none;
        opacity: 1;
      }
    }

    @media (max-width: 767.98px) {
      .project-gallery-hero {
        min-height: 220px;
        height: 34vh;
        height: 34dvh;
        padding: 2rem 0;
      }

      .album-selector-panel {
        padding-top: 0.875rem !important;
        padding-bottom: 0.875rem !important;
      }

      .album-selector-panel label {
        font-size: 0.95rem;
        margin-bottom: 0.4rem;
      }

      .album-selector-panel .form-select {
        min-height: 44px;
        font-size: 0.95rem;
        padding-right: 2rem;
      }

      .project-gallery-section {
        padding-top: 1.5rem !important;
        padding-bottom: 1.5rem !important;
        scroll-margin-top: 5.5rem;
      }

      .project-gallery-section .container,
      .album-selector-panel .container {
        padding-left: 0.875rem;
        padding-right: 0.875rem;
      }

      .gallery-section-card {
        border-radius: 0.5rem;
      }

      .gallery-modal .contador-imagens {
        font-size: 0.95rem;
      }

      .gallery-modal .btn-close-white {
        width: 1.25rem;
        height: 1.25rem;
      }

      .gallery-modal .carousel-item img {
        max-height: calc(100vh - 88px);
        max-height: calc(100dvh - 88px);
        max-width: calc(100vw - 24px);
      }

      .gallery-modal .carousel-control-prev,
      .gallery-modal .carousel-control-next {
        width: 44px;
      }

      .gallery-modal .carousel-control-prev-icon,
      .gallery-modal .carousel-control-next-icon {
        width: 2rem;
        height: 2rem;
      }
    }

</style>

<section class="hero bg-image text-white d-flex align-items-center project-gallery-hero" style="background-image: url('<?php echo cms_attr('galeria', 'hero.image', isset($hero['background']) ? (string)$hero['background'] : '/images/imagem.jpg'); ?>'); --gallery-hero-height: <?php echo (int) $hero['height']; ?>px; background-size: cover; background-position: center;">
  <div class="container text-center">
    <h1 class="display-4"><?php echo htmlspecialchars($hero['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
  </div>
</section>

<?php if (!empty($gallerySections)): ?>
<section class="album-selector-panel py-4 bg-white">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <label for="project-album-select" class="form-label fw-semibold">Selecione um álbum</label>
        <select id="project-album-select" class="form-select form-select-lg" aria-label="Selecionar álbum de projetos em execução">
          <?php foreach ($gallerySections as $section): ?>
            <option value="<?php echo htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) $section['count']; ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php foreach ($gallerySections as $sectionIndex => $section): ?>
  <section
    class="project-gallery-section py-5 <?php echo htmlspecialchars($section['background'], ENT_QUOTES, 'UTF-8'); ?><?php echo $sectionIndex === 0 ? '' : ' d-none'; ?>"
    id="galeria-<?php echo htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8'); ?>"
    data-gallery-album="<?php echo htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8'); ?>"
  >
    <div class="container">
      <div class="card border-0 shadow-sm gallery-section-card">
        <div class="card-body">
          <div class="text-center mb-4">
            <h2 class="mb-3"><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php if (!empty($section['description'])): ?>
              <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($section['description'], ENT_QUOTES, 'UTF-8')); ?></p>
            <?php endif; ?>
          </div>

          <?php if ($section['count'] === 0): ?>
            <p class="text-center text-muted">Nenhum item cadastrado para esta seção no momento.</p>
          <?php else: ?>
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 project-gallery-grid">
              <?php foreach ($section['items'] as $index => $item): ?>
                <div class="col">
                  <div class="ratio ratio-1x1 project-gallery-tile">
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
  </section>
<?php endforeach; ?>

<?php foreach ($gallerySections as $section): ?>
  <?php if ($section['count'] === 0) { continue; } ?>
  <div
    class="modal fade gallery-modal"
    id="<?php echo htmlspecialchars($section['modal_id'], ENT_QUOTES, 'UTF-8'); ?>"
    tabindex="-1"
    aria-hidden="true"
    data-gallery-modal-album="<?php echo htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8'); ?>"
  >
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap === 'undefined') {
      return;
    }

    const lazyPlaceholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

    function hydrateLazyImages(scope) {
      if (!scope) {
        return;
      }

      scope.querySelectorAll('img[data-src]').forEach(function(img) {
        if (!img.getAttribute('src') || img.getAttribute('src') === lazyPlaceholder) {
          img.setAttribute('src', img.getAttribute('data-src'));
        }
      });
    }

    function resetLazyImages(scope) {
      if (!scope) {
        return;
      }

      scope.querySelectorAll('img[data-src]').forEach(function(img) {
        if (img.getAttribute('src') && img.getAttribute('src') !== lazyPlaceholder) {
          img.setAttribute('src', lazyPlaceholder);
        }
      });
    }

    const counters = new Map();

    document.querySelectorAll('.gallery-modal').forEach(function(modalElement) {
      modalElement.addEventListener('shown.bs.modal', function(event) {
        const carouselElement = modalElement.querySelector('.carousel');
        const modalAlbumId = modalElement.getAttribute('data-gallery-modal-album');

        if (!albumSelect || modalAlbumId === albumSelect.value) {
          hydrateLazyImages(modalElement);
        }

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

    const albumSelect = document.getElementById('project-album-select');
    const albumSections = Array.prototype.slice.call(document.querySelectorAll('[data-gallery-album]'));

    function albumIdFromHash() {
      if (!window.location.hash) {
        return '';
      }

      const hash = decodeURIComponent(window.location.hash.substring(1));
      const prefix = 'galeria-';

      return hash.indexOf(prefix) === 0 ? hash.substring(prefix.length) : hash;
    }

    function showAlbum(albumId, shouldScroll) {
      if (!albumId || albumSections.length === 0) {
        return;
      }

      let selectedSection = null;

      albumSections.forEach(function(section) {
        const isSelected = section.getAttribute('data-gallery-album') === albumId;
        section.classList.toggle('d-none', !isSelected);

        if (isSelected) {
          selectedSection = section;
          hydrateLazyImages(section);
        } else {
          resetLazyImages(section);
        }
      });

      document.querySelectorAll('[data-gallery-modal-album]').forEach(function(modalElement) {
        if (modalElement.getAttribute('data-gallery-modal-album') !== albumId) {
          resetLazyImages(modalElement);
        }
      });

      if (!selectedSection) {
        return;
      }

      if (albumSelect && albumSelect.value !== albumId) {
        albumSelect.value = albumId;
      }

      if (shouldScroll) {
        selectedSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }

    if (albumSelect && albumSections.length > 0) {
      const requestedAlbumId = albumIdFromHash();
      const initialAlbumId = albumSections.some(function(section) {
        return section.getAttribute('data-gallery-album') === requestedAlbumId;
      }) ? requestedAlbumId : albumSelect.value;

      albumSelect.value = initialAlbumId;
      showAlbum(initialAlbumId, requestedAlbumId !== '');

      albumSelect.addEventListener('change', function() {
        showAlbum(albumSelect.value, true);

        if (history.replaceState) {
          history.replaceState(null, '', '#galeria-' + albumSelect.value);
        }
      });

      window.addEventListener('hashchange', function() {
        const albumId = albumIdFromHash();
        const hasAlbum = albumSections.some(function(section) {
          return section.getAttribute('data-gallery-album') === albumId;
        });

        if (hasAlbum) {
          showAlbum(albumId, true);
        }
      });
    }

  });
</script>
