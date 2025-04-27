// Validação do formulário de newsletter
document.getElementById('newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('email').value;
    if (email) {
        alert('Obrigado por se inscrever, ' + email + '!');
        this.reset();
    }
});

// Toggle do menu em dispositivos móveis (adicione um botão de menu no HTML se desejar)
const navMenu = document.querySelector('.nav-menu');
navMenu.addEventListener('click', () => {
    navMenu.classList.toggle('active');
});

// JavaScript para ajustar a opacidade da navbar ao rolar a página
window.addEventListener("scroll", function() {
  const nav = document.getElementById("mainNav");
  if (window.scrollY > 50) {
    nav.classList.add("opacity-75");
  } else {
    nav.classList.remove("opacity-75");
  }
});

new bootstrap.Carousel(document.getElementById('carousel3'), { interval: false, ride: false });

document.addEventListener('DOMContentLoaded', () => {
  // Inicializa o carrossel da galeria home
  new bootstrap.Carousel(document.getElementById('carouselGaleriaHome'), { interval: false, ride: false });

  // Configura os cliques nas imagens da galeria
  document.querySelectorAll('.galeria-home-img').forEach((img) => {
    img.addEventListener('click', () => {
      const target = img.getAttribute('data-bs-target');
      const index = parseInt(img.getAttribute('data-index'), 10);
      const modalEl = document.querySelector(target);
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
      bootstrap.Carousel.getInstance(document.getElementById('carouselGaleriaHome')).to(index);
    });
  });
});


