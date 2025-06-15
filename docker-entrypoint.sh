#!/bin/bash
set -e

# Iniciar o PHP-FPM em background
php-fpm &

# Iniciar o Apache em foreground (para manter o contêiner rodando)
exec httpd -D FOREGROUND
