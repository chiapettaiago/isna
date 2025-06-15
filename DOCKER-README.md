# Docker Setup - Site ISNA

Este diretório contém os arquivos necessários para executar o site ISNA em um container Docker com Apache e CentOS.

## Arquivos Docker

- `Dockerfile`: Configuração principal do container
- `docker-compose.yml`: Configuração para facilitar o gerenciamento
- `.dockerignore`: Lista de arquivos a serem ignorados no build

## Como usar

### Opção 1: Usando Docker Compose (Recomendado)

```bash
# Construir e iniciar o container
docker-compose up -d --build

# Parar o container
docker-compose down

# Ver logs
docker-compose logs -f
```

### Opção 2: Usando Docker diretamente

```bash
# Construir a imagem
docker build -t isna-website .

# Executar o container
docker run -d -p 8080:8080 --name isna-web isna-website

# Parar e remover o container
docker stop isna-web
docker rm isna-web
```

## Acesso

Após iniciar o container, o site estará disponível em:
- http://localhost:8080

## Estrutura do Container

- **Sistema Operacional**: Rocky Linux 9
- **Servidor Web**: Apache HTTP Server
- **PHP**: Versão disponível no repositório Rocky Linux 9
- **DocumentRoot**: `/var/www/html/isna`
- **Porta**: 8080

## Configurações

### Apache
- Mod_rewrite habilitado para URLs amigáveis
- DocumentRoot configurado para `/var/www/html/isna`
- Arquivo `.htaccess` criado automaticamente

### PHP
- Timezone: America/Sao_Paulo
- Upload max: 10MB
- Post max: 10MB
- Max execution time: 300 segundos

## Logs

Os logs do Apache podem ser visualizados com:
```bash
docker-compose logs -f isna-website
```

## Troubleshooting

### Container não inicia
```bash
# Verificar logs
docker-compose logs isna-website

# Verificar se a porta 8080 está em uso
netstat -tulnp | grep 8080
```

### Problemas de permissão
O Dockerfile já configura as permissões corretas (apache:apache), mas se houver problemas:
```bash
docker-compose exec isna-website chown -R apache:apache /var/www/html/isna
```

## Desenvolvimento

Para desenvolvimento, você pode montar o diretório local:
```yaml
# Adicionar ao docker-compose.yml em volumes:
- .:/var/www/html/isna
```

Isso permitirá editar arquivos localmente e ver as mudanças imediatamente.
