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
      <p class="mb-0">&copy; <?php echo date("Y"); ?> Instituto Social Novo Amanhecer. Desenvolvido por <a href="https://chiapettadev.site" class="text-white text-decoration-none">Iago Chiapetta</a></p>
    </div>
  </div>
</footer>

  <script>
    // JavaScript para ajustar a opacidade da navbar ao rolar a página
    window.addEventListener("scroll", function() {
      const nav = document.getElementById("mainNav");
      if (window.scrollY > 50) {
        nav.classList.add("opacity-75");
      } else {
        nav.classList.remove("opacity-75");
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

  <!-- Script do WhatsApp -->
  <script>
    var url = 'https://cdn.waplus.io/waplus-crm/settings/ossembed.js';
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = url;
    var options = {
    "enabled": true,
    "chatButtonSetting": {
    "backgroundColor": "#16BE45",
    "ctaText": "Fale Conosco",
    "borderRadius": "8",
    "marginLeft": "20",
    "marginBottom": "20",
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

  <!-- Bootstrap JS (inclui Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Script customizado -->
  <script src="<?php echo $site_url; ?>/js/script.js"></script>
</body>
</html>
