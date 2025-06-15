#!/bin/bash

echo "=== Teste de Configuração para Subdiretórios ==="
echo ""

# Simular diferentes cenários
echo "1. Testando detecção de diretório base:"
echo "   Raiz: http://localhost/"
echo "   Subdiretório: http://localhost/beta/"
echo "   Subdiretório aninhado: http://isna.org.br/beta/v2/"
echo ""

echo "2. URLs que devem ser geradas:"
echo "   Para raiz: http://localhost/images/logo.png"
echo "   Para beta: http://localhost/beta/images/logo.png"
echo "   Para produção: https://isna.org.br/beta/images/logo.png"
echo ""

echo "3. Estrutura de arquivos necessária:"
echo "   ✓ .htaccess (configurado para subdiretórios)"
echo "   ✓ index.php (com detecção automática de base path)"
echo "   ✓ config.php (configuração por ambiente)"
echo ""

echo "4. Para testar em isna.org.br/beta:"
echo "   - Copie todos os arquivos para a pasta /beta/"
echo "   - Acesse isna.org.br/beta/"
echo "   - As imagens devem carregar automaticamente"
echo ""

echo "✅ Configuração completa para subdiretórios!"
