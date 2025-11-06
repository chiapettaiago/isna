# Geração Automática de Thumbnails de Vídeos

Este documento explica como gerar automaticamente thumbnails (imagens de preview) dos vídeos hospedados na API.

## 📋 Pré-requisitos

- **FFmpeg** instalado no sistema
- **PHP 7.4+** com suporte a `file_get_contents()` para URLs
- Conexão com a internet para baixar os vídeos

### Instalar FFmpeg

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install ffmpeg

# Verificar instalação
ffmpeg -version
```

## 🎬 Scripts Disponíveis

### 1. Script Interativo (`generate_video_thumbnails.php`)

Pergunta antes de sobrescrever thumbnails existentes.

**Uso:**
```bash
php generate_video_thumbnails.php
```

**Características:**
- Interativo - pergunta antes de sobrescrever
- Mostra progresso detalhado
- Útil para uso manual

### 2. Script Automático (`generate_video_thumbnails_auto.php`)

Executa sem interação, sobrescrevendo arquivos existentes.

**Uso:**
```bash
php generate_video_thumbnails_auto.php
```

**Características:**
- Não-interativo - sobrescreve automaticamente
- Ideal para automação (CI/CD, cronjobs, Docker)
- Código de saída: 0 (sucesso) ou 1 (erro)

## ⚙️ Configuração

### Vídeos Configurados

Atualmente, os scripts geram thumbnails para:

| Vídeo | Thumbnail Gerada | Tempo (seg) |
|-------|------------------|-------------|
| ISNA - Doações | `images/donation-thumbnail.jpg` | 2 |
| Outubro Rosa (H) | `videos/outubro_rosa_poster.jpg` | 2 |
| Outubro Rosa (V) | `videos/outubro_rosa_poster_vertical.jpg` | 2 |
| Realização 1 (H) | `images/realizacoes/realizacao-1-horizontal.jpg` | 2 |
| Realização 1 (V) | `images/realizacoes/realizacao-1-vertical.jpg` | 2 |

### Personalizar Configuração

Edite a array `$videos` em qualquer um dos scripts:

```php
$videos = [
    [
        'url' => 'https://api.chiapetta.dev/v/SEU_ID_AQUI',
        'name' => 'nome-do-video',
        'thumbnail' => __DIR__ . '/caminho/para/thumbnail.jpg',
        'time' => 2, // segundos do vídeo para capturar
    ],
];
```

### Parâmetros de Qualidade

Por padrão, as thumbnails são geradas com:
- **Largura:** 1280px (altura proporcional)
- **Qualidade:** Alta (q:v 2, escala de 1-31, sendo 2 = muito alta)
- **Formato:** JPEG

Para ajustar, modifique o comando FFmpeg na função `generateThumbnail()`:

```php
$cmd = sprintf(
    'ffmpeg -y -ss %d -i %s -vframes 1 -q:v 2 -vf "scale=1920:-1" %s 2>&1',
    //                                          ^^^^^ altere aqui
    $timeInSeconds,
    escapeshellarg($tempVideo),
    escapeshellarg($outputPath)
);
```

## 🐳 Integração com Docker

### Adicionar ao Dockerfile

```dockerfile
# Instalar FFmpeg
RUN apt-get update && apt-get install -y ffmpeg

# Copiar script
COPY generate_video_thumbnails_auto.php /var/www/html/isna/

# Gerar thumbnails durante o build (opcional)
RUN php /var/www/html/isna/generate_video_thumbnails_auto.php || true
```

### Executar no Container

```bash
# Durante o build
docker exec isna-container php /var/www/html/isna/generate_video_thumbnails_auto.php

# Como cronjob (diariamente às 3h)
echo "0 3 * * * php /var/www/html/isna/generate_video_thumbnails_auto.php" | crontab -
```

## 🔧 Solução de Problemas

### FFmpeg não encontrado

```bash
# Verificar instalação
which ffmpeg

# Se não estiver instalado
sudo apt-get install ffmpeg
```

### Permissões de diretório

```bash
# Dar permissão de escrita
chmod 755 images/ videos/
chmod 755 images/realizacoes/

# Verificar proprietário
ls -la images/ videos/
```

### Erro ao baixar vídeo

- Verifique conexão com internet
- Confirme que as URLs da API estão corretas
- Teste manualmente: `curl -I https://api.chiapetta.dev/v/4N4uRF1EkEjnpxgx`

### Memória insuficiente

Para vídeos grandes, pode ser necessário aumentar o limite de memória do PHP:

```bash
php -d memory_limit=512M generate_video_thumbnails_auto.php
```

## 📊 Exemplo de Saída

```
🎥 Gerando thumbnails automaticamente...

📹 ISNA - Doações... ✅ 156.32 KB
📹 outubro_rosa-horizontal... ✅ 189.45 KB
📹 outubro_rosa-vertical... ✅ 142.78 KB
📹 realizacao-1-horizontal... ✅ 201.12 KB
📹 realizacao-1-vertical... ✅ 98.34 KB

==================================================
✅ Sucesso: 5 | ❌ Falhas: 0
```

## 🔄 Automação

### Cronjob (Atualização Semanal)

```bash
# Editar crontab
crontab -e

# Adicionar linha (todo domingo às 2h)
0 2 * * 0 cd /home/iago/projects/isna && php generate_video_thumbnails_auto.php >> logs/thumbnails.log 2>&1
```

### Git Hook (Pre-commit)

Crie `.git/hooks/pre-commit`:

```bash
#!/bin/bash
cd /home/iago/projects/isna
php generate_video_thumbnails_auto.php
git add images/ videos/
```

## 📝 Notas

- Os vídeos são baixados temporariamente e deletados após gerar a thumbnail
- O frame é capturado aos 2 segundos do vídeo por padrão
- Diretórios são criados automaticamente se não existirem
- Thumbnails existentes são sobrescritas no modo automático

## 🆘 Suporte

Para problemas ou dúvidas:
1. Verifique os logs de erro do FFmpeg
2. Teste o comando FFmpeg manualmente
3. Confirme permissões de arquivo/diretório
4. Verifique espaço em disco disponível
