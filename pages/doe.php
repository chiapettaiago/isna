<style>
    /* Estilização adicional para os cards */
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
    .card-text {
      font-size: 1rem;
      margin-bottom: 15px;
    }

    /* Estilos para o player de vídeo estilo Netflix */
    .netflix-player {
      position: relative;
      overflow: hidden;
      border-radius: 8px;
      background-color: #000; /* Fundo preto para a área do player */
    }
    .netflix-player::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5); /* Camada escura sobreposta à thumbnail */
      z-index: 1;
      pointer-events: none;
    }
    .netflix-player video {
      position: relative;
      z-index: 0;
    }
    .video-controls-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      pointer-events: none;
      z-index: 2; /* Acima da camada escura */
    }
    .video-info {
      pointer-events: all;
    }
    .video-controls {
      pointer-events: all;
    }
    .progress {
      height: 4px;
      cursor: pointer;
    }

    /* Estilo adicional para o botão de play centralizado na thumbnail */
    .play-button-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: rgba(255, 0, 0, 0.7);
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 3;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .play-button-overlay:hover {
      background-color: rgba(255, 0, 0, 0.9);
    }
    .play-button-overlay i {
      color: white;
      font-size: 24px;
    }
</style>
    <!-- Seção Hero -->
    <section class="hero bg-image text-white d-flex align-items-center" style="background-image: url('/images/imagem.jpg'); height: 600px;  background-size: cover; /* ou 'contain' ou valores específicos */
    background-position: center; /* Centraliza a imagem no elemento */
    background-repeat: no-repeat; /* Evita a repetição da imagem */">
      <div class="container text-center">
        <h1 class="display-4">Doações</h1>
        <p class="lead">Saiba como apoiar nosso instituto por meio de doações</p>
      </div>
    </section>

  <!-- Conteúdo Principal -->
  <div class="container mt-5">
    <h1 class="mb-4 text-center">Faça sua Doação</h1>
    <p class="mb-5 text-center">
      Sua contribuição é essencial para continuarmos com nossos projetos sociais e ajudarmos mais pessoas.
      Selecione uma das opções abaixo para realizar sua doação de maneira segura e prática.
    </p>
    <div class="row justify-content-center">
      <!-- Card 1: Doações Bancárias -->
      <div class="col-md-4 mb-4">
        <div class="card shadow h-100 d-flex flex-column justify-content-center align-items-center">
          <img src="<?php echo asset('images/caixa.png'); ?>" class="card-img-top" alt="Doações Bancárias">
          <div class="card-body text-center">
            <h5 class="card-title">Doações Bancárias</h5>
            <a href="<?php echo url('doacoes-bancarias'); ?>" class="btn btn-primary btn-lg">Ver opções Bancárias</a>
          </div>
        </div>
      </div>
      <!-- Card 2: Doação via PayPal -->
      <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
          <img src="/images/paypal.jpg" class="card-img-top" alt="Doação via PayPal">
          <div class="card-body">
            <h5 class="card-title">Doação via PayPal</h5>
            <p class="card-text">
              Contribua utilizando o PayPal, uma opção rápida e segura para doações online.
            </p>
            <div class="text-center">
              <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
                <input type="hidden" name="cmd" value="_donations">
                <input type="hidden" name="business" value="isnaimpactosocial@gmail.com">
                <input type="hidden" name="lc" value="BR">
                <input type="hidden" name="item_name" value="Instituto Social Novo Amanhecer">
                <input type="hidden" name="item_number" value="Doação">
                <input type="hidden" name="currency_code" value="BRL">
                <input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted">
                <input type="image" src="https://www.paypalobjects.com/pt_BR/BR/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - A maneira fácil e segura de enviar pagamentos online!">
                <img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Card 3: Doações Internacionais -->
      <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
          <img src="/images/international-gifts.png" class="card-img-top" alt="Doações Internacionais">
          <div class="card-body">
            <h5 class="card-title">Doações Internacionais</h5>
            <p class="card-text">
              Para doações internacionais, utilize nossa plataforma segura que aceita diversas moedas e métodos de pagamento.
            </p>
            <div class="text-center mt-4">
              <a href="https://donatehub.chiapettadev.site/donate" target="_blank" class="btn btn-primary btn-lg">Doar Agora</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    // JavaScript para o funcionamento do player de vídeo estilo Netflix
    document.addEventListener('DOMContentLoaded', function() {
      const video = document.getElementById('donation-video');
      const playPauseBtn = document.getElementById('play-pause-btn');
      const muteBtn = document.getElementById('mute-btn');
      const fullscreenBtn = document.getElementById('fullscreen-btn');
      const currentTimeLabel = document.getElementById('current-time');
      const durationLabel = document.getElementById('duration');
      // const volumeControl = document.getElementById('volume-control'); // Not used in current HTML
      // const videoProgress = document.getElementById('video-progress'); // Not used in current HTML
      const playButtonOverlay = document.querySelector('.play-button-overlay');

      let isPlaying = false;
      let isMuted = false;

      if (video && playButtonOverlay) { // Check if video and overlay exist
        playButtonOverlay.addEventListener('click', function() {
          video.play();
          if(playPauseBtn) playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
          isPlaying = true;
          playButtonOverlay.style.display = 'none';
        });

        video.addEventListener('pause', function() {
          playButtonOverlay.style.display = 'flex';
        });

        video.addEventListener('ended', function() {
          playButtonOverlay.style.display = 'flex';
          if(playPauseBtn) playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
          isPlaying = false;
        });

        video.addEventListener('loadedmetadata', function() {
          if(durationLabel) durationLabel.textContent = formatTime(video.duration);
        });

        video.addEventListener('timeupdate', function() {
          if(currentTimeLabel) currentTimeLabel.textContent = formatTime(video.currentTime);
          // if(videoProgress) videoProgress.style.width = (video.currentTime / video.duration) * 100 + '%'; // Uncomment if progress bar is added
        });
      }


      if(playPauseBtn){
        playPauseBtn.addEventListener('click', function() {
          if (isPlaying) {
            video.pause();
            playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            if(playButtonOverlay) playButtonOverlay.style.display = 'flex';
          } else {
            video.play();
            playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
            if(playButtonOverlay) playButtonOverlay.style.display = 'none';
          }
          isPlaying = !isPlaying;
        });
      }

      if(muteBtn && video){
        muteBtn.addEventListener('click', function() {
          if (isMuted) {
            video.muted = false;
            muteBtn.innerHTML = '<i class="bi bi-volume-up-fill"></i>';
          } else {
            video.muted = true;
            muteBtn.innerHTML = '<i class="bi bi-volume-mute-fill"></i>';
          }
          isMuted = !isMuted;
        });
      }

      if(fullscreenBtn && video){
        fullscreenBtn.addEventListener('click', function() {
          if (video.requestFullscreen) {
            video.requestFullscreen();
          } else if (video.webkitRequestFullscreen) { // Safari
            video.webkitRequestFullscreen();
          } else if (video.msRequestFullscreen) { // IE11
            video.msRequestFullscreen();
          }
        });
      }

      function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
      }
    });
  </script>
