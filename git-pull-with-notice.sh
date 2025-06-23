#!/bin/bash

# Este script deve ser colocado em .git/hooks/pre-merge-commit ou usado manualmente
# antes e depois de um git pull

# Diretório raiz do projeto (ajuste conforme necessário)
ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../.."
UPDATE_SCRIPT="$ROOT_DIR/update-status.sh"

# Verifique se o script de status existe
if [ ! -f "$UPDATE_SCRIPT" ]; then
    echo "Script de status não encontrado: $UPDATE_SCRIPT"
    exit 1
fi

# Inicia o processo de atualização
"$UPDATE_SCRIPT" start

# Executa o git pull
echo "Executando git pull..."
git pull

# Sinaliza o término
"$UPDATE_SCRIPT" finish

echo "Atualização concluída!"
