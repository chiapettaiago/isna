# ISNA - Website Institucional

## Visão Geral

Portal institucional do Instituto Social Novo Amanhecer (ISNA) construído em PHP. O projeto utiliza um único front controller (`index.php`) que faz o roteamento das páginas públicas, aplica filtros de segurança e agora disponibiliza uma área restrita protegida por autenticação.

- Conteúdo público organizado em arquivos dentro de `pages/`, com cabeçalho e rodapé compartilhados em `templates/`.
- Recursos estáticos (CSS, JS, imagens, vídeos e PDFs) servidos diretamente pelo Apache.
- Sistema de login baseado em sessões PHP com senhas armazenadas via `password_hash`.
- Dashboard interno com gráfico diário de acessos gerado a partir do Apache (`logs/access_log`).
- Portal interno para redefinir senhas e cadastrar novos usuários com validação CSRF.
- Interface administrativa para criar seções e anexar itens à galeria pública.
- Blog institucional com publicação via painel e exibição na página inicial.
- Mensagens de feedback exibidas como *flash messages* na navegação.

## Tecnologias Principais

- PHP 8.1 ou superior
- Apache 2.4 com `mod_php` e `mod_rewrite`
- Bootstrap 5 e Bootstrap Icons para o layout

## Estrutura de Pastas

```
/ (raiz)
├─ index.php              # Roteador principal do site
├─ auth.php               # Helpers de autenticação, CSRF e mensagens
├─ gallery.php           # Utilitários da galeria dinâmica
├─ blog.php              # Utilitários do blog
├─ config.php             # Configurações de ambiente (força HTTPS, debug)
├─ config/
│  ├─ users.php           # Usuários e senhas (hash) para o login
│  ├─ gallery.php         # Configuração persistida da galeria
│  ├─ blog.php            # Posts publicados no blog
│  └─ .htaccess           # Bloqueia acesso direto ao diretório de configuração
├─ pages/                 # Conteúdo das rotas públicas e restritas
│  ├─ home.php
│  ├─ ...
│  ├─ gestao-galeria.php
│  ├─ gestao-blog.php
│  ├─ gestao-usuarios.php
│  ├─ login.php
│  └─ area-restrita.php
├─ templates/
│  ├─ header.php
│  └─ footer.php
├─ css/, js/, images/, docs/, videos/   # Arquivos estáticos
├─ Dockerfile            # Ambiente de container com Apache + PHP
└─ README.md             # Este documento
```

## Credenciais Iniciais e Gestão de Usuários

- Usuário padrão: `admin`
- Senha inicial: `senha123`

> **Importante:** altere a senha imediatamente em produção. A lista de usuários fica em `config/users.php`. Cada entrada utiliza o seguinte formato:

```php
return [
    'admin' => [
        'name' => 'Administrador',
        'password' => '$2y$10$82kayoDuAGkhRIjrvZCpaO1xnDIsmfYGwqvgDay9dbJV7o8GJWYzq',
        'roles' => ['admin'],
    ],
];
```

Para gerar um novo hash seguro execute no terminal:

```bash
php -r "echo password_hash('nova-senha', PASSWORD_DEFAULT), PHP_EOL;"
```

Substitua o valor retornado no arquivo `config/users.php`.

## Fluxo de Autenticação

- A página de login está disponível em `/login` e utiliza tokens CSRF gerados em `auth.php`.
- Ao tentar acessar `/area-restrita` sem sessão ativa o usuário é redirecionado para `/login` e, após autenticar, retorna automaticamente para a página solicitada.
- A página `/sobre` também exige sessão ativa, pois reúne orientações internas sobre o portal.
- O menu superior exibe o nome do usuário autenticado e fornece atalhos para a área restrita e para o logout (`/logout`).
- Mensagens de sucesso, erro ou aviso são mostradas logo abaixo da barra de navegação.

## Dashboard de Acessos

- O painel roda em `/area-restrita` e lê o arquivo `logs/access_log` para consolidar requisições GET com resposta abaixo de 500.
- Apenas URLs sem extensão (páginas) entram no cálculo para evitar ruído de arquivos estáticos.
- O filtro de datas usa o formato ISO (`YYYY-MM-DD`) e vem pré-configurado com os últimos 30 dias.
- O gráfico de linha é renderizado via CDN do Chart.js (nenhuma dependência local adicional é necessária).

## Gestão de Usuários via Interface

- A página interna `/gestao-usuarios` fica acessível após o login e reúne as ferramentas de credenciais.
- Qualquer pessoa autenticada pode redefinir a própria senha informando a senha atual e uma nova senha de pelo menos 8 caracteres.
- Apenas usuários com papel `admin` visualizam o formulário para cadastrar novos logins e escolher se o acesso terá privilégios administrativos.
- As alterações são gravadas novamente em `config/users.php`, portanto o arquivo precisa estar com permissão de escrita para o processo funcionar.
- Administradores conseguem consultar uma lista dos usuários ativos e seus perfis diretamente na mesma página.

## Gestão da Galeria

- Administradores acessam `/gestao-galeria` para criar novas seções (layout em grid) e definir a cor de fundo e descrição de cada bloco.
- O mesmo painel permite incluir itens individuais informando apenas o caminho/URL da imagem e, opcionalmente, o texto alternativo e a legenda.
- Seções automáticas baseadas em pastas (como a de Recital) continuam disponíveis, mas não recebem uploads pela interface; basta adicionar arquivos no diretório monitorado.
- As alterações ficam registradas em `config/gallery.php`, que precisa ter permissão de escrita pelo servidor web.

## Blog Institucional

- Os artigos são publicados em `/gestao-blog`, disponível somente para administradores após autenticação.
- Cada post recebe título, autor (opcional), resumo e corpo completo; o resumo alimenta os cards da página inicial.
- Os dados são persistidos em `config/blog.php`, portanto conceda permissão de escrita para o usuário do servidor web.
- A homepage lista automaticamente os três posts mais recentes e expande o conteúdo completo via colapso.

## Configuração no Apache

1. Copie o projeto para a pasta que será o `DocumentRoot` do site (ex.: `/var/www/isna`).
2. Garanta que o Apache tenha permissão de leitura no diretório e que o PHP consiga gravar os arquivos de sessão (pasta padrão `/var/lib/php/sessions`).
3. Ative os módulos necessários:

```bash
sudo a2enmod php8.1 rewrite
sudo systemctl restart apache2
```

4. Configure o VirtualHost apontando para o diretório do projeto:

```apache
<VirtualHost *:80>
    ServerName isna.local
    DocumentRoot /var/www/isna

    <Directory /var/www/isna>
        AllowOverride All
        Options FollowSymLinks
        Require all granted
    </Directory>

    DirectoryIndex index.php
    ErrorLog ${APACHE_LOG_DIR}/isna-error.log
    CustomLog ${APACHE_LOG_DIR}/isna-access.log combined
</VirtualHost>
```

5. Certifique-se de que o `.htaccess` padrão permite que todas as requisições que não forem arquivos físicos sejam enviadas para `index.php`. Um exemplo simples:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

6. Reinicie o serviço:

```bash
sudo systemctl reload apache2
```

> O diretório `config/` já contém um `.htaccess` com `Deny from all` para evitar que os arquivos de credenciais sejam baixados diretamente. Mantenha `AllowOverride All` habilitado para que essa regra seja aplicada.

## Ambiente de Desenvolvimento

Para testar rapidamente sem Apache você pode utilizar o servidor embutido do PHP:

```bash
php -S 127.0.0.1:8000 -t /caminho/para/o/projeto
```

Esse modo ignora o `.htaccess`, portanto os redirecionamentos são tratados pelo próprio `index.php` e o login funcionará normalmente.

## Testes Rápidos

```bash
php -l index.php
php -l auth.php
```

Execute os linters sempre que alterar arquivos PHP para garantir que não haja erros de sintaxe.

## Contato

- Desenvolvido por: Iago Chiapetta
- Site: https://chiapettadev.site

---

_Projeto confidencial. Todo uso e distribuição requer autorização prévia._
