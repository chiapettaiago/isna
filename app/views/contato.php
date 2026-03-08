<!-- Seção Hero -->
<section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 400px; background-size: cover; background-position: center; background-repeat: no-repeat;">
  <div class="container text-center">
    <h1 class="display-4">Entre em Contato</h1>
    <p class="lead">Estamos prontos para ajudar e responder suas dúvidas.</p>
  </div>
</section>

<style>
  .contact-card {
    border-radius: 16px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12) !important;
  }
  
  .contact-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1rem;
  }
  
  .contact-icon.phone {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
  }
  
  .contact-icon.email {
    background: linear-gradient(135deg, #0d6efd, #6f42c1);
    color: white;
  }
  
  .contact-icon.location {
    background: linear-gradient(135deg, #dc3545, #fd7e14);
    color: white;
  }
  
  .contact-icon.instagram {
    background: linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045);
    color: white;
  }
  
  .contact-icon.facebook {
    background: linear-gradient(135deg, #1877f2, #3b5998);
    color: white;
  }
  
  .contact-link {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
  }
  
  .contact-link:hover {
    color: #ffc107;
  }
  
  .map-container {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  }
  
  .dark-theme .contact-card {
    background-color: #252525 !important;
  }
  
  .dark-theme .contact-card h5,
  .dark-theme .contact-card p {
    color: #e0e0e0;
  }
</style>

<!-- Seção Informações de Contato -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4 justify-content-center">
      <!-- Telefone/WhatsApp -->
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm contact-card text-center p-4">
          <div class="contact-icon phone">
            <i class="bi bi-whatsapp"></i>
          </div>
          <h5 class="mb-2">WhatsApp</h5>
          <p class="text-muted mb-3">Fale conosco pelo WhatsApp</p>
          <a href="https://wa.me/5521998074784" target="_blank" class="btn btn-success">
            <i class="bi bi-whatsapp me-2"></i>Iniciar Conversa
          </a>
        </div>
      </div>
      
      <!-- E-mail -->
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 shadow-sm contact-card text-center p-4">
          <div class="contact-icon email">
            <i class="bi bi-envelope"></i>
          </div>
          <h5 class="mb-2">E-mail</h5>
          <p class="text-muted mb-3">Envie-nos uma mensagem</p>
          <a href="mailto:contato@isna.org.br" class="btn btn-primary">
            <i class="bi bi-envelope me-2"></i>Enviar E-mail
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Seção Redes Sociais -->
<section class="py-5">
  <div class="container">
    <h2 class="text-center mb-4">Siga-nos nas Redes Sociais</h2>
    <p class="text-center text-muted mb-5">Acompanhe nossas atividades e novidades</p>
    
    <div class="row g-4 justify-content-center">
      <!-- Instagram -->
      <div class="col-md-4 col-6">
        <div class="card h-100 border-0 shadow-sm contact-card text-center p-4">
          <div class="contact-icon instagram">
            <i class="bi bi-instagram"></i>
          </div>
          <h5 class="mb-2">Instagram</h5>
          <p class="text-muted mb-3">@isnasocial</p>
          <a href="https://instagram.com/isnasocial" target="_blank" class="btn btn-outline-dark">
            <i class="bi bi-instagram me-2"></i>Seguir
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
<section class="py-5 text-center">
  <div class="container">
    <h3 class="mb-3">Quer contribuir com nossa causa?</h3>
    <p class="text-muted mb-4">Sua doação ajuda a transformar vidas através da educação e capacitação profissional.</p>
    <a href="<?php echo $site_url; ?>/doe" class="btn btn-warning btn-lg px-5">
      <i class="bi bi-heart me-2"></i>Fazer uma Doação
    </a>
  </div>
</section>
