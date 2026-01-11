// Detecta scripts/carregamentos bloqueados e fornece fallbacks/placeholder
(function () {
  'use strict';

  // Tenta executar um callback quando um objeto global existir
  function waitForGlobal(name, timeout = 3000) {
    return new Promise(function (resolve) {
      if (window[name] !== undefined) return resolve(true);
      var waited = 0;
      var interval = 150;
      var id = setInterval(function () {
        waited += interval;
        if (window[name] !== undefined) {
          clearInterval(id);
          return resolve(true);
        }
        if (waited >= timeout) {
          clearInterval(id);
          return resolve(false);
        }
      }, interval);
    });
  }

  // Mostra placeholder com mensagem informativa
  function showPlaceholder(selector, message) {
    var el = document.querySelector(selector);
    if (!el) return;
    var ph = document.createElement('div');
    ph.className = 'alert alert-secondary';
    ph.textContent = message;
    // Limpa conteúdo e insere placeholder
    el.innerHTML = '';
    el.appendChild(ph);
  }

  // Detectar Google Maps
  document.addEventListener('DOMContentLoaded', function () {
    // Se houver um container #mapContainer, tentamos esperar por google.maps
    var mapContainer = document.getElementById('mapContainer');
    if (mapContainer) {
      waitForGlobal('google', 2500).then(function (ok) {
        if (!ok || !window.google || !window.google.maps) {
          // mostra placeholder com ação de carregar manualmente
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-outline-primary';
          btn.textContent = 'Carregar mapa (bloqueado por extensão?)';
          btn.addEventListener('click', function () {
            // dispara evento para quem quiser carregar o script
            window.dispatchEvent(new CustomEvent('loadGoogleMapsRequested'));
          });
          mapContainer.innerHTML = '';
          var wrap = document.createElement('div');
          wrap.className = 'd-flex align-items-center gap-2';
          wrap.appendChild(document.createTextNode('Mapa indisponível ou bloqueado.'));
          wrap.appendChild(btn);
          mapContainer.appendChild(wrap);
        }
      });
    }

    // Detectar Chart (caso continue sendo bloqueado)
    var chartCanvas = document.getElementById('accessChart');
    if (chartCanvas) {
      waitForGlobal('Chart', 2500).then(function (ok) {
        if (!ok) {
          showPlaceholder('#accessChart', 'Gráfico indisponível — biblioteca bloqueada.');
        }
      });
    }
  });

  // Escuta evento para carregar Google Maps dinamicamente
  window.addEventListener('loadGoogleMapsRequested', function () {
    var mapContainer = document.getElementById('mapContainer');
    if (!mapContainer) return;
    var apiKey = mapContainer.getAttribute('data-maps-api-key') || '';
    if (!apiKey) {
      // informar que não há chave configurada
      mapContainer.innerHTML = '';
      var warn = document.createElement('div');
      warn.className = 'alert alert-warning';
      warn.textContent = 'Chave do Google Maps não configurada no servidor.';
      mapContainer.appendChild(warn);
      return;
    }

    // se já carregado, inicializar
    if (window.google && window.google.maps) {
      if (typeof window.initMap === 'function') window.initMap();
      return;
    }

    // Carregar script do Google Maps com callback
    var script = document.createElement('script');
    script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&callback=initMap';
    script.async = true;
    script.defer = true;
    script.onerror = function () {
      mapContainer.innerHTML = '';
      var err = document.createElement('div');
      err.className = 'alert alert-danger';
      err.textContent = 'Falha ao carregar o Google Maps. Verifique bloqueadores ou a chave da API.';
      mapContainer.appendChild(err);
    };
    document.head.appendChild(script);

    // Define initMap global
    window.initMap = function () {
      try {
        var center = { lat: -23.55052, lng: -46.633308 }; // default São Paulo
        var map = new google.maps.Map(mapContainer, { center: center, zoom: 12 });
      } catch (e) {
        mapContainer.innerHTML = '';
        var err = document.createElement('div');
        err.className = 'alert alert-danger';
        err.textContent = 'Erro ao inicializar o mapa.';
        mapContainer.appendChild(err);
      }
    };
  });
})();
