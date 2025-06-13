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
    /* Limita a imagem ampliada para não ultrapassar o viewport no desktop */
    .modal-body img {
      max-height: 90vh;   /* até 90% da altura da janela */
      max-width: 100%;    /* não ultrapassa a largura */
      width: auto;        /* mantém proporção */
      display: block;
      margin: 0 auto;     /* centraliza */
    }
    .hover-card {
      transition: transform 0.3s ease-in-out;
    }
    .hover-card:hover {
      transform: translateY(-10px);
    }
</style>
   <!-- Seção Hero -->
   <section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 600px;  background-size: cover; /* ou 'contain' ou valores específicos */
   background-position: center; /* Centraliza a imagem no elemento */
   background-repeat: no-repeat; /* Evita a repetição da imagem */">
     <div class="container text-center">
       <h1 class="display-4">Transparência</h1>
       <p class="lead">Conheça nossas práticas de transparência e prestação de contas.</p>
     </div>
   </section>

  <!-- Seção de Transparência -->
  <section class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <h2 class="text-center mb-4">Nossa Compromisso com a Transparência</h2>
          <p class="lead text-center mb-5">
            O ISNA acredita que a transparência é fundamental para construir confiança e demonstrar nosso compromisso com a sociedade. 
            Aqui você encontrará informações sobre nossos projetos, recursos e resultados.
          </p>
        </div>
      </div>

      <!-- Cards de Informações -->
      <div class="row g-4 mb-5">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Prestação de Contas</h5>
              <p class="card-text">Acesse nossos relatórios financeiros e prestações de contas anuais, demonstrando como utilizamos os recursos recebidos.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Estatuto e Documentos</h5>
              <p class="card-text">Conheça nossos documentos institucionais, incluindo estatuto, regimento interno e políticas organizacionais.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Resultados e Impacto</h5>
              <p class="card-text">Veja os resultados alcançados por nossos projetos e o impacto social gerado em nossas comunidades.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Seção de Projetos Realizados -->
      <section class="py-5">
        <div class="container">
          <h2 class="text-center mb-4">Projetos Realizados</h2>
          <p class="lead text-center mb-5">
            Conheça alguns dos projetos que desenvolvemos ao longo de nossa história, 
            demonstrando nosso compromisso com a transformação social e o desenvolvimento comunitário.
          </p>
          <div class="row g-4">
            <!-- Cartão Jiu-Jitsu -->
            <div class="col-md-4">
              <div class="card h-100 shadow hover-card">
                <img src="/images/projetos-realizados/1.jpg" class="card-img-top galeria-img" alt="Projeto Jiu-Jitsu" style="height: 200px; object-fit: cover; cursor: pointer;" data-bs-target="#imageModal3" data-index="0">
                <div class="card-body">
                  <h5 class="card-title">Jiu-Jitsu</h5>
                  <p class="card-text">Projeto que promove a prática do Jiu-Jitsu como ferramenta de desenvolvimento pessoal e social.</p>
                </div>
              </div>
            </div>
            <!-- Cartão Notas Musicais -->
            <div class="col-md-4">
              <div class="card h-100 shadow hover-card">
                <a href="https://www.instagram.com/s/aGlnaGxpZ2h0OjE4MDcyMDk5MzA3MDExNDY1/?igshid=19q1pxh7arujv&story_media_id=2059748744879228230_9448633282#" target="_blank" class="text-decoration-none">
                  <img src="/images/projetos-realizados/2.jpg" class="card-img-top galeria-img" alt="Projeto Notas Musicais" style="height: 200px; object-fit: cover; cursor: pointer;" data-bs-target="#imageModal3" data-index="1">
                  <div class="card-body">
                    <h5 class="card-title">Notas Musicais</h5>
                    <p class="card-text">Iniciativa que utiliza a música como instrumento de transformação social e desenvolvimento cultural.</p>
                  </div>
                </a>
              </div>
            </div>
            <!-- Cartão Nosso Natal -->
            <div class="col-md-4">
              <div class="card h-100 shadow hover-card">
                <img src="/images/projetos-realizados/3.jpg" class="card-img-top galeria-img" alt="Projeto Nosso Natal" style="height: 200px; object-fit: cover; cursor: pointer;" data-bs-target="#imageModal3" data-index="2">
                <div class="card-body">
                  <h5 class="card-title">Nosso Natal</h5>
                  <p class="card-text">Projeto que leva alegria e esperança através de ações solidárias durante o período natalino.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </section>

  <!-- Modal & Carousel Seção 3 -->
  <div class="modal fade" id="imageModal3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-dark bg-opacity-75 border-0 rounded">
        <div class="modal-header border-0 p-2">
          <span class="text-white contador-imagens me-auto ms-2">1/3</span>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body p-0">
          <div id="carousel3" class="carousel slide carousel-fade" data-bs-ride="false" data-bs-touch="true">
            <div class="carousel-inner">
              <!-- Slides do carrossel -->
              <div class="carousel-item active">
                <img src="/images/projetos-realizados/1.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Projetos Realizados 1">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Projetos Realizados 1</h5>
                  <p>Descrição da imagem 1</p>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projetos-realizados/2.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Projetos Realizados 2">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Projetos Realizados 2</h5>
                  <p>Descrição da imagem 2</p>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/projetos-realizados/3.jpg" class="d-block mx-auto img-fluid carousel-img" alt="Projetos Realizados 3">
                <div class="carousel-caption bg-dark bg-opacity-50 rounded px-3 py-2 d-block">
                  <h5>Projetos Realizados 3</h5>
                  <p>Descrição da imagem 3</p>
                </div>
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel3" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
              <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel3" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
              <span class="visually-hidden">Próximo</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
      // Inicializa o carrossel explicitamente
      const carousel3 = document.getElementById('carousel3');
      if (carousel3) {
        new bootstrap.Carousel(carousel3, { interval: false, ride: false, touch: true });
      }
      
      // Configura os cliques nas imagens
      document.querySelectorAll('.galeria-img').forEach(function(img) {
        img.addEventListener('click', function() {
          const target = img.getAttribute('data-bs-target');
          const index = parseInt(img.getAttribute('data-index'), 10);
          const modalEl = document.querySelector(target);
          
          if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            setTimeout(function() {
              const carouselEl = document.getElementById('carousel3'); // Ensure this ID matches your carousel
              if (carouselEl) {
                const carouselInstance = bootstrap.Carousel.getInstance(carouselEl) || 
                                        new bootstrap.Carousel(carouselEl);
                carouselInstance.to(index);
              }
            }, 150); // Delay to ensure modal is shown before sliding
          }
        });
      });
      
      // Atualiza o contador de imagens
      const modalEl = document.getElementById('imageModal3'); // Ensure this ID matches your modal
      if (modalEl) {
        const contador = modalEl.querySelector('.contador-imagens');
        const carousel = document.getElementById('carousel3'); // Ensure this ID matches your carousel
        
        if (contador && carousel) {
          const totalItems = carousel.querySelectorAll('.carousel-item').length;
          contador.textContent = `1/${totalItems}`; // Initialize counter
          
          carousel.addEventListener('slid.bs.carousel', function() {
            const activeIndex = Array.from(carousel.querySelectorAll('.carousel-item'))
              .findIndex(item => item.classList.contains('active'));
            contador.textContent = `${activeIndex + 1}/${totalItems}`;
          });
        }
      }
    }
  });
</script>
