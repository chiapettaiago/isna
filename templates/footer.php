<footer class="bg-dark text-white pt-5 pb-3">
  <div class="container">
    <div class="row">
      <!-- Sobre o Instituto -->
      <div class="col-md-4 mb-4">
        <h5>Sobre o Instituto</h5>
        <p>
          É uma organização de sociedade civil de interesse público, sem fins lucrativos, que busca através de seus projetos sociais qualificar e requalificar pessoas para a inclusão no mercado de trabalho.
        </p>
        <div class="social-media mt-3">
          <a href="https://www.instagram.com/isnasocial/" target="_blank" class="btn btn-outline-light me-2" title="Siga-nos no Instagram">
            <i class="bi bi-instagram"></i> Instagram
          </a>
        </div>
      </div>
      <!-- Localização -->
      <div class="col-md-4 mb-4">
        <h5>Localização</h5>
        <div class="ratio ratio-4x3">
          <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14715.341816010288!2d-42.939398!3d-22.771487!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9bf99b5d774f2d%3A0x4dd354624b51654f!2sInstituto%20Social%20Um%20Novo%20Amanhecer!5e0!3m2!1spt-BR!2sus!4v1745316903372!5m2!1spt-BR!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </div>
    <hr class="border-light">
    <div class="text-center">
      <p class="mb-0">&copy; <?php echo date("Y"); ?> Instituto Social Novo Amanhecer. Desenvolvido por <a href="https://chiapetta.dev" class="text-white text-decoration-none">Iago Chiapetta</a></p>
    </div>
  </div>
</footer>

  <script>
    // JavaScript para ajustar a opacidade da navbar ao rolar a página
    window.addEventListener("scroll", function() {
      const nav = document.getElementById("mainNav");
      const isMobile = window.innerWidth <= 991.98;
      
      if (isMobile) {
        // Efeito para mobile - navbar se afasta do fundo
        if (window.scrollY > 50) {
          nav.classList.add("scrolled");
          document.body.classList.add("navbar-scrolled");
        } else {
          nav.classList.remove("scrolled");
          document.body.classList.remove("navbar-scrolled");
        }
      } else {
        // Efeito para desktop - opacidade
        if (window.scrollY > 50) {
          nav.classList.add("opacity-75");
        } else {
          nav.classList.remove("opacity-75");
        }
      }
    });

    // Força um reload total (Ctrl+F5) ao abrir a página
    // window.addEventListener('DOMContentLoaded', function() {
    //   if (!window.location.hash.includes('noreload')) {
    //     window.location.hash = 'noreload';
    //     window.location.reload(true);
    //   }
    // });
    // Commented out the force reload script as it might cause issues in a PHP setup.
  </script>

  <?php if (empty($currentUser)): ?>
  <!-- Script do WhatsApp -->
  <script>
    var url = 'https://cdn.waplus.io/waplus-crm/settings/ossembed.js';
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = url;
    
    // Configurações diferentes para mobile e desktop
    var isMobile = window.innerWidth <= 991.98;
    
    var options = {
    "enabled": true,
    "chatButtonSetting": {
    "backgroundColor": "#25D366",
    "ctaText": isMobile ? "" : "Fale Conosco",
    "borderRadius": "50",
    "marginLeft": "20",
    "marginBottom": isMobile ? "90" : "20",
    "marginRight": "20",
    "position": "left",
    "textColor": "#ffffff",
    "phoneNumber": "55219 98074784",
    "messageText": "Olá, gostaria de saber mais sobre o instituto",
    "trackClick": true
    }
    }
    s.onload = function() {
    CreateWhatsappBtn(options);
    };
    var x = document.getElementsByTagName('script')[0];
    x.parentNode.insertBefore(s, x);
  </script>
  <?php endif; ?>

  <!-- Bootstrap JS (inclui Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Plyr JS (Player de vídeo com UI moderna) -->
  <script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.min.js"></script>

  <!-- Inicialização do Plyr apenas na seção Realizações -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var mqMobile = window.matchMedia('(max-width: 767.98px)');
      var videos = Array.prototype.slice.call(document.querySelectorAll('#realizacoes video.realizacoes-video'));

      function applyVideoSources(isMobileView) {
        videos.forEach(function (el) {
          var srcDesktop = el.dataset.srcDesktop || el.getAttribute('src');
          var srcMobile = el.dataset.srcMobile || srcDesktop;
          var posterDesktop = el.dataset.posterDesktop || el.getAttribute('poster') || '';
          var posterMobile = el.dataset.posterMobile || posterDesktop;
          var targetSrc = isMobileView ? srcMobile : srcDesktop;
          var targetPoster = isMobileView ? posterMobile : posterDesktop;
          var sourceChanged = false;

          if (targetSrc && el.getAttribute('src') !== targetSrc) {
            el.setAttribute('src', targetSrc);
            sourceChanged = true;
          }
          if (targetPoster && el.getAttribute('poster') !== targetPoster) {
            el.setAttribute('poster', targetPoster);
          }

          if (isMobileView && srcMobile && srcMobile !== srcDesktop && !el.dataset.mobileFallbackBound) {
            var fallbackSrc = srcDesktop;
            var fallbackPoster = posterDesktop;
            var handleVideoError = function () {
              el.removeEventListener('error', handleVideoError);
              el.setAttribute('src', fallbackSrc);
              if (fallbackPoster) {
                el.setAttribute('poster', fallbackPoster);
              }
              el.load();
            };
            el.addEventListener('error', handleVideoError, { once: true });
            el.dataset.mobileFallbackBound = '1';
          }

          if (sourceChanged) {
            el.load();
          }
        });
      }

      applyVideoSources(mqMobile.matches);

      if (typeof mqMobile.addEventListener === 'function') {
        mqMobile.addEventListener('change', function (event) {
          applyVideoSources(event.matches);
        });
      } else if (typeof mqMobile.addListener === 'function') {
        mqMobile.addListener(function (event) {
          applyVideoSources(event.matches);
        });
      }

      if (window.Plyr) {
        var options = {
          // Mantém a UI semelhante ao YouTube
          controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
          settings: ['speed'],
          speed: { options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
          loadSprite: true, // carrega os ícones do CDN
          fullscreen: { enabled: true, fallback: true, iosNative: true }
        };

        videos.forEach(function (el) {
          try {
            var player = new Plyr(el, options);
            
            // Detectar quando o vídeo entrar em fullscreen em mobile
            player.on('play', function() {
              // Verifica se é mobile
              if (window.innerWidth <= 767.98) {
                // Pequeno delay para garantir que o play foi processado
                setTimeout(function() {
                  // Entra em fullscreen automaticamente
                  if (player.fullscreen && !player.fullscreen.active) {
                    player.fullscreen.enter();
                  }
                }, 100);
              }
            });
            
            // Ocultar navbar e botões quando entrar em fullscreen
            player.on('enterfullscreen', function() {
              // Oculta navbar
              var navbar = document.querySelector('.navbar');
              if (navbar) {
                navbar.style.display = 'none';
              }
              
              // Oculta botão de tema
              var themeToggle = document.querySelector('.theme-toggle');
              if (themeToggle) {
                themeToggle.style.display = 'none';
              }
              
              // Oculta botão do WhatsApp
              var whatsappSelectors = [
                '[id*="waplus"]',
                '[class*="waplus"]',
                '[id*="whatsapp"]',
                '[class*="whatsapp"]'
              ];
              
              whatsappSelectors.forEach(function(selector) {
                var elements = document.querySelectorAll(selector);
                elements.forEach(function(elem) {
                  elem.style.display = 'none';
                  elem.setAttribute('data-hidden-by-video', 'true');
                });
              });
            });
            
            // Mostrar navbar e botões quando sair do fullscreen
            player.on('exitfullscreen', function() {
              // Mostra navbar
              var navbar = document.querySelector('.navbar');
              if (navbar) {
                navbar.style.display = '';
              }
              
              // Mostra botão de tema
              var themeToggle = document.querySelector('.theme-toggle');
              if (themeToggle) {
                themeToggle.style.display = '';
              }
              
              // Mostra botão do WhatsApp
              var hiddenElements = document.querySelectorAll('[data-hidden-by-video="true"]');
              hiddenElements.forEach(function(elem) {
                elem.style.display = '';
                elem.removeAttribute('data-hidden-by-video');
              });
            });
            
          } catch (e) {
            /* segue com nativo se falhar */
          }
        });
      }
    });
  </script>

  <!-- Script customizado -->
  <script src="<?php echo $site_url; ?>/js/script.js"></script>
</body>
</html>
