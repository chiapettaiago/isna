# Nginx

Use `nginx/isna.conf` como base do bloco `server` do site.

Pontos obrigatórios:

- `root` deve apontar para a pasta da aplicação, onde fica o `index.php`.
- a regra `try_files $uri $uri/ /index.php?route=$uri&$is_args$args;` precisa existir no `location /`.
- se o PHP-FPM não estiver acessível em `php:9000`, troque `fastcgi_pass php:9000;` pelo socket/endereço do seu servidor, por exemplo `fastcgi_pass unix:/run/php/php8.2-fpm.sock;`.

Depois de alterar o nginx:

```bash
nginx -t
systemctl reload nginx
```

Se o site estiver publicado em subdiretório, por exemplo `https://dominio.com/isna`, o fallback deve apontar para `/isna/index.php`:

```nginx
location /isna/ {
    try_files $uri $uri/ /isna/index.php?route=$uri&$is_args$args;
}
```
