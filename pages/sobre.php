<!-- Hero -->
  <section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 600px; background-size: cover; background-position: center; min-height: 220px;">
    <div class="container text-center">
      <h1 class="display-4">Sobre o Site</h1>
      <p class="lead">Conheça os recursos, funcionalidades e a proposta do nosso portal</p>
    </div>
  </section>

  <section class="py-5 bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="card sobre-card shadow-lg border-0 mb-4 bg-white bg-opacity-75">
            <div class="card-body p-5">
              <h2 class="h4 mb-4 text-secondary text-center"><i class="bi bi-stars me-2"></i>Recursos do site</h2>
              <ul class="fs-5 mb-4 list-unstyled">
                <li class="text-center py-2 border-bottom"><i class="bi bi-phone me-2 text-primary"></i>Design responsivo e moderno com Bootstrap 5</li>
                <li class="text-center py-2 border-bottom"><i class="bi bi-images me-2 text-primary"></i>Galeria de fotos com visualização em carrossel e modal</li>
                <li class="text-center py-2 border-bottom"><i class="bi bi-cash-coin me-2 text-primary"></i>Página de doações com múltiplas opções (banco, PayPal, internacional)</li>
                <li class="text-center py-2 border-bottom"><i class="bi bi-shield-check me-2 text-info"></i>Seção de transparência e documentos</li>
                <li class="text-center py-2 border-bottom"><i class="bi bi-people me-2 text-warning"></i>Seção de parceiros e projetos</li>
                <li class="text-center py-2 border-bottom"><i class="bi bi-whatsapp me-2 text-success"></i>Botão de contato via WhatsApp integrado</li>
                <li class="text-center py-2 border-bottom"><i class="bi bi-geo-alt me-2 text-danger"></i>Mapa de localização integrado via Google Maps</li>
                <li class="text-center py-2"><i class="bi bi-lightning-charge me-2 text-primary"></i>Carregamento otimizado de imagens (lazy loading)</li>
              </ul>
              <div class="alert alert-primary d-flex align-items-center justify-content-center" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                <div>
                  <strong>Versão do site:</strong> 1.3<br>
                  <strong>Data de lançamento:</strong> Julho de 2025
                </div>
              </div>
              <p class="mt-4 fs-5 text-center">Este site foi desenvolvido para facilitar o acesso às informações do Instituto Social Novo Amanhecer, promover a transparência e incentivar a participação da comunidade em nossos projetos sociais.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <style>
    /* Timeline styles */
    .timeline { position: relative; margin: 2rem 0; padding: 0; }
    .timeline::before { content: ''; position: absolute; top: 0; bottom: 0; left: 50%; width: 4px; background: #ddd; }
    .timeline-item { position: relative; width: 50%; padding: 1rem 2rem; box-sizing: border-box; }
    .timeline-item:nth-child(odd) { left: 0; text-align: right; }
    .timeline-item:nth-child(even) { left: 50%; }
    .timeline-item::before { content: ''; position: absolute; top: 1.5rem; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 4px solid #0d6efd; }
    .timeline-item:nth-child(odd)::before { right: -12px; }
    .timeline-item:nth-child(even)::before { left: -12px; }

    /* Responsividade para dispositivos móveis */
    @media (max-width: 767px) {
      .timeline::before {
        left: 8px;
      }
      .timeline-item {
        width: 100%;
        left: 0 !important;
        text-align: left !important;
        padding: 1rem 1rem 1rem 2.5rem;
      }
      .timeline-item::before {
        top: 1rem;
        left: 0;
        right: auto;
      }
    }
  </style>

  <section class="py-5 bg-white">
    <div class="container">
      <div class="timeline">
        <!-- Card da versão 1.3 -->
        <div class="timeline-item">
          <div class="card sobre-card shadow border-0 mb-4 bg-light bg-opacity-75">
            <div class="card-body p-5">
              <h2 class="h4 mb-4 text-primary text-center"><i class="bi bi-bank me-2"></i>Melhorias da versão 1.3 (atual)</h2>
              <div class="alert alert-primary d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                <div>Versão lançada em Julho de 2025</div>
              </div>
              <ul class="fs-5 mb-0 list-unstyled">
                <li class="text-center py-2"><i class="bi bi-cash-stack me-2 text-success"></i>Opção de Doações Bancárias adicionada na página de Doação</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="timeline-item">
          <!-- Card da versão 1.2 (atual) -->
          <div class="card sobre-card shadow border-0 mb-4 bg-light bg-opacity-75">
            <div class="card-body p-5">
              <h2 class="h4 mb-4 text-success text-center"><i class="bi bi-arrow-up-circle me-2"></i>Melhorias da versão 1.2</h2>
              <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                <div>Versão lançada em Junho de 2025</div>
              </div>
              <ul class="fs-5 mb-0 list-unstyled">
                <li class="text-center py-2"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Sistema aprimorado de exibição de PDFs com visualização integrada e navegação facilitada</li>
                <li class="text-center py-2"><i class="bi bi-instagram me-2 text-danger"></i>Botão do Instagram no footer</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="timeline-item">
          <!-- Card da versão 1.1 -->
          <div class="card sobre-card shadow border-0 mb-4 bg-light bg-opacity-75">
            <div class="card-body p-5">
              <h2 class="h4 mb-4 text-info text-center"><i class="bi bi-clock-history me-2"></i>Melhorias da versão 1.1</h2>
              <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                <div>Versão lançada em Março de 2025</div>
              </div>
              <ul class="fs-5 mb-0 list-unstyled">
                <li class="text-center py-2 border-bottom"><i class="bi bi-images me-2 text-primary"></i>O modal de imagens agora abre e fecha corretamente, proporcionando melhor experiência ao usuário</li>
                <li class="text-center py-2 border-bottom"><i class="bi bi-diagram-3 me-2 text-secondary"></i>Roteamento avançado para URLs amigáveis e navegação dinâmica</li>
                <li class="text-center py-2 border-bottom"><i class="bi bi-shield-lock me-2 text-info"></i>Melhorias de segurança (validação de entrada, proteção contra ataques comuns)</li>
                <li class="text-center py-2"><i class="bi bi-speedometer2 me-2 text-warning"></i>Otimizações de desempenho para carregamento mais rápido</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
