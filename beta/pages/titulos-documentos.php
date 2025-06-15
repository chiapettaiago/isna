<style>
    .pdf-preview {
      height: 300px;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      margin-bottom: 1rem;
      overflow: hidden;
      background: #fff;
      position: relative;
    }
    .pdf-preview iframe {
      width: 100%;
      height: 100%;
      border: none;
      position: absolute;
      top: 0;
      left: 0;
    }
    .pdf-preview.rotate-90 {
      height: 400px;
    }
    .pdf-preview.rotate-90 iframe {
      transform: rotate(90deg);
      transform-origin: 0 0;
      width: 100vh;
      height: 100vw;
      position: absolute;
      top: 0;
      left: 100%;
    }
    .card {
      transition: transform 0.2s;
      will-change: transform;
      height: 100%;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card-body {
      padding: 1rem;
    }
    .card-title {
      margin-bottom: 1rem;
      font-size: 1.1rem;
      font-weight: 600;
    }
    .btn-primary {
      width: 100%;
      margin-top: 0.5rem;
    }
</style>
   <!-- Seção Hero -->
   <section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 600px;  background-size: cover; background-position: center; background-repeat: no-repeat;">
     <div class="container text-center">
       <h1 class="display-4">Títulos e Documentos</h1>
       <p class="lead">Conheça nossos títulos, certificações e documentos institucionais que demonstram nosso compromisso com a transparência e a prestação de contas.</p>
     </div>
   </section>

  <!-- Seção Principal -->
  <section class="py-5">
    <div class="container">
      <h1 class="text-center mb-5">Títulos e Documentos</h1>

      <div class="row g-4">
        <!-- Card: CERTIFICADO CEBAS -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="pdf-preview">
              <iframe data-src="/docs/CERTIFICADO CEBAS.pdf#toolbar=0&navpanes=0&view=Fit" type="application/pdf"></iframe>
            </div>
            <div class="card-body">
              <h5 class="card-title">CERTIFICADO CEBAS</h5>
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalPdf" data-pdf="/docs/CERTIFICADO CEBAS.pdf">Visualizar Completo</button>
            </div>
          </div>
        </div>

        <!-- Card: CertificadoSiconv -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="pdf-preview">
              <iframe data-src="/docs/CertificadoSiconv.pdf#toolbar=0&navpanes=0&view=Fit" type="application/pdf"></iframe>
            </div>
            <div class="card-body">
              <h5 class="card-title">Certificado Siconv</h5>
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalPdf" data-pdf="/docs/CertificadoSiconv.pdf">Visualizar Completo</button>
            </div>
          </div>
        </div>

        <!-- Card: cmas -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="pdf-preview">
              <iframe data-src="/docs/cmas.pdf#toolbar=0&navpanes=0&view=Fit" type="application/pdf"></iframe>
            </div>
            <div class="card-body">
              <h5 class="card-title">CMAS</h5>
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalPdf" data-pdf="/docs/cmas.pdf">Visualizar Completo</button>
            </div>
          </div>
        </div>

        <!-- Card: ISNA_Declaração -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="pdf-preview">
              <iframe data-src="/docs/ISNA_Declaração.pdf#toolbar=0&navpanes=0&view=Fit" type="application/pdf"></iframe>
            </div>
            <div class="card-body">
              <h5 class="card-title">Declaração ISNA</h5>
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalPdf" data-pdf="/docs/ISNA_Declaração.pdf">Visualizar Completo</button>
            </div>
          </div>
        </div>

        <!-- Card: OSCIP -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="pdf-preview">
              <iframe data-src="/docs/OSCIP.pdf#toolbar=0&navpanes=0&view=Fit" type="application/pdf"></iframe>
            </div>
            <div class="card-body">
              <h5 class="card-title">OSCIP</h5>
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalPdf" data-pdf="/docs/OSCIP.pdf">Visualizar Completo</button>
            </div>
          </div>
        </div>

        <!-- Card: Titulo de Utilidade publica municipal -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="pdf-preview">
              <iframe data-src="/docs/Titulo de Utilidade publica municipal.pdf#toolbar=0&navpanes=0&view=Fit" type="application/pdf"></iframe>
            </div>
            <div class="card-body">
              <h5 class="card-title">Título de Utilidade Pública Municipal</h5>
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalPdf" data-pdf="/docs/Titulo de Utilidade publica municipal.pdf">Visualizar Completo</button>
            </div>
          </div>
        </div>

        <!-- Card: Titulo de Utilidade publica -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="pdf-preview">
              <iframe data-src="/docs/Titulo de Utilidade publica.pdf#toolbar=0&navpanes=0&view=Fit" type="application/pdf"></iframe>
            </div>
            <div class="card-body">
              <h5 class="card-title">Título de Utilidade Pública</h5>
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalPdf" data-pdf="/docs/Titulo de Utilidade publica.pdf">Visualizar Completo</button>
            </div>
          </div>
        </div>

        <!-- Card: utilidade publica -->
        <div class="col-12 col-sm-6 col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="pdf-preview">
              <iframe data-src="/docs/utilidade publica.pdf#toolbar=0&navpanes=0&view=Fit" type="application/pdf"></iframe>
            </div>
            <div class="card-body">
              <h5 class="card-title">Utilidade Pública</h5>
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalPdf" data-pdf="/docs/utilidade publica.pdf">Visualizar Completo</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal PDF -->
      <div class="modal fade" id="modalPdf" tabindex="-1" aria-labelledby="modalPdfLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalPdfLabel">Visualização do Documento</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" style="height:80vh;">
              <iframe id="pdfFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
            </div>
          </div>
        </div>
      </div>

      <script>
        // Função para debounce
        function debounce(func, wait) {
          let timeout;
          return function executedFunction(...args) {
            const later = () => {
              clearTimeout(timeout);
              func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
          };
        }

        // Função para carregar PDFs
        function loadPDFs() {
          document.querySelectorAll('.pdf-preview iframe').forEach(iframe => {
            const pdfPath = iframe.getAttribute('data-src');
            if (pdfPath && !iframe.src) {
              // Add a cache-busting parameter to ensure the PDF reloads if necessary
              iframe.src = pdfPath + "?t=" + new Date().getTime();
            }
          });
        }

        // Função para verificar e corrigir a orientação do PDF
        const checkPDFOrientation = debounce(() => {
          const pdfPreviews = document.querySelectorAll('.pdf-preview');

          pdfPreviews.forEach(preview => {
            const iframe = preview.querySelector('iframe');
            if (!iframe || !iframe.contentWindow || !iframe.contentWindow.document || !iframe.contentWindow.document.body) return;

            try {
                // Ensure content is loaded
                if (iframe.contentWindow.document.readyState !== 'complete') {
                    iframe.onload = () => setTimeout(() => checkAndRotate(iframe, preview), 500);
                } else {
                    checkAndRotate(iframe, preview);
                }
            } catch (e) {
                console.warn('Não foi possível verificar a orientação do PDF (cross-origin?):', e);
            }
          });
        }, 250);

        function checkAndRotate(iframe, preview) {
            try {
                const width = iframe.contentWindow.document.body.scrollWidth;
                const height = iframe.contentWindow.document.body.scrollHeight;

                if (width > height) {
                    preview.classList.add('rotate-90');
                    const containerHeight = Math.min(width * (preview.offsetWidth / height), 400);
                    preview.style.height = containerHeight + 'px';
                } else {
                    preview.classList.remove('rotate-90');
                    preview.style.height = '300px';
                }
            } catch (e) {
                console.warn('Error during checkAndRotate:', e);
            }
        }

        function cleanupModalResources() {
          const frame = document.getElementById('pdfFrame');
          if (frame) {
            frame.src = 'about:blank'; // Clear the iframe src to release resources
          }
        }

        document.addEventListener('DOMContentLoaded', () => {
          loadPDFs();

          const modalPdf = document.getElementById('modalPdf');
          if (modalPdf) {
            modalPdf.addEventListener('show.bs.modal', function (event) {
              const button = event.relatedTarget;
              const pdf = button.getAttribute('data-pdf');
              const frame = document.getElementById('pdfFrame');
              if (frame) {
                frame.src = pdf;
              }
            });
            modalPdf.addEventListener('hidden.bs.modal', cleanupModalResources);
          }

          // Initial check after a short delay to allow PDFs to load
          setTimeout(checkPDFOrientation, 1000);
        });

        window.addEventListener('resize', checkPDFOrientation);
        window.addEventListener('beforeunload', cleanupModalResources);
      </script>
    </div>
  </section>
