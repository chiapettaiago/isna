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
    .pdf-preview iframe, .pdf-preview canvas {
      width: 100%;
      height: 100%;
      border: none;
      position: absolute;
      top: 0;
      left: 0;
      transition: transform 0.3s ease-out;
    }
    .pdf-preview.rotate-90 {
      height: 400px;
    }
    .pdf-preview.rotate-90 iframe, .pdf-preview.rotate-90 canvas {
      transform: rotate(90deg);
      transform-origin: 0 0;
      width: 100vh;
      height: 100vw;
      position: absolute;
      top: 0;
      left: 100%;
    }
    .pdf-preview.rotate-180 canvas {
      transform: rotate(180deg);
      transform-origin: center center;
    }
    .pdf-preview.rotate-270 {
      height: 400px; 
    }
    .pdf-preview.rotate-270 canvas {
      transform: rotate(-90deg);
      transform-origin: center center;
      width: 100vh;
      height: 100vw;
      position: absolute;
      top: 50%;
      left: -50%;
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
    
    /* Estilos para controles do PDF */
    .pdf-controls {
      position: absolute;
      bottom: 10px;
      right: 10px;
      z-index: 10;
      background-color: rgba(255, 255, 255, 0.7);
      border-radius: 4px;
      padding: 5px;
      display: flex;
      gap: 5px;
    }
    .pdf-controls button {
      padding: 2px 5px;
      font-size: 12px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 3px;
      cursor: pointer;
    }
    .pdf-controls button:hover {
      background-color: #0056b3;
    }
    .pdf-controls button.rotate-doc {
      font-size: 14px;
      padding: 1px 5px;
    }
    
    /* Estilo para transição suave da rotação */
    .pdf-preview canvas, #pdf-canvas {
      transition: transform 0.3s ease-out;
    }
    
    /* Loader para os PDFs */
    .pdf-loader {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }
    .pdf-loader .spinner-border {
      width: 3rem;
      height: 3rem;
    }
</style>

<!-- PDF.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
  // Configura o worker para PDF.js
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
</script>

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

      <div class="row pdf-grid row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
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

      <!-- Modal PDF Melhorado com PDF.js -->
      <div class="modal fade" id="modalPdf" tabindex="-1" aria-labelledby="modalPdfLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalPdfLabel">Visualização do Documento</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" style="height:80vh;">
              <!-- O conteúdo será preenchido dinamicamente pelo JavaScript -->
            </div>
          </div>
        </div>
      </div>
      
      <!-- Versão para impressão -->
      <div id="print-container" style="display: none;"></div>

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

        // Cache para documentos PDF já carregados
        const pdfCache = new Map();
        
        // Função para carregar PDFs com PDF.js
        function loadPDFs() {
          document.querySelectorAll('.pdf-preview').forEach(preview => {
            const iframe = preview.querySelector('iframe');
            if (!iframe) return;
            
            // Substituir o iframe por um canvas para o PDF.js
            const pdfPath = iframe.getAttribute('data-src')?.split('#')[0];
            if (!pdfPath) return;
            
            // Remover parâmetros da URL
            const cleanPdfPath = pdfPath.split('?')[0];
            
            // Adicionar um loader
            iframe.style.display = 'none';
            const loader = document.createElement('div');
            loader.className = 'pdf-loader';
            loader.innerHTML = `
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
              </div>
              <p class="mt-2">Carregando documento...</p>
            `;
            preview.appendChild(loader);
            
            // Criar canvas para o PDF
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-canvas';
            preview.appendChild(canvas);
            
            // Adicionar controles de zoom e rotação
            const controls = document.createElement('div');
            controls.className = 'pdf-controls';
            controls.innerHTML = `
              <button class="zoom-in" title="Aumentar zoom">+</button>
              <button class="zoom-out" title="Diminuir zoom">-</button>
              <button class="reset-zoom" title="Resetar zoom">↺</button>
              <button class="rotate-doc" title="Girar documento">⟳</button>
            `;
            preview.appendChild(controls);
            
            // Registrar o canvas e controles
            preview.dataset.canvas = true;
            
            // Iniciar carregamento do PDF
            renderPDF(cleanPdfPath, canvas, preview, loader);
          });
        }
        
        // Renderizar o PDF usando PDF.js
        async function renderPDF(pdfPath, canvas, container, loader) {
          try {
            // Verificar cache
            let pdfDoc = pdfCache.get(pdfPath);
            
            if (!pdfDoc) {
              // Carregar o documento PDF
              pdfDoc = await pdfjsLib.getDocument({
                url: pdfPath,
                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                cMapPacked: true
              }).promise;
              
              // Salvar no cache
              pdfCache.set(pdfPath, pdfDoc);
            }
            
            // Obter a primeira página
            const page = await pdfDoc.getPage(1);

            // Obter metadados do PDF para verificar a orientação correta
            const metadata = await pdfDoc.getMetadata().catch(e => null);
            
            // Determinar a escala para ajustar ao container
            const containerWidth = container.clientWidth;
            const viewport = page.getViewport({ scale: 1 });
            
            // Analisar a orientação do PDF
            let rotation = 0;
            let isPortrait = viewport.width <= viewport.height;
            let isLandscape = viewport.width > viewport.height;

            // Verificar se o PDF possui indicação de orientação preferida
            if (metadata && metadata.info && metadata.info.Orientation) {
              // Alguns PDFs têm informação explícita de orientação
              rotation = parseInt(metadata.info.Orientation) || 0;
            } else if (page.rotate !== 0) {
              // O PDF já tem rotação definida internamente
              rotation = page.rotate;
            } else {
              // Análise baseada em texto e conteúdo para detectar se está invertido
              try {
                // Obter o conteúdo da página para análise
                const textContent = await page.getTextContent();
                if (textContent.items && textContent.items.length > 0) {
                  // Contar quantos itens de texto estão "de cabeça para baixo" (180 graus)
                  let upsideDownCount = 0;
                  let normalCount = 0;
                  
                  textContent.items.forEach(item => {
                    const transform = item.transform || [1, 0, 0, 1, 0, 0];
                    // Verifica a transformação da matriz para determinar orientação
                    if (transform[0] < 0 && transform[3] < 0) {
                      upsideDownCount++; // Provavelmente rotacionado 180°
                    } else {
                      normalCount++;
                    }
                  });
                  
                  // Se mais de 60% do texto parece estar invertido
                  if (textContent.items.length > 5 && upsideDownCount > (normalCount * 0.6)) {
                    rotation = 180;
                  }
                }
              } catch (e) {
                console.warn("Não foi possível analisar o conteúdo do texto para orientação:", e);
              }
            }
            
            // Aplicar a rotação detectada
            let scaledViewport;
            if (rotation !== 0) {
              // Criar viewport com a rotação corrigida
              scaledViewport = page.getViewport({ scale: 1, rotation: rotation });
              // Recalcular proporções com a rotação aplicada
              isPortrait = scaledViewport.width <= scaledViewport.height;
              isLandscape = scaledViewport.width > scaledViewport.height;
            }
            
            // Calcular escala final após determinar a orientação correta
            const scale = containerWidth / (scaledViewport ? scaledViewport.width : viewport.width);
            scaledViewport = page.getViewport({ scale, rotation });
            
            // Configurar o canvas
            const context = canvas.getContext('2d');
            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            
            // Limpar classes de rotação existentes
            container.classList.remove('landscape', 'rotate-90', 'rotate-180', 'rotate-270');
            
            // Ajustar altura do container e aplicar rotação conforme necessário
            if (isLandscape) {
              container.classList.add('landscape');
              container.style.height = Math.min(scaledViewport.height, 400) + 'px';
            } else {
              container.style.height = '300px';
            }
            
            // Aplicar classe de rotação CSS adicional se necessário
            // (para casos onde a rotação do PDF.js não é suficiente)
            if (rotation === 90 || rotation === -270) {
              container.classList.add('rotate-90');
            } else if (rotation === 180 || rotation === -180) {
              container.classList.add('rotate-180');
            } else if (rotation === 270 || rotation === -90) {
              container.classList.add('rotate-270');
            }
            
            // Renderizar PDF no canvas com a rotação aplicada
            const renderContext = {
              canvasContext: context,
              viewport: scaledViewport
            };
            
            await page.render(renderContext).promise;
            
            // Armazenar escala original
            container.dataset.originalScale = scale;
            container.dataset.currentScale = scale;
            container.dataset.pdfPage = 1;
            container.dataset.pdfPath = pdfPath;
            
            // Configurar eventos de zoom
            setupZoomEvents(container);
            
            // Remover o loader
            if (loader) {
              loader.remove();
            }
          } catch (error) {
            console.error("Erro ao carregar o PDF:", error);
            if (loader) {
              loader.innerHTML = '<p>Erro ao carregar o documento. Tente novamente.</p>';
            }
          }
        }
        
        // Configurar eventos de zoom e rotação para os controles
        function setupZoomEvents(container) {
          const zoomIn = container.querySelector('.zoom-in');
          const zoomOut = container.querySelector('.zoom-out');
          const resetZoom = container.querySelector('.reset-zoom');
          const rotateDoc = container.querySelector('.rotate-doc');
          const canvas = container.querySelector('canvas');
          
          if (!canvas) return;
          
          zoomIn.addEventListener('click', () => changeZoom(container, 0.2));
          zoomOut.addEventListener('click', () => changeZoom(container, -0.2));
          resetZoom.addEventListener('click', () => resetZoomLevel(container));
          
          // Adicionar evento de rotação manual
          if (rotateDoc) {
            rotateDoc.addEventListener('click', () => rotateDocument(container));
          }
        }
        
        // Função para rotação manual do documento
        async function rotateDocument(container) {
          if (!container) return;
          
          // Obter a rotação atual ou definir como 0 se não existir
          let currentRotation = parseInt(container.dataset.rotation || '0');
          
          // Incremetar a rotação em 90 graus
          currentRotation = (currentRotation + 90) % 360;
          
          // Salvar a nova rotação
          container.dataset.rotation = currentRotation;
          
          // Remover classes de rotação existentes
          container.classList.remove('rotate-90', 'rotate-180', 'rotate-270');
          
          // Aplicar a classe apropriada para a rotação
          if (currentRotation === 90) {
            container.classList.add('rotate-90');
          } else if (currentRotation === 180) {
            container.classList.add('rotate-180');
          } else if (currentRotation === 270) {
            container.classList.add('rotate-270');
          }
          
          // Re-renderizar o documento com a nova rotação
          const canvas = container.querySelector('canvas');
          const pdfPath = container.dataset.pdfPath;
          
          if (canvas && pdfPath) {
            try {
              const pdfDoc = pdfCache.get(pdfPath);
              if (!pdfDoc) return;
              
              const pageNum = parseInt(container.dataset.pdfPage) || 1;
              const page = await pdfDoc.getPage(pageNum);
              
              // Obter a escala atual
              const currentScale = parseFloat(container.dataset.currentScale || '1');
              
              // Criar viewport com a rotação 
              const viewport = page.getViewport({ 
                scale: currentScale,
                rotation: currentRotation
              });
              
              // Ajustar as dimensões do canvas
              const context = canvas.getContext('2d');
              
              // Ajustar o canvas e o container
              if (currentRotation === 90 || currentRotation === 270) {
                // Em rotação lateral, inverter dimensões
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                container.style.height = Math.min(viewport.height, 400) + 'px';
              } else {
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                container.style.height = (currentRotation === 180) ? '300px' : Math.min(viewport.height, 300) + 'px';
              }
              
              // Renderizar o PDF com a rotação aplicada
              const renderContext = {
                canvasContext: context,
                viewport: viewport
              };
              
              await page.render(renderContext).promise;
            } catch (e) {
              console.error("Erro ao rodar o documento:", e);
            }
          }
        }
        
        // Alterar o zoom do PDF
        async function changeZoom(container, delta) {
          const canvas = container.querySelector('canvas');
          if (!canvas) return;
          
          const originalScale = parseFloat(container.dataset.originalScale) || 1;
          const currentScale = parseFloat(container.dataset.currentScale) || 1;
          const newScale = Math.max(0.5, Math.min(3, currentScale + delta));
          
          if (newScale === currentScale) return;
          
          container.dataset.currentScale = newScale;
          
          // Recarregar o PDF com a nova escala
          const pdfPath = container.dataset.pdfPath;
          const pageNum = parseInt(container.dataset.pdfPage) || 1;
          
          try {
            const pdfDoc = pdfCache.get(pdfPath);
            if (!pdfDoc) return;
            
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: newScale });
            
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            const renderContext = {
              canvasContext: context,
              viewport: viewport
            };
            
            await page.render(renderContext).promise;
          } catch (error) {
            console.error("Erro ao aplicar zoom:", error);
          }
        }
        
        // Resetar o nível de zoom
        function resetZoomLevel(container) {
          const originalScale = parseFloat(container.dataset.originalScale) || 1;
          container.dataset.currentScale = originalScale;
          
          // Recarregar com a escala original
          changeZoom(container, 0);
        }

        // Melhorar o visualizador modal para usar PDF.js também
        function setupModalViewer() {
          const modalPdf = document.getElementById('modalPdf');
          if (!modalPdf) return;
          
          modalPdf.addEventListener('show.bs.modal', async function (event) {
            const button = event.relatedTarget;
            const pdfPath = button.getAttribute('data-pdf');
            const modalTitle = modalPdf.querySelector('.modal-title');
            const modalBody = modalPdf.querySelector('.modal-body');
            
            if (!pdfPath) return;
            
            // Atualizar o título
            if (modalTitle) {
              modalTitle.textContent = button.closest('.card-body').querySelector('.card-title').textContent;
            }
            
            // Preparar o corpo do modal
            modalBody.innerHTML = `
              <div class="position-relative h-100">
                <div class="pdf-loader position-absolute top-50 start-50 translate-middle">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                  </div>
                  <p class="mt-2">Carregando documento...</p>
                </div>
                <div class="d-flex flex-column h-100">
                  <div class="pdf-modal-controls bg-light p-2 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                      <button id="prev-page" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="bi bi-chevron-left"></i> Anterior
                      </button>
                      <span id="page-info" class="mx-2">Página: <span id="page-num">1</span> / <span id="page-count">?</span></span>
                      <button id="next-page" class="btn btn-sm btn-outline-secondary ms-2">
                        Próxima <i class="bi bi-chevron-right"></i>
                      </button>
                    </div>
                    <div class="zoom-controls">
                      <button id="zoom-out" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-zoom-out"></i>
                      </button>
                      <button id="zoom-in" class="btn btn-sm btn-outline-secondary mx-1">
                        <i class="bi bi-zoom-in"></i>
                      </button>
                      <button id="zoom-reset" class="btn btn-sm btn-outline-secondary me-1">
                        <i class="bi bi-arrows-angle-contract"></i>
                      </button>
                      <button id="rotate-doc-modal" class="btn btn-sm btn-outline-secondary" title="Girar documento">
                        <i class="bi bi-arrow-clockwise"></i>
                      </button>
                    </div>
                  </div>
                  <div class="pdf-container flex-grow-1 overflow-auto position-relative">
                    <canvas id="pdf-canvas" class="d-block mx-auto my-3"></canvas>
                  </div>
                </div>
              </div>
            `;
            
            // Após o modal ser exibido completamente, carregar o PDF
            modalPdf.addEventListener('shown.bs.modal', async function onceShown() {
              modalPdf.removeEventListener('shown.bs.modal', onceShown);
              
              // Inicializar o visualizador de PDF no modal
              initModalPdfViewer(pdfPath);
            }, { once: true });
          });
          
          // Limpar recursos ao fechar o modal
          modalPdf.addEventListener('hidden.bs.modal', function() {
            // Limpar recursos
            const modalBody = modalPdf.querySelector('.modal-body');
            if (modalBody) {
              modalBody.innerHTML = ''; // Limpar o conteúdo do modal
            }
          });
        }
        
        // Inicializar o visualizador de PDF no modal
        async function initModalPdfViewer(pdfPath) {
          const modalBody = document.querySelector('#modalPdf .modal-body');
          if (!modalBody) return;
          
          const canvas = document.getElementById('pdf-canvas');
          const pageNum = document.getElementById('page-num');
          const pageCount = document.getElementById('page-count');
          const prevButton = document.getElementById('prev-page');
          const nextButton = document.getElementById('next-page');
          const zoomIn = document.getElementById('zoom-in');
          const zoomOut = document.getElementById('zoom-out');
          const zoomReset = document.getElementById('zoom-reset');
          const loader = modalBody.querySelector('.pdf-loader');
          
          // Variáveis para controle do visualizador
          let pdfDoc = null;
          let currentPage = 1;
          let currentScale = 1.0;
          let originalScale = 1.0;
          let currentRotation = 0; // Inicializar rotação como 0
          
          try {
            // Verificar cache
            pdfDoc = pdfCache.get(pdfPath);
            
            if (!pdfDoc) {
              // Carregar o documento PDF
              pdfDoc = await pdfjsLib.getDocument({
                url: pdfPath,
                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                cMapPacked: true
              }).promise;
              
              // Salvar no cache
              pdfCache.set(pdfPath, pdfDoc);
            }
            
            // Atualizar a contagem de páginas
            pageCount.textContent = pdfDoc.numPages;
            
            // Função para renderizar uma página
            async function renderPage(num) {
              // Validar número da página
              if (num < 1 || num > pdfDoc.numPages) return;
              
              currentPage = num;
              pageNum.textContent = currentPage;
              
              // Ativar/desativar botões de navegação
              prevButton.disabled = currentPage === 1;
              nextButton.disabled = currentPage === pdfDoc.numPages;
              
              // Mostrar loader durante renderização
              if (loader) loader.style.display = 'block';
              
              try {
                // Obter a página
                const page = await pdfDoc.getPage(num);
                
                // Calcular escala para ajustar à largura
                const container = canvas.parentElement;
                const containerWidth = container.clientWidth - 40; // -40px para margem
                
                const viewport = page.getViewport({ scale: 1 });
                const scale = containerWidth / viewport.width;
                
                // Salvar escala original se ainda não definida
                if (!originalScale) {
                  originalScale = scale;
                }
                
                // Aplicar escala atual e rotação (se houver)
                const scaledViewport = page.getViewport({ 
                  scale: scale * currentScale,
                  rotation: currentRotation // Usar a rotação atual
                });
                
                // Configurar o canvas
                const context = canvas.getContext('2d');
                canvas.height = scaledViewport.height;
                canvas.width = scaledViewport.width;
                
                // Renderizar PDF no canvas
                const renderContext = {
                  canvasContext: context,
                  viewport: scaledViewport
                };
                
                await page.render(renderContext).promise;
                
                // Esconder loader após renderização
                if (loader) loader.style.display = 'none';
              } catch (error) {
                console.error("Erro ao renderizar página:", error);
                if (loader) {
                  loader.innerHTML = '<p>Erro ao carregar a página. Tente novamente.</p>';
                }
              }
            }
            
            // Renderizar a primeira página
            renderPage(1);
            
            // Eventos de navegação
            prevButton.addEventListener('click', () => {
              if (currentPage > 1) {
                renderPage(currentPage - 1);
              }
            });
            
            nextButton.addEventListener('click', () => {
              if (currentPage < pdfDoc.numPages) {
                renderPage(currentPage + 1);
              }
            });
            
            // Eventos de zoom
            zoomIn.addEventListener('click', () => {
              currentScale = Math.min(3, currentScale + 0.2);
              renderPage(currentPage);
            });
            
            zoomOut.addEventListener('click', () => {
              currentScale = Math.max(0.5, currentScale - 0.2);
              renderPage(currentPage);
            });
            
            zoomReset.addEventListener('click', () => {
              currentScale = 1;
              renderPage(currentPage);
            });
            
            // Evento de rotação para o botão no modal
            rotateDocModal.addEventListener('click', () => {
              // Incrementar a rotação em 90 graus (0 -> 90 -> 180 -> 270 -> 0)
              currentRotation = (currentRotation + 90) % 360;
              renderPage(currentPage);
            });
            
            // Navegação por teclado
            document.addEventListener('keydown', (e) => {
              // Apenas se o modal estiver aberto
              if (!document.getElementById('modalPdf').classList.contains('show')) return;
              
              if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                if (currentPage > 1) {
                  renderPage(currentPage - 1);
                }
              } else if (e.key === 'ArrowRight' || e.key === 'PageDown') {
                if (currentPage < pdfDoc.numPages) {
                  renderPage(currentPage + 1);
                }
              } else if (e.key === 'r' || e.key === 'R') {
                // Tecla R para rotação
                currentRotation = (currentRotation + 90) % 360;
                renderPage(currentPage);
              } else if (e.key === '+' || e.key === '=') {
                // Zoom in com tecla + ou =
                currentScale = Math.min(3, currentScale + 0.2);
                renderPage(currentPage);
              } else if (e.key === '-' || e.key === '_') {
                // Zoom out com tecla - ou _
                currentScale = Math.max(0.5, currentScale - 0.2);
                renderPage(currentPage);
              } else if (e.key === '0') {
                // Reset zoom com tecla 0
                currentScale = 1;
                renderPage(currentPage);
              }
            });
            
          } catch (error) {
            console.error("Erro ao inicializar o visualizador:", error);
            if (loader) {
              loader.innerHTML = '<p>Erro ao carregar o documento. Tente novamente.</p>';
            }
          }
        }

        document.addEventListener('DOMContentLoaded', () => {
          // Carregar os PDFs com qualidade melhorada
          loadPDFs();
          
          // Configurar o visualizador no modal
          setupModalViewer();
        });

        window.addEventListener('resize', debounce(() => {
          // Reajustar os PDFs ao redimensionar a janela
          document.querySelectorAll('.pdf-preview[data-canvas="true"]').forEach(preview => {
            const pdfPath = preview.dataset.pdfPath;
            const canvas = preview.querySelector('canvas');
            const loader = preview.querySelector('.pdf-loader');
            
            if (pdfPath && canvas) {
              // Redefinir a escala ao tamanho original
              preview.dataset.currentScale = preview.dataset.originalScale || 1;
              
              // Renderizar novamente com o tamanho atualizado
              renderPDF(pdfPath, canvas, preview, loader);
            }
          });
        }, 250));
      </script>
    </div>
  </section>
