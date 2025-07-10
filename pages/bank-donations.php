<?php
// Conteúdo de Doações Bancárias incluído via roteador (index.php)
?>
<style>
    /* Estilização adicional para os cards (mesma do doe.php) */
    .card {
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card-img-top {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    .card-title {
      font-size: 1.25rem;
      margin-bottom: 10px;
    }
    .card-text, .list-unstyled {
      font-size: 1rem;
      margin-bottom: 15px;
    }
</style>
<!-- Seção Hero -->
<section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 600px; background-size: cover; background-position: center; background-repeat: no-repeat;">
  <div class="container text-center">
    <h1 class="display-4">Doações Bancárias</h1>
    <p class="lead">Saiba como apoiar nosso instituto por meio de doações bancárias</p>
  </div>
</section>
<div class="container mt-5">
  <h1 class="mb-4 text-center">Doações Bancárias</h1>
  <p class="mb-5 text-center">Você pode contribuir por meio de depósito ou transferência Pix utilizando os dados abaixo:</p>
  <div class="row justify-content-center">
    <!-- Caixa Econômica Federal -->
    <div class="col-md-4 mb-4">
      <div class="card shadow h-100">
        <img src="<?php echo asset('images/caixa.png'); ?>" class="card-img-top" alt="Caixa Econômica Federal">
        <div class="card-body text-center">
          <h5 class="card-title">Caixa Econômica Federal</h5>
          <ul class="list-unstyled">
            <li><strong>Agência:</strong> 0769</li>
            <li><strong>Operação:</strong> 003</li>
            <li><strong>Conta Corrente:</strong> 54213-6</li>
            <li><strong>Pix:</strong> 08.912.758/0001-08</li>
          </ul>
        </div>
      </div>
    </div>
    <!-- Bradesco -->
    <div class="col-md-4 mb-4">
      <div class="card shadow h-100">
        <img src="<?php echo asset('images/bradesco.png'); ?>" class="card-img-top" alt="Bradesco">
        <div class="card-body text-center">
          <h5 class="card-title">Bradesco</h5>
          <ul class="list-unstyled">
            <li><strong>Agência:</strong> 2055</li>
            <li><strong>Conta Corrente:</strong> 39362-2</li>
            <li><strong>Pix:</strong> projetos@isna.org.br</li>
          </ul>
        </div>
      </div>
    </div>
    <!-- Santander -->
    <div class="col-md-4 mb-4">
      <div class="card shadow h-100">
        <img src="<?php echo asset('images/santander.jpg'); ?>" class="card-img-top" alt="Santander">
        <div class="card-body text-center">
          <h5 class="card-title">Santander</h5>
          <ul class="list-unstyled">
            <li><strong>Agência:</strong> 3402</li>
            <li><strong>Conta Corrente:</strong> 13005369-2</li>
            <li><strong>Pix:</strong> isnaimpactosocial@gmail.com</li>
          </ul>
        </div>
      </div>
    </div>
    <!-- Banco do Brasil -->
    <div class="col-md-4 mb-4">
      <div class="card shadow h-100">
        <img src="<?php echo asset('images/banco-do-brasil.png'); ?>" class="card-img-top" alt="Banco do Brasil">
        <div class="card-body text-center">
          <h5 class="card-title">Banco do Brasil</h5>
          <ul class="list-unstyled">
            <li><strong>Agência:</strong> 850-8</li>
            <li><strong>Conta Corrente:</strong> 70350-8</li>
            <li><strong>Pix:</strong> contato@isna.org.br</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
