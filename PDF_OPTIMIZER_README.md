# Otimizador de PDFs para o ISNA

Este script processa todos os arquivos PDF na pasta `docs`, realizando as seguintes melhorias:
- Corrige a orientação dos documentos para garantir que o texto esteja na orientação correta
- Melhora a nitidez e o contraste dos documentos
- Aumenta a resolução para 300 DPI
- Salva os arquivos processados com o mesmo nome, substituindo os originais

## Requisitos

O script requer Python 3.7 ou superior e as seguintes bibliotecas:
- PyPDF2
- pdf2image
- Pillow

Além disso, você precisará instalar o Poppler para que a biblioteca `pdf2image` funcione:

### No Ubuntu/Debian:
```bash
sudo apt-get install poppler-utils
```

### No CentOS/RHEL:
```bash
sudo yum install poppler-utils
```

## Instalação

1. Instale as dependências Python:
```bash
pip install -r requirements.txt
```

2. Dê permissão de execução ao script:
```bash
chmod +x optimize_pdfs.py
```

## Uso

### Uso Básico

Simplesmente execute o script na pasta raiz do projeto:
```bash
./optimize_pdfs.py
```

### Opções Avançadas

O script oferece várias opções para personalizar o processamento:

```bash
# Desativar a rotação automática (processa apenas nitidez e resolução)
./optimize_pdfs.py --no-rotate

# Processar apenas arquivos específicos
./optimize_pdfs.py --files documento1.pdf documento2.pdf

# Especificar uma resolução diferente
./optimize_pdfs.py --dpi 400

# Combinando opções
./optimize_pdfs.py --no-rotate --files documento1.pdf --dpi 400
```

### Ajuda

Para ver todas as opções disponíveis:
```bash
./optimize_pdfs.py --help
```

O script irá:
1. Processar os PDFs na pasta `docs` (todos ou os especificados)
2. Corrigir a orientação apenas se necessário (a menos que --no-rotate seja especificado)
3. Criar um arquivo de log chamado `pdf_optimization.log`
4. Substituir os arquivos originais pelos otimizados (com o mesmo nome)

## Observações

- Certifique-se de ter backup dos seus documentos antes de executar este script
- O processo pode ser demorado para PDFs grandes ou com muitas páginas
- O log será salvo no arquivo `pdf_optimization.log` para referência
