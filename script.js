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
