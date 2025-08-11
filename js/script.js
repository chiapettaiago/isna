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
  // ----- Pinch-to-Zoom para imagens -----
  (function enablePinchZoom() {
    // Apenas imagens dentro de modais/carrosséis
    const candidates = document.querySelectorAll('.modal .carousel-item img');
    candidates.forEach(img => {
      if (img.classList.contains('pinch-zoom-target') || img.width < 80 || /logo|icon|favicon/i.test(img.src)) return;
      // Usar o próprio .carousel-item como wrapper para não quebrar layout Bootstrap
      const carouselItem = img.closest('.carousel-item');
      if (!carouselItem) return;
      carouselItem.classList.add('pinch-zoom-wrapper');
      img.classList.add('pinch-zoom-target');
      const wrapper = carouselItem; // Mantém interface usada abaixo

      let scale = 1;
      let minScale = 1;
      let maxScale = 4;
      let startDistance = 0;
      let startScale = 1;
      let originX = 0;
      let originY = 0;
      let translateX = 0;
      let translateY = 0;
      let lastTranslateX = 0;
      let lastTranslateY = 0;
      let isPointerDown = false;
      let lastTouchEnd = 0;

      function getDistance(t1, t2) {
        const dx = t2.clientX - t1.clientX;
        const dy = t2.clientY - t1.clientY;
        return Math.hypot(dx, dy);
      }
      function clamp(v, min, max) { return Math.min(max, Math.max(min, v)); }
      function applyTransform() {
        img.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
      }
      function setOriginFromTouches(t1, t2) {
        const rect = img.getBoundingClientRect();
        originX = ( (t1.clientX + t2.clientX) / 2 ) - rect.left;
        originY = ( (t1.clientY + t2.clientY) / 2 ) - rect.top;
      }
      function onPointerDown(e) {
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        // Em carrossel, só inicia arrasto se já estiver com zoom (>1)
        if (scale === 1) return;
        isPointerDown = true;
        img.classList.add('grabbing');
      }
      function onPointerUp() {
        isPointerDown = false;
        img.classList.remove('grabbing');
        lastTranslateX = translateX;
        lastTranslateY = translateY;
      }
      function onPointerMove(e) {
  if (!isPointerDown || scale === 1) return; // só arrasta quando ampliado
        translateX = lastTranslateX + e.movementX;
        translateY = lastTranslateY + e.movementY;
        limitPan();
        applyTransform();
      }
      function limitPan() {
        const rect = wrapper.getBoundingClientRect();
        const imgW = img.naturalWidth * scale;
        const imgH = img.naturalHeight * scale;
        const maxX = Math.max(0, (imgW - rect.width) / 2);
        const maxY = Math.max(0, (imgH - rect.height) / 2);
        translateX = clamp(translateX, -maxX, maxX);
        translateY = clamp(translateY, -maxY, maxY);
      }
      function onTouchStart(e) {
        if (e.touches.length === 2) {
          e.preventDefault();
          startDistance = getDistance(e.touches[0], e.touches[1]);
          startScale = scale;
          setOriginFromTouches(e.touches[0], e.touches[1]);
          img.style.transformOrigin = `${originX}px ${originY}px`;
        } else if (e.touches.length === 1) {
          // Se com zoom, habilita pan; se sem zoom, deixa swipe do carrossel funcionar
          if (scale > 1) {
            isPointerDown = true;
          } else {
            isPointerDown = false; // não interferir no swipe do carrossel
          }
        }
      }
      function onTouchMove(e) {
        if (e.touches.length === 2) {
          e.preventDefault();
          const dist = getDistance(e.touches[0], e.touches[1]);
          let newScale = startScale * (dist / startDistance);
          newScale = clamp(newScale, minScale, maxScale);
          // ajustar translate para manter centro aparente
          const scaleRatio = newScale / scale;
          translateX *= scaleRatio;
          translateY *= scaleRatio;
          scale = newScale;
          limitPan();
          applyTransform();
          if (scale > 1) img.classList.add('zoomed-in'); else img.classList.remove('zoomed-in');
        } else if (e.touches.length === 1 && isPointerDown && scale > 1) {
          e.preventDefault();
          translateX += e.movementX || 0; // movementX não existe em touch, tratamos abaixo
          translateY += e.movementY || 0;
          // fallback calculando delta manual
          const touch = e.touches[0];
          if (!img._lastTouch) img._lastTouch = {x: touch.clientX, y: touch.clientY};
          const dx = touch.clientX - img._lastTouch.x;
          const dy = touch.clientY - img._lastTouch.y;
          translateX += dx;
          translateY += dy;
          img._lastTouch = {x: touch.clientX, y: touch.clientY};
          limitPan();
          applyTransform();
        }
      }
      function onTouchEnd(e) {
        delete img._lastTouch;
        if (scale <= 1) { scale = 1; translateX = 0; translateY = 0; applyTransform(); img.classList.remove('zoomed-in'); }
        isPointerDown = false;
        lastTranslateX = translateX;
        lastTranslateY = translateY;
        const now = Date.now();
        if (e.touches.length === 0 && now - lastTouchEnd < 280) {
          // duplo toque: alterna zoom
          if (scale === 1) { scale = 2; img.classList.add('zoomed-in'); } else { scale = 1; translateX = 0; translateY = 0; img.classList.remove('zoomed-in'); }
          applyTransform();
        }
        if (e.touches.length === 0) lastTouchEnd = now;
      }
      function onWheel(e) {
        if (!e.ctrlKey) return; // só permitir pinch-zoom simulado (trackpad) com ctrl
        e.preventDefault();
        const rect = img.getBoundingClientRect();
        const cx = e.clientX - rect.left;
        const cy = e.clientY - rect.top;
        img.style.transformOrigin = `${cx}px ${cy}px`;
        const delta = -e.deltaY;
        let newScale = scale * (delta > 0 ? 1.08 : 0.92);
        newScale = clamp(newScale, minScale, maxScale);
        const ratio = newScale / scale;
        translateX *= ratio;
        translateY *= ratio;
        scale = newScale;
        limitPan();
        if (scale > 1) img.classList.add('zoomed-in'); else { img.classList.remove('zoomed-in'); translateX = 0; translateY = 0; }
        applyTransform();
      }

      // Eventos
      img.addEventListener('pointerdown', onPointerDown);
      window.addEventListener('pointerup', onPointerUp);
      window.addEventListener('pointermove', onPointerMove);
      img.addEventListener('touchstart', onTouchStart, { passive: false });
      img.addEventListener('touchmove', onTouchMove, { passive: false });
      img.addEventListener('touchend', onTouchEnd, { passive: true });
      img.addEventListener('wheel', onWheel, { passive: false });

      // Reset em duplo clique desktop
      img.addEventListener('dblclick', e => {
        e.preventDefault();
        if (scale === 1) { scale = 2; img.classList.add('zoomed-in'); }
        else { scale = 1; translateX = 0; translateY = 0; img.classList.remove('zoomed-in'); }
        applyTransform();
      });
    });
  })();

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

// Funções para otimização de exibição de PDFs
// ------------------------------------------

// Função para verificar se estamos na página de títulos e documentos
function isPdfPage() {
  return window.location.pathname.includes('titulos-documentos');
}

// Pré-carregar o worker de PDF.js quando estiver na página de documentos
document.addEventListener('DOMContentLoaded', function() {
  if (isPdfPage()) {
    // Pré-carregar os scripts PDF.js necessários
    const preloadPdfJs = document.createElement('link');
    preloadPdfJs.rel = 'preload';
    preloadPdfJs.as = 'script';
    preloadPdfJs.href = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
    document.head.appendChild(preloadPdfJs);
    
    const preloadPdfJsWorker = document.createElement('link');
    preloadPdfJsWorker.rel = 'preload';
    preloadPdfJsWorker.as = 'script';
    preloadPdfJsWorker.href = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    document.head.appendChild(preloadPdfJsWorker);
  }
});

// Otimização para detectar dispositivos móveis e ajustar a qualidade do PDF de acordo
function isMobileDevice() {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Definir configurações de qualidade de PDF com base no dispositivo
window.pdfQualitySettings = {
  // Qualidades mais baixas para dispositivos móveis para melhor desempenho
  mobile: {
    canvasContextType: '2d',
    enableWebGL: false,
    useOnlyCssZoom: true,
    maxCanvasPixels: 5242880, // 2.5 megapixels
    disableFontFace: true,
    disableRange: false,
    disableStream: false,
    disableAutoFetch: false
  },
  // Qualidades mais altas para desktop
  desktop: {
    canvasContextType: '2d',
    enableWebGL: true,
    useOnlyCssZoom: false,
    maxCanvasPixels: 16777216, // 16 megapixels 
    disableFontFace: false,
    disableRange: false,
    disableStream: false,
    disableAutoFetch: false
  }
};

// Auxiliar para detectar conexão lenta 
function detectSlowConnection() {
  const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  if (connection) {
    const type = connection.effectiveType || connection.type;
    // Considerar conexão lenta se for 3G ou menos
    return ['slow-2g', '2g', '3g'].includes(type);
  }
  return false; // Se não conseguir detectar, assumir que não é lenta
}

// Sistema de detecção e aviso de atualização (git pull)
document.addEventListener('DOMContentLoaded', function() {
  // Função para verificar se o sistema está sendo atualizado
  function checkForSystemUpdate() {
    fetch('/check-update-status.php', {
      method: 'GET',
      cache: 'no-store',
      headers: {
        'Cache-Control': 'no-cache'
      }
    })
    .then(response => response.json())
    .then(data => {
      // Se há uma atualização em andamento
      if (data.updating) {
        showUpdateAlert(data.progress, data.message);
        
        // Continua verificando até a atualização terminar
        setTimeout(checkForSystemUpdate, 2000);
      } else {
        hideUpdateAlert();
      }
    })
    .catch(error => {
      // Se o servidor estiver incontactável, assume que está atualizando
      if (!document.getElementById('system-update-alert')) {
        showUpdateAlert(30, "Conectando ao servidor...");
        setTimeout(checkForSystemUpdate, 3000);
      }
    });
  }

  // Função para mostrar o alerta de atualização
  function showUpdateAlert(progress, message) {
    let alertElement = document.getElementById('system-update-alert');
    
    // Se o alerta ainda não existe, cria ele
    if (!alertElement) {
      alertElement = document.createElement('div');
      alertElement.id = 'system-update-alert';
      alertElement.className = 'update-alert';
      
      alertElement.innerHTML = `
        <img src="/images/logo.png" alt="Logo do Instituto" class="site-logo-update" style="height: 80px; margin-bottom: 20px;">
        <h2>Atualizando o Sistema</h2>
        <p>O site está sendo atualizado para a versão mais recente. Por favor, aguarde alguns instantes.</p>
        <div class="update-progress">
          <div class="update-progress-bar" id="update-progress-bar"></div>
        </div>
        <div class="update-status" id="update-status-message"></div>
      `;
      
      document.body.appendChild(alertElement);
    }
    
    // Atualiza a barra de progresso
    const progressBar = document.getElementById('update-progress-bar');
    if (progressBar && typeof progress === 'number') {
      progressBar.style.width = progress + '%';
    }
    
    // Atualiza a mensagem de status
    const statusMsg = document.getElementById('update-status-message');
    if (statusMsg && message) {
      statusMsg.textContent = message;
    }
  }
  
  // Função para esconder o alerta
  function hideUpdateAlert() {
    const alertElement = document.getElementById('system-update-alert');
    if (alertElement) {
      alertElement.style.animation = 'fadeOut 0.5s ease forwards';
      setTimeout(() => {
        if (alertElement.parentNode) {
          alertElement.parentNode.removeChild(alertElement);
        }
      }, 500);
    }
  }

  // Verifica na carga da página e a cada 30 segundos
  checkForSystemUpdate();
  setInterval(checkForSystemUpdate, 30000);
});
