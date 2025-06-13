// Validação do formulário de newsletter
document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('email').value;
    if (email) {
        alert('Obrigado por se inscrever, ' + email + '!');
        this.reset();
    }
});

// Toggle do menu em dispositivos móveis (se existir)
const navMenu = document.querySelector('.nav-menu');
if (navMenu) {
    navMenu.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
}

// JavaScript para ajustar a opacidade da navbar ao rolar a página
window.addEventListener("scroll", function() {
  const nav = document.getElementById("mainNav");
  if (nav) {
    if (window.scrollY > 50) {
      nav.classList.add("opacity-75");
    } else {
      nav.classList.remove("opacity-75");
    }
  }
});

// Inicialização do carrossel e funções de galeria
document.addEventListener('DOMContentLoaded', () => {
  // Função para atualizar o contador de imagens
  function atualizarContador(carouselElement, activeIndex) {
    const totalItems = carouselElement.querySelectorAll('.carousel-item').length;
    const contador = carouselElement.closest('.modal-content').querySelector('.contador-imagens');
    if (contador) {
      contador.textContent = `${activeIndex + 1}/${totalItems}`;
    }
  }

  // Inicializa todos os carrosséis da página
  const carousels = document.querySelectorAll('.carousel');
  carousels.forEach(carousel => {
    // Cria instância do carrossel Bootstrap
    const carouselInstance = new bootstrap.Carousel(carousel, { 
      interval: false, 
      ride: false,
      touch: true
    });

    // Evento para atualizar contador quando o slide muda
    carousel.addEventListener('slid.bs.carousel', (e) => {
      const activeIndex = Array.from(carousel.querySelectorAll('.carousel-item'))
        .findIndex(item => item.classList.contains('active'));
      atualizarContador(carousel, activeIndex);
    });

    // Inicializa o contador para o slide ativo
    const activeIndex = Array.from(carousel.querySelectorAll('.carousel-item'))
      .findIndex(item => item.classList.contains('active'));
    atualizarContador(carousel, activeIndex);
  });

  // Configura eventos de clique nas imagens da galeria da página inicial
  document.querySelectorAll('.galeria-home-img').forEach((img) => {
    img.addEventListener('click', () => {
      const target = img.getAttribute('data-bs-target');
      const index = parseInt(img.getAttribute('data-index'), 10);
      const modalEl = document.querySelector(target);
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        
        const carouselId = modalEl.querySelector('.carousel').id;
        const carouselInstance = bootstrap.Carousel.getInstance(document.getElementById(carouselId));
        if (carouselInstance) {
          carouselInstance.to(index);
        }
      }
    });
  });

  // Configura eventos de clique nas imagens da galeria principal
  document.querySelectorAll('.galeria-img').forEach(img => {
    img.addEventListener('click', () => {
      const target = img.getAttribute('data-bs-target');
      const index = parseInt(img.getAttribute('data-index'), 10);
      const modalEl = document.querySelector(target);
      if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        
        const carouselId = target === '#imageModal1' ? 'carousel1' : 'carousel2';
        const carouselInstance = bootstrap.Carousel.getInstance(document.getElementById(carouselId));
        if (carouselInstance) {
          carouselInstance.to(index);
        }
      }
    });
  });

  // Adiciona navegação por teclado nos modais de galeria
  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('keydown', function(e) {
      if (modal.classList.contains('show')) {
        const carousel = modal.querySelector('.carousel');
        const carouselInstance = bootstrap.Carousel.getInstance(carousel);
        
        if (e.key === 'ArrowLeft') {
          carouselInstance?.prev();
          e.preventDefault();
        } else if (e.key === 'ArrowRight') {
          carouselInstance?.next();
          e.preventDefault();
        } else if (e.key === 'Escape') {
          bootstrap.Modal.getInstance(modal)?.hide();
        }
      }
    });
  });

  // Adiciona suporte a gestos de toque para dispositivos móveis
  document.querySelectorAll('.carousel').forEach(carousel => {
    let touchStartX = 0;
    let touchEndX = 0;
    
    carousel.addEventListener('touchstart', e => {
      touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    carousel.addEventListener('touchend', e => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe(carousel);
    }, false);
    
    function handleSwipe(element) {
      const carouselInstance = bootstrap.Carousel.getInstance(element);
      if (touchEndX < touchStartX - 50) {
        // Deslizou para a esquerda - próximo slide
        carouselInstance?.next();
      } else if (touchEndX > touchStartX + 50) {
        // Deslizou para a direita - slide anterior
        carouselInstance?.prev();
      }
    }
  });
});

// Script para criar e controlar o botão de alternar tema (claro/escuro)
document.addEventListener('DOMContentLoaded', function() {
  // Criar o botão de alternar tema
  const themeToggleBtn = document.createElement('button');
  themeToggleBtn.className = 'theme-toggle';
  themeToggleBtn.setAttribute('aria-label', 'Alternar tema claro/escuro');
  themeToggleBtn.setAttribute('title', 'Alternar tema claro/escuro');
  
  // Verificar se há uma preferência salva no localStorage
  const isDarkTheme = localStorage.getItem('dark-theme') === 'true';
  
  // Definir o ícone inicial com base na preferência
  themeToggleBtn.innerHTML = isDarkTheme 
    ? '<i class="bi bi-sun-fill"></i>' 
    : '<i class="bi bi-moon-fill"></i>';
  
  // Aplicar tema escuro se estiver salvo no localStorage
  if (isDarkTheme) {
    document.body.classList.add('dark-theme');
  }
  
  // Adicionar evento de clique para alternar tema
  themeToggleBtn.addEventListener('click', function() {
    document.body.classList.toggle('dark-theme');
    
    // Verificar se o tema está ativado após o toggle
    const isDarkMode = document.body.classList.contains('dark-theme');
    
    // Atualizar o ícone com base no tema atual
    this.innerHTML = isDarkMode 
      ? '<i class="bi bi-sun-fill"></i>' 
      : '<i class="bi bi-moon-fill"></i>';
    
    // Salvar preferência no localStorage
    localStorage.setItem('dark-theme', isDarkMode);
  });
  
  // Adicionar o botão ao corpo da página
  document.body.appendChild(themeToggleBtn);
});
