<?php
require_once __DIR__ . '/../services/PdfRenderer.php';

$documents = PdfRenderer::documents();
$pdfRenderingAvailable = PdfRenderer::supportsRendering();
?>

<style>
  .documents-hero {
    min-height: 420px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }

  .document-card {
    height: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .document-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .75rem 1.5rem rgba(0, 0, 0, .14) !important;
  }

  .pdf-preview {
    aspect-ratio: 4 / 3;
    background: #f6f7f9;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    overflow: hidden;
  }

  .pdf-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: top center;
    display: block;
  }

  .pdf-modal-toolbar {
    gap: .5rem;
  }

  .pdf-stage {
    height: min(78vh, 820px);
    overflow: auto;
    background: #eef0f3;
  }

  .pdf-stage-inner {
    min-width: 100%;
    padding: 1rem;
    display: flex;
    align-items: flex-start;
    justify-content: center;
  }

  #pdfModalImage {
    width: auto;
    max-width: 100%;
    height: auto;
    background: #fff;
    box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .18);
    transform-origin: top center;
  }

  #pdfModalImage.is-zoomed {
    max-width: none;
  }

  @media (max-width: 767.98px) {
    .documents-hero {
      min-height: 320px;
    }

    .documents-hero .display-4 {
      font-size: 2.25rem;
    }

    .pdf-stage {
      height: 72vh;
    }

    .pdf-stage-inner {
      padding: .75rem;
    }

    .pdf-modal-toolbar {
      width: 100%;
      justify-content: space-between;
    }

    .pdf-modal-toolbar .btn {
      padding-inline: .55rem;
    }
  }
</style>

<section class="hero documents-hero bg-image text-white d-flex align-items-center" style="background-image: url('<?php echo cms_attr('titulos-documentos', 'hero.image', '/images/imagem.jpg'); ?>');">
  <div class="container text-center">
    <h1 class="display-4"><?php echo cms_text('titulos-documentos', 'hero.title', 'Títulos e Documentos'); ?></h1>
    <p class="lead"><?php echo cms_paragraph('titulos-documentos', 'hero.subtitle', 'Conheça nossos títulos, certificações e documentos institucionais que demonstram nosso compromisso com a transparência e a prestação de contas.'); ?></p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h1 class="text-center mb-5"><?php echo cms_text('titulos-documentos', 'main.title', 'Títulos e Documentos'); ?></h1>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
      <?php foreach ($documents as $document): ?>
        <?php
          $docKey = htmlspecialchars($document['key'], ENT_QUOTES, 'UTF-8');
          $docTitle = htmlspecialchars($document['title'], ENT_QUOTES, 'UTF-8');
          $pageCount = (int) ($document['pages'] ?? 1);
          $pdfUrl = url('/docs/' . rawurlencode($document['file']));
          $thumbnailFile = $document['thumbnail'] ?? 'all_documents.png';
          $thumbnailPath = __DIR__ . '/../../thumbnails/' . $thumbnailFile;
          $fallbackThumb = url('/thumbnails/' . rawurlencode($thumbnailFile));
          if (is_file($thumbnailPath)) {
            $fallbackThumb .= '?v=' . filemtime($thumbnailPath);
          }
          $thumbUrl = $pdfRenderingAvailable
            ? url('/api/pdf-page?doc=' . rawurlencode($document['key']) . '&page=1&size=thumb')
            : $fallbackThumb;
        ?>
        <div class="col">
          <div class="card document-card shadow-sm">
            <div class="pdf-preview">
              <a class="d-flex w-100 h-100" href="<?php echo htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                <img
                  src="<?php echo htmlspecialchars($thumbUrl, ENT_QUOTES, 'UTF-8'); ?>"
                  alt="<?php echo $docTitle; ?>"
                  loading="lazy"
                  decoding="async"
                >
              </a>
            </div>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?php echo $docTitle; ?></h5>
              <?php if ($pdfRenderingAvailable): ?>
                <button
                  class="btn btn-primary mt-auto"
                  type="button"
                  data-bs-toggle="modal"
                  data-bs-target="#modalPdf"
                  data-doc="<?php echo $docKey; ?>"
                  data-title="<?php echo $docTitle; ?>"
                  data-pages="<?php echo $pageCount; ?>"
                >
                  Visualizar Completo
                </button>
              <?php else: ?>
                <a class="btn btn-primary mt-auto" href="<?php echo htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                  Abrir Documento
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="modal fade" id="modalPdf" tabindex="-1" aria-labelledby="modalPdfLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
    <div class="modal-content">
      <div class="modal-header flex-wrap align-items-center">
        <h5 class="modal-title me-auto" id="modalPdfLabel">Visualização do Documento</h5>
        <div class="pdf-modal-toolbar d-flex flex-wrap align-items-center me-2">
          <button id="pdfPrevPage" class="btn btn-sm btn-outline-secondary" type="button">
            <i class="bi bi-chevron-left"></i>
            <span class="d-none d-sm-inline">Anterior</span>
          </button>
          <span class="small fw-semibold px-1">
            Página: <span id="pdfCurrentPage">1</span> / <span id="pdfTotalPages">1</span>
          </span>
          <button id="pdfNextPage" class="btn btn-sm btn-outline-secondary" type="button">
            <span class="d-none d-sm-inline">Próxima</span>
            <i class="bi bi-chevron-right"></i>
          </button>
          <button id="pdfZoomOut" class="btn btn-sm btn-outline-secondary" type="button" title="Diminuir zoom">
            <i class="bi bi-zoom-out"></i>
          </button>
          <button id="pdfZoomReset" class="btn btn-sm btn-outline-secondary" type="button" title="Ajustar à tela">
            <i class="bi bi-arrows-angle-contract"></i>
          </button>
          <button id="pdfZoomIn" class="btn btn-sm btn-outline-secondary" type="button" title="Aumentar zoom">
            <i class="bi bi-zoom-in"></i>
          </button>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body p-0">
        <div class="pdf-stage" id="pdfStage">
          <div class="pdf-stage-inner">
            <div id="pdfLoading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
              </div>
              <p class="mt-3 mb-0">Carregando documento...</p>
            </div>
            <img id="pdfModalImage" alt="Documento selecionado" hidden>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const siteUrl = <?php echo json_encode($site_url, JSON_UNESCAPED_SLASHES); ?>;
    const modal = document.getElementById('modalPdf');
    const modalTitle = document.getElementById('modalPdfLabel');
    const image = document.getElementById('pdfModalImage');
    const loading = document.getElementById('pdfLoading');
    const stage = document.getElementById('pdfStage');
    const currentPageLabel = document.getElementById('pdfCurrentPage');
    const totalPagesLabel = document.getElementById('pdfTotalPages');
    const prevButton = document.getElementById('pdfPrevPage');
    const nextButton = document.getElementById('pdfNextPage');
    const zoomOutButton = document.getElementById('pdfZoomOut');
    const zoomResetButton = document.getElementById('pdfZoomReset');
    const zoomInButton = document.getElementById('pdfZoomIn');
    const loadingMarkup = loading.innerHTML;

    let activeDoc = '';
    let activeTitle = '';
    let currentPage = 1;
    let totalPages = 1;
    let zoom = 1;

    function pageUrl(doc, page) {
      const params = new URLSearchParams({ doc: doc, page: String(page), size: 'full' });
      return siteUrl + '/api/pdf-page?' + params.toString();
    }

    function setLoading(isLoading) {
      loading.hidden = !isLoading;
      image.hidden = isLoading;
    }

    function updateButtons() {
      currentPageLabel.textContent = String(currentPage);
      totalPagesLabel.textContent = String(totalPages);
      prevButton.disabled = currentPage <= 1;
      nextButton.disabled = currentPage >= totalPages;
    }

    function applyZoom() {
      image.style.transform = 'scale(' + zoom + ')';
      image.classList.toggle('is-zoomed', zoom > 1);
    }

    function loadPage(page) {
      currentPage = Math.max(1, Math.min(page, totalPages));
      updateButtons();
      setLoading(true);
      stage.scrollTop = 0;
      stage.scrollLeft = 0;
      image.src = pageUrl(activeDoc, currentPage);
    }

    modal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      if (!button) {
        return;
      }

      activeDoc = button.getAttribute('data-doc') || '';
      activeTitle = button.getAttribute('data-title') || 'Documento';
      totalPages = Math.max(1, parseInt(button.getAttribute('data-pages') || '1', 10));
      currentPage = 1;
      zoom = 1;

      modalTitle.textContent = activeTitle;
      image.alt = activeTitle;
      image.removeAttribute('src');
      loading.innerHTML = loadingMarkup;
      applyZoom();
      loadPage(1);
    });

    modal.addEventListener('hidden.bs.modal', function () {
      image.removeAttribute('src');
      setLoading(true);
      document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.remove();
      });
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
    });

    image.addEventListener('load', function () {
      setLoading(false);
    });

    image.addEventListener('error', function () {
      loading.hidden = false;
      image.hidden = true;
      loading.innerHTML = '<p class="text-danger mb-0">Não foi possível carregar este documento.</p>';
    });

    prevButton.addEventListener('click', function () {
      if (currentPage > 1) {
        loadPage(currentPage - 1);
      }
    });

    nextButton.addEventListener('click', function () {
      if (currentPage < totalPages) {
        loadPage(currentPage + 1);
      }
    });

    zoomOutButton.addEventListener('click', function () {
      zoom = Math.max(0.7, zoom - 0.15);
      applyZoom();
    });

    zoomResetButton.addEventListener('click', function () {
      zoom = 1;
      applyZoom();
      stage.scrollTop = 0;
      stage.scrollLeft = 0;
    });

    zoomInButton.addEventListener('click', function () {
      zoom = Math.min(2.5, zoom + 0.15);
      applyZoom();
    });

    document.addEventListener('keydown', function (event) {
      if (!modal.classList.contains('show')) {
        return;
      }

      if (event.key === 'ArrowLeft' || event.key === 'PageUp') {
        prevButton.click();
      } else if (event.key === 'ArrowRight' || event.key === 'PageDown') {
        nextButton.click();
      } else if (event.key === '+' || event.key === '=') {
        zoomInButton.click();
      } else if (event.key === '-' || event.key === '_') {
        zoomOutButton.click();
      } else if (event.key === '0') {
        zoomResetButton.click();
      }
    });
  });
</script>
