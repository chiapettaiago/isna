<style>
  :root {
    --isna-accent: #ffc107; /* amarelo do instituto (Bootstrap warning) */
    --isna-text: #fff;
    --isna-rail: rgba(255,255,255,.25);
    --isna-buffer: rgba(255,255,255,.4);
    --isna-bg: rgba(0,0,0,.6);
    --isna-bg-strong: rgba(0,0,0,.85);
  }
  /* Cards */
  .card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,.1); transition: transform .3s; }
  .card:hover { transform: translateY(-5px); }
  .card-img-top { width: 100%; height: 200px; object-fit: cover; }
  .card-title { font-size: 1.25rem; margin-bottom: 10px; }
  .card-text { font-size: 1rem; margin-bottom: 15px; }

  /* Player estilo Netflix */
  .netflix-player { position: relative; overflow: hidden; border-radius: 8px; background-color: #000; display: none; }
  /* Importante: dentro de .ratio, os filhos devem ser absolute para ocupar o espaço do player */
  .netflix-player video { position: absolute; inset: 0; z-index: 0; width: 100%; height: 100%; background:#000; object-fit: cover; }
  .video-controls-overlay { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: flex-end; pointer-events: none; z-index: 2; opacity: 1; transition: opacity .2s ease; }
  /* Barra inferior ao estilo YouTube */
  .video-controls { pointer-events: all; padding: 10px 8px 8px; background: linear-gradient(180deg, transparent, rgba(0,0,0,.2) 40%, rgba(0,0,0,.35) 80%); }

  .gradient-top, .gradient-bottom { position: absolute; left: 0; width: 100%; pointer-events: none; z-index: 1; }
  .gradient-top { top: 0; height: 25%; background: linear-gradient(180deg, rgba(0,0,0,.6), rgba(0,0,0,0)); }
  .gradient-bottom { bottom: 0; height: 35%; background: linear-gradient(0deg, rgba(0,0,0,.85), rgba(0,0,0,0)); }

  .video-info { pointer-events: all; }
  .video-controls { pointer-events: all; background: linear-gradient(0deg, rgba(0,0,0,.9), rgba(0,0,0,.4) 60%, rgba(0,0,0,0) 100%); padding: 6px 0 8px; }

  /* Seekbar */
  .seekbar { position: relative; z-index: 2; height: 6px; margin: 6px 0 10px; cursor: pointer; }
  .seek-rail { position: absolute; left: 0; right: 0; top: 0; bottom: 0; background: var(--isna-rail); border-radius: 999px; }
  .seek-buffer { position: absolute; left: 0; top: 0; bottom: 0; background: var(--isna-buffer); border-radius: 999px; width: 0%; }
  .seek-played { position: absolute; left: 0; top: 0; bottom: 0; background: var(--isna-accent); border-radius: 999px; width: 0%; }
  .seek-handle { position: absolute; top: 50%; transform: translate(-50%, -50%); width: 12px; height: 12px; background: #fff; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,.4); opacity: 0; transition: opacity .15s ease; }
  .seekbar:hover .seek-handle, .seekbar:focus-within .seek-handle { opacity: 1; }
  .seek-tooltip { position: absolute; bottom: calc(100% + 6px); transform: translateX(-50%); background: rgba(0,0,0,.95); color: #fff; font-size: 12px; padding: 4px 8px; border-radius: 4px; white-space: nowrap; display: none; border: 1px solid rgba(255,255,255,.2); }

  /* Controles inferiores */
  .controls-bar { position: relative; z-index: 3; display: flex; align-items: center; gap: 12px; color: var(--isna-text); }
  .controls-left, .controls-right { display: flex; align-items: center; gap: 10px; }
  .control-btn { background: transparent; border: none; color: var(--isna-text); padding: 6px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; }
  .control-btn i { font-size: 1.4rem; line-height: 1; text-shadow: 0 1px 3px rgba(0,0,0,.8); }
  .control-btn:hover { color: var(--isna-accent); }
  .time-label { font-size: .95rem; opacity: .95; background: rgba(0,0,0,.6); padding: 4px 8px; border-radius: 4px; color: #fff; border: 1px solid rgba(255,255,255,.2); box-shadow: 0 2px 6px rgba(0,0,0,.3); }
  .volume-container { display: inline-flex; align-items: center; gap: 6px; }
  .volume-range { width: 0; height: 4px; opacity: 0; transition: width .2s ease, opacity .2s ease; }
  .volume-container:hover .volume-range, .volume-range:focus { width: 90px; opacity: 1; }

  /* Botão de play central */
  .play-button-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: rgba(0, 0, 0, 0.7); border-radius: 50%; width: 70px; height: 70px; display: flex; justify-content: center; align-items: center; z-index: 3; cursor: pointer; transition: background-color .2s ease, transform .2s ease; border:1px solid rgba(255,255,255,.25) }
  .play-button-overlay:hover { background-color: rgba(0, 0, 0, 0.85); transform: translate(-50%, -50%) scale(1.05); }
  .play-button-overlay i { color: #fff; font-size: 28px; }

  /* Removidos: zonas de pulo e menu de configurações para estabilidade */

  /* Melhorias mobile/touch */
  @media (max-width: 576px), (pointer: coarse) {
    .card-img-top { height: 160px; }
    .seekbar { height: 8px; margin: 8px 0 12px; }
    .seek-handle { width: 16px; height: 16px; }
    .control-btn { padding: 8px; }
    .control-btn i { font-size: 1.4rem; }
    .time-label { font-size: .9rem; padding: 6px 10px; }
    .volume-container .volume-range { width: 90px; opacity: 1; }
    .play-button-overlay { width: 56px; height: 56px; }
    .play-button-overlay i { font-size: 24px; }
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
    <!-- Player de Vídeo (antes das opções de doação) -->
    <div class="row justify-content-center mb-5 ">
      <div class="col-lg-10">
        <div class="netflix-player ratio ratio-16x9" id="yt-like-player">
          <video id="donation-video" preload="auto" playsinline webkit-playsinline poster="<?php $thumb='images/donation-thumbnail.jpg'; echo asset($thumb) . '?v=' . (file_exists($thumb)?filemtime($thumb):time()); ?>" disablepictureinpicture controlslist="noplaybackrate nodownload noremoteplayback">
            <source src="<?php $vid='videos/ISNA - Doações.mp4'; echo asset($vid) . '?v=' . (file_exists($vid)?filemtime($vid):time()); ?>" type="video/mp4">
            Seu navegador não suporta vídeo HTML5.
          </video>

          <!-- Botão de play central -->
          <div class="play-button-overlay" aria-label="Reproduzir vídeo">
            <i class="bi bi-play-fill"></i>
          </div>

          <!-- Overlay com informações e controles -->
          <div class="video-controls-overlay p-3">
            <!-- Info minimal como no YouTube -->
            <div class="video-info text-white d-flex justify-content-start align-items-start px-2 pt-1" style="pointer-events: none;">
              <span class="fw-semibold small">Impacto Social ISNA</span>
            </div>
            <div class="video-controls px-2 pb-1">
              <div class="px-1 pb-2">
                <div id="seekbar" class="seekbar" aria-label="Linha do tempo do vídeo">
                  <div class="seek-rail"></div>
                  <div id="seek-buffer" class="seek-buffer"></div>
                  <div id="seek-played" class="seek-played"></div>
                  <div id="seek-handle" class="seek-handle"></div>
                  <div id="seek-tooltip" class="seek-tooltip">00:00</div>
                </div>
              </div>
              <div class="controls-bar">
                <div class="controls-left">
                  <button id="play-pause-btn" class="control-btn" type="button" aria-label="Reproduzir/Pausar">
                    <i class="bi bi-play-fill"></i>
                  </button>
                  <div class="volume-container">
                    <button id="mute-btn" class="control-btn" type="button" aria-label="Ativar/Desativar som">
                      <i class="bi bi-volume-up-fill"></i>
                    </button>
                    <input id="volume-range" class="volume-range" type="range" min="0" max="1" step="0.05" value="1" aria-label="Volume">
                  </div>
                </div>
                <div class="controls-right ms-auto">
                  <span class="time-label"><span id="current-time">00:00</span> / <span id="duration">00:00</span></span>
                  <button id="fullscreen-btn" class="control-btn" type="button" aria-label="Tela cheia">
                    <i class="bi bi-arrows-fullscreen"></i>
                  </button>
                </div>
              </div>
              <!-- Menu de configurações removido para evitar conflitos de layout -->
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <!-- Card 1: Doações Bancárias -->
      <div class="col-md-4 mb-4">
        <div class="card shadow h-100 d-flex flex-column justify-content-center align-items-center">
          <img src="<?php echo asset('images/depositos.png'); ?>" class="card-img-top" alt="Doações Bancárias">
          <div class="card-body text-center">
            <h5 class="card-title">Doações Bancárias</h5>
             <p class="card-text">
              Contribua fazendo uma transferência bancária ou Pix:
            </p>
            <a href="<?php echo url('doacoes-bancarias'); ?>" class="btn btn-primary btn-lg">Ver opções Bancárias</a>
          </div>
        </div>
      </div>
      <!-- Card 2: Doação via PayPal -->
      <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
          <img src="<?php echo asset('images/paypal.jpg'); ?>" class="card-img-top" alt="Doação via PayPal">
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
          <img src="<?php echo asset('images/international-gifts.png'); ?>" class="card-img-top" alt="Doações Internacionais">
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
      const player = document.querySelector('.netflix-player');
      const video = document.getElementById('donation-video');
      const playPauseBtn = document.getElementById('play-pause-btn');
      const muteBtn = document.getElementById('mute-btn');
      const volumeRange = document.getElementById('volume-range');
      const fullscreenBtn = document.getElementById('fullscreen-btn');
      const currentTimeLabel = document.getElementById('current-time');
      const durationLabel = document.getElementById('duration');
      const playButtonOverlay = document.querySelector('.play-button-overlay');
      const seekbar = document.getElementById('seekbar');
      const seekBuffer = document.getElementById('seek-buffer');
      const seekPlayed = document.getElementById('seek-played');
      const seekHandle = document.getElementById('seek-handle');
      const seekTooltip = document.getElementById('seek-tooltip');
  const sourceEl = video ? video.querySelector('source') : null;
  // Simplificado: sem modo teatro, sem menu de configurações, sem zonas de pulo

      let isPlaying = false;
      let isMuted = false;
      let isSeeking = false;

      function updateTimeLabels() {
        if (currentTimeLabel) currentTimeLabel.textContent = formatTime(video.currentTime || 0);
        if (durationLabel) durationLabel.textContent = formatTime(video.duration || 0);
      }

      function updateProgress() {
        if (!video.duration) return;
        const playedPct = (video.currentTime / video.duration) * 100;
        seekPlayed.style.width = playedPct + '%';
        seekHandle.style.left = playedPct + '%';
      }

      function updateBuffered() {
        if (!video.duration || video.buffered.length === 0) return;
        const end = video.buffered.end(video.buffered.length - 1);
        const bufPct = Math.min(100, (end / video.duration) * 100);
        seekBuffer.style.width = bufPct + '%';
      }

      function seekToClientX(clientX) {
        const rect = seekbar.getBoundingClientRect();
        const x = Math.max(0, Math.min(clientX - rect.left, rect.width));
        const pct = x / rect.width;
        video.currentTime = pct * (video.duration || 0);
      }

      function formatTime(seconds) {
        const s = Math.max(0, seconds || 0);
        const minutes = Math.floor(s / 60);
        const secs = Math.floor(s % 60);
        return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
      }

  // Lazy-load removido: src definido direto e preload=auto para carregar imediatamente

      if (player && video && playButtonOverlay) {
        // Estado inicial (pausado -> overlay ativo)
        player.classList.add('paused');
        updateTimeLabels();
        updateBuffered();

        playButtonOverlay.addEventListener('click', function() {
          video.play();
        });

        // Play/pause
        if (playPauseBtn) {
          playPauseBtn.addEventListener('click', function() {
            if (video.paused) {
              video.play();
            } else {
              video.pause();
            }
          });
        }
        // Clicar no vídeo também pausa/continua como no YouTube
        video.addEventListener('click', () => {
          if (settingsMenu) settingsMenu.classList.remove('active');
          video.paused ? video.play() : video.pause();
        });

        // Mute/volume
        if (muteBtn) {
          muteBtn.addEventListener('click', function() {
            video.muted = !video.muted;
            isMuted = video.muted;
            muteBtn.innerHTML = isMuted ? '<i class="bi bi-volume-mute-fill"></i>' : '<i class="bi bi-volume-up-fill"></i>';
          });
        }
        if (volumeRange) {
          volumeRange.addEventListener('input', function() {
            video.volume = parseFloat(volumeRange.value);
            if (video.volume === 0) {
              video.muted = true;
              muteBtn && (muteBtn.innerHTML = '<i class="bi bi-volume-mute-fill"></i>');
            } else {
              video.muted = false;
              muteBtn && (muteBtn.innerHTML = '<i class="bi bi-volume-up-fill"></i>');
            }
          });
        }

        // Fullscreen
        if (fullscreenBtn) {
          fullscreenBtn.addEventListener('click', function() {
            const el = player;
            if (document.fullscreenElement) {
              document.exitFullscreen && document.exitFullscreen();
            } else {
              el.requestFullscreen && el.requestFullscreen();
            }
          });
        }

  // Modo teatro removido

        // Progresso/seek
        if (seekbar) {
          seekbar.addEventListener('mousemove', function(e) {
            const rect = seekbar.getBoundingClientRect();
            const x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
            const pct = x / rect.width;
            const time = pct * (video.duration || 0);
            if (seekTooltip) {
              seekTooltip.style.display = 'block';
              seekTooltip.style.left = (pct * 100) + '%';
              seekTooltip.textContent = formatTime(time);
            }
          });
          seekbar.addEventListener('mouseleave', function() {
            if (seekTooltip) seekTooltip.style.display = 'none';
          });
          seekbar.addEventListener('mousedown', function(e) {
            isSeeking = true;
            seekToClientX(e.clientX);
          });
          document.addEventListener('mousemove', function(e) {
            if (isSeeking) seekToClientX(e.clientX);
          });
          document.addEventListener('mouseup', function() {
            isSeeking = false;
          });
          seekbar.addEventListener('click', function(e) {
            seekToClientX(e.clientX);
          });
        }

  // Menu de configurações removido

  // Zonas de pulo removidas

        // Eventos do vídeo
        video.addEventListener('play', function() {
          isPlaying = true;
          playButtonOverlay.style.display = 'none';
          playPauseBtn && (playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>');
        });
        video.addEventListener('pause', function() {
          isPlaying = false;
          playButtonOverlay.style.display = 'flex';
          playPauseBtn && (playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>');
        });
        video.addEventListener('ended', function() {
          isPlaying = false;
          playButtonOverlay.style.display = 'flex';
          playPauseBtn && (playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>');
        });
        video.addEventListener('loadedmetadata', function() {
          updateTimeLabels();
          updateBuffered();
        });
        video.addEventListener('timeupdate', function() {
          updateTimeLabels();
          updateProgress();
        });
        video.addEventListener('progress', updateBuffered);

        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
          if (!player.contains(document.activeElement)) {
            // Permite atalhos mesmo sem foco em controles
          }
          switch (e.key.toLowerCase()) {
            case ' ':
              e.preventDefault();
              video.paused ? video.play() : video.pause();
              break;
            case 'k':
              e.preventDefault();
              video.paused ? video.play() : video.pause();
              break;
            case 'j':
              video.currentTime = Math.max((video.currentTime || 0) - 10, 0);
              break;
            case 'l':
              video.currentTime = Math.min((video.currentTime || 0) + 10, video.duration || 0);
              break;
            case 'm':
              muteBtn && muteBtn.click();
              break;
            case 'f':
              fullscreenBtn && fullscreenBtn.click();
              break;
            case 'arrowright':
              video.currentTime = Math.min((video.currentTime || 0) + 5, video.duration || 0);
              break;
            case 'arrowleft':
              video.currentTime = Math.max((video.currentTime || 0) - 5, 0);
              break;
            case 'arrowup':
              video.volume = Math.min(1, (video.volume || 0) + 0.05);
              volumeRange && (volumeRange.value = String(video.volume));
              break;
            case 'arrowdown':
              video.volume = Math.max(0, (video.volume || 0) - 0.05);
              volumeRange && (volumeRange.value = String(video.volume));
              break;
          }
        });
      }
    });
  </script>
