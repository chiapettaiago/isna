# Use Ubuntu LTS como base
FROM ubuntu:22.04

# Metadados do maintainer
LABEL maintainer="ISNA Website"
LABEL description="Dockerfile para site ISNA com Apache e PHP no Ubuntu"

# Evitar interações durante instalação
ENV DEBIAN_FRONTEND=noninteractive

# Atualizar o sistema e instalar dependências, incluindo ferramentas de imagem
RUN apt-get update && \
        apt-get install -y \
        apache2 \
        libapache2-mod-php8.1 \
        php8.1 \
        php8.1-mysql \
        php8.1-gd \
        php8.1-xml \
        php8.1-mbstring \
        php8.1-curl \
        php8.1-zip \
        imagemagick \
        libheif-examples \
        webp \
        unzip \
        wget \
        curl && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/*

# Configurar Apache para usar porta 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf && \
        sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf

# Configurar DocumentRoot do Apache
RUN sed -i 's|/var/www/html|/var/www/html/isna|g' /etc/apache2/sites-available/000-default.conf && \
        mkdir -p /var/www/html/isna

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Permitir .htaccess e URLs amigáveis
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Criar diretório para o site
RUN mkdir -p /var/www/html/isna

# Copiar arquivos do site para o container
COPY . /var/www/html/isna/

# Conversão automática de imagens HEIC/HEIF para JPG e WebP (idempotente)
# - Converte apenas se a pasta existir
# - Mantém os arquivos originais e cria .jpg e .webp correspondentes
RUN set -eux; \
        dir="/var/www/html/isna/images/recital"; \
        if [ -d "$dir" ]; then \
            find "$dir" -type f \( -iname '*.heic' -o -iname '*.heif' \) -print0 | \
            xargs -0 -I {} sh -c ' \
                f="$1"; \
                b="${f%.*}"; \
                jpg="${b}.jpg"; \
                webp="${b}.webp"; \
                if [ ! -f "$jpg" ]; then \
                    heif-convert "$f" "$jpg" >/dev/null 2>&1 || true; \
                    if [ -f "$jpg" ]; then mogrify -auto-orient "$jpg" || true; fi; \
                fi; \
                if [ -f "$jpg" ] && [ ! -f "$webp" ]; then \
                    cwebp -quiet -q 82 "$jpg" -o "$webp" || true; \
                fi \
            ' sh {}; \
        fi

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html/isna && \
        chmod -R 755 /var/www/html/isna && \
        find /var/www/html/isna -type f -name "*.php" -exec chmod 644 {} \;

# Criar arquivos de diagnóstico PHP
RUN echo '<?php phpinfo(); ?>' > /var/www/html/isna/phpinfo.php && \
        chown www-data:www-data /var/www/html/isna/phpinfo.php && \
        echo '<?php echo "Status OK - " . date("Y-m-d H:i:s"); ?>' > /var/www/html/isna/health.php && \
        chown www-data:www-data /var/www/html/isna/health.php && \
        echo '<?php' > /var/www/html/isna/diagnostico.php && \
        echo '$errors = [];' >> /var/www/html/isna/diagnostico.php && \
        echo 'if(function_exists("mysqli_connect")) { echo "<p style=""color:green"">✓ MySQLi disponível</p>"; } else { echo "<p style=""color:red"">✗ MySQLi não disponível</p>"; $errors[] = "mysqli"; }' >> /var/www/html/isna/diagnostico.php && \
        echo 'if(function_exists("gd_info")) { echo "<p style=""color:green"">✓ GD disponível</p>"; } else { echo "<p style=""color:red"">✗ GD não disponível</p>"; $errors[] = "gd"; }' >> /var/www/html/isna/diagnostico.php && \
        echo 'if(function_exists("simplexml_load_string")) { echo "<p style=""color:green"">✓ XML disponível</p>"; } else { echo "<p style=""color:red"">✗ XML não disponível</p>"; $errors[] = "xml"; }' >> /var/www/html/isna/diagnostico.php && \
        echo 'if(function_exists("mb_strlen")) { echo "<p style=""color:green"">✓ Multibyte String disponível</p>"; } else { echo "<p style=""color:red"">✗ Multibyte String não disponível</p>"; $errors[] = "mbstring"; }' >> /var/www/html/isna/diagnostico.php && \
        echo 'if(is_writable("/var/www/html/isna")) { echo "<p style=""color:green"">✓ Diretório raiz é gravável</p>"; } else { echo "<p style=""color:red"">✗ Diretório raiz não é gravável</p>"; $errors[] = "permissions"; }' >> /var/www/html/isna/diagnostico.php && \
        echo 'echo "<hr/><strong>Server Info:</strong> " . $_SERVER["SERVER_SOFTWARE"] . "<br/>";' >> /var/www/html/isna/diagnostico.php && \
        echo 'echo "<strong>PHP Version:</strong> " . phpversion();' >> /var/www/html/isna/diagnostico.php && \
        echo '?>' >> /var/www/html/isna/diagnostico.php && \
        chown www-data:www-data /var/www/html/isna/diagnostico.php

# Criar arquivo .htaccess para URLs amigáveis
RUN echo 'RewriteEngine On' > /var/www/html/isna/.htaccess && \
        echo 'RewriteCond %{REQUEST_FILENAME} !-f' >> /var/www/html/isna/.htaccess && \
        echo 'RewriteCond %{REQUEST_FILENAME} !-d' >> /var/www/html/isna/.htaccess && \
        echo 'RewriteRule ^([^/]+)/?$ index.php?page=$1 [QSA,L]' >> /var/www/html/isna/.htaccess

# Expor a porta configurada
EXPOSE 8080

# Definir o ponto de entrada para o contêiner
CMD ["apachectl", "-D", "FOREGROUND"]
