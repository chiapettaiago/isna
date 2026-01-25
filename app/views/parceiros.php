<!-- Seção Hero -->
<section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 600px; background-size: cover; background-position: center; background-repeat: no-repeat;">
  <div class="container text-center">
    <h1 class="display-4">Parceiros</h1>
    <p class="lead">Conheça nossos parceiros e apoiadores que fazem nosso trabalho possível.</p>
  </div>
</section>

<style>
  .parceiro-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 16px;
    overflow: hidden;
  }
  
  .parceiro-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15) !important;
  }
  
  .parceiro-logo-wrapper {
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 1.5rem;
  }
  
  .parceiro-logo-wrapper img {
    max-height: 100px;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
  }
  
  .parceiro-card:hover .parceiro-logo-wrapper img {
    transform: scale(1.05);
  }
  
  .section-header {
    position: relative;
    padding-bottom: 1rem;
  }
  
  .section-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, #ffc107, #ff9800);
    border-radius: 2px;
  }
  
  .dark-theme .parceiro-logo-wrapper {
    background: linear-gradient(135deg, #2a2a2a 0%, #1e1e1e 100%);
  }
  
  .dark-theme .parceiro-card {
    background-color: #252525;
  }
  
  .dark-theme .parceiro-card .card-title,
  .dark-theme .parceiro-card .card-text {
    color: #e0e0e0;
  }
</style>

<!-- Seção Parceiros Institucionais -->
<section class="py-5 bg-light" id="parceiros-institucionais">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-header mb-4">Parceiros Institucionais</h2>
      <p class="text-muted">Organizações e empresas que apoiam nossas iniciativas</p>
    </div>
    
    <div class="row g-4 justify-content-center">
      <!-- Coca-Cola Brasil -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/coca-cola.png" alt="Coca-Cola Brasil" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Coca-Cola Brasil</h5>
            <p class="card-text small text-muted mb-0">Parceiro Corporativo</p>
          </div>
        </div>
      </div>
      
      <!-- Petrobras -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/petrobras.png" alt="Petrobras" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Petrobras</h5>
            <p class="card-text small text-muted mb-0">Parceiro Corporativo</p>
          </div>
        </div>
      </div>
      
      <!-- Google -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/google.png" alt="Google" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Google</h5>
            <p class="card-text small text-muted mb-0">Tecnologia e Inovação</p>
          </div>
        </div>
      </div>
      
      <!-- Prefeitura de Itaboraí -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/prefeitura-itaborai.png" alt="Prefeitura de Itaboraí" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Prefeitura de Itaboraí</h5>
            <p class="card-text small text-muted mb-0">Poder Público Municipal</p>
          </div>
        </div>
      </div>
      
      <!-- Governo do Estado do RJ -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/governo-rj.png" alt="Governo do Estado do RJ" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Governo do Estado do RJ</h5>
            <p class="card-text small text-muted mb-0">Poder Público Estadual</p>
          </div>
        </div>
      </div>
      
      <!-- Banco de Alimentos -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/banco-de-alimentos.png" alt="Banco de Alimentos" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Banco de Alimentos</h5>
            <p class="card-text small text-muted mb-0">Segurança Alimentar</p>
          </div>
        </div>
      </div>
      
      <!-- MLX Gases -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/mlx-gases.png" alt="MLX Gases" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">MLX Gases</h5>
            <p class="card-text small text-muted mb-0">Parceiro Local</p>
          </div>
        </div>
      </div>
      
      <!-- Agência do Bem -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/agencia-do-bem.jpg" alt="Agência do Bem" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Agência do Bem</h5>
            <p class="card-text small text-muted mb-0">Parceiro Social</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Seção Parceiros do Bairro -->
<section class="py-5" id="parceiros-bairro">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-header mb-4">Parceiros do Bairro</h2>
      <p class="text-muted">Conheça os parceiros locais que apoiam nossas iniciativas no dia a dia</p>
    </div>
    
    <div class="row g-4 justify-content-center">
      <!-- AF Fitness -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/af-fitness.png" alt="AF Fitness - Centro de Treinamento" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">AF Fitness</h5>
            <p class="card-text small text-muted mb-0">Centro de Treinamento</p>
          </div>
        </div>
      </div>
      
      <!-- Centro de Treinamento Elvis Hernandez -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/elvis-hernandez.png" alt="Centro de Treinamento Elvis Hernandez" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">CT Elvis Hernandez</h5>
            <p class="card-text small text-muted mb-0">Centro de Treinamento</p>
          </div>
        </div>
      </div>
      
      <!-- Além do Disfarce -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/alem-do-disfarce.png" alt="Além do Disfarce - Barber Shop" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Além do Disfarce</h5>
            <p class="card-text small text-muted mb-0">Barber Shop</p>
          </div>
        </div>
      </div>
      
      <!-- Mercadinho Felpão -->
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm parceiro-card">
          <div class="parceiro-logo-wrapper">
            <img src="/images/parceiros/mercadinho-felpao.png" alt="Mercadinho Felpão" class="img-fluid">
          </div>
          <div class="card-body text-center py-3">
            <h5 class="card-title h6 mb-1">Mercadinho Felpão</h5>
            <p class="card-text small text-muted mb-0">Desde 2015</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Seção CTA -->
<section class="py-5 bg-light">
  <div class="container text-center">
    <h3 class="mb-3">Quer ser nosso parceiro?</h3>
    <p class="text-muted mb-4">Entre em contato conosco e saiba como sua empresa ou organização pode contribuir com nossas iniciativas sociais.</p>
    <a href="<?php echo $site_url; ?>/contato" class="btn btn-warning btn-lg px-5">
      <i class="bi bi-envelope me-2"></i>Entre em Contato
    </a>
  </div>
</section>
