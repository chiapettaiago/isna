#!/bin/bash

# Script para gerenciar o status de atualização durante git pull
# Este script é usado para criar, atualizar e remover o arquivo de lock
# indicando que há uma atualização em andamento.

# Diretório raiz do projeto
ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
LOCK_FILE="$ROOT_DIR/.git-pull-in-progress"

# Função para criar ou atualizar o arquivo de lock
create_or_update_lock() {
    local progress=$1
    local message=$2
    local expires=$(($(date +%s) + 120)) # Expira em 2 minutos
    
    # Cria JSON com informações de status
    echo "{\"progress\":$progress,\"message\":\"$message\",\"expires\":$expires}" > $LOCK_FILE
    echo "Arquivo de lock criado/atualizado: $LOCK_FILE"
    echo "Progresso: $progress%, Mensagem: $message, Expira em: $(date -d @$expires '+%H:%M:%S')"
}

# Função para remover o arquivo de lock
remove_lock() {
    if [ -f $LOCK_FILE ]; then
        rm $LOCK_FILE
        echo "Arquivo de lock removido: $LOCK_FILE"
    else
        echo "Nenhum arquivo de lock encontrado."
    fi
}

# Verifica parâmetros
case "$1" in
    start)
        create_or_update_lock 10 "Iniciando atualização..."
        ;;
    update)
        progress=${2:-50}
        message=${3:-"Atualizando..."}
        create_or_update_lock $progress "$message"
        ;;
    finish)
        create_or_update_lock 100 "Atualização concluída!"
        sleep 3
        remove_lock
        ;;
    remove)
        remove_lock
        ;;
    *)
        echo "Uso: $0 {start|update|finish|remove} [progresso] [mensagem]"
        echo "  start                  - Inicia o processo de atualização (10%)"
        echo "  update [prog] [msg]    - Atualiza o progresso e mensagem"
        echo "  finish                 - Finaliza o processo (100% e remove após 3s)"
        echo "  remove                 - Remove o arquivo de lock imediatamente"
        echo ""
        echo "Exemplos:"
        echo "  $0 start"
        echo "  $0 update 30 \"Baixando arquivos...\""
        echo "  $0 update 70 \"Aplicando alterações...\""
        echo "  $0 finish"
        exit 1
esac

exit 0
