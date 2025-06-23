#!/bin/bash

# Adiciona um hook pré-merge ao git para mostrar o aviso de atualização

HOOKS_DIR="$(git rev-parse --git-dir)/hooks"
PRE_MERGE_HOOK="$HOOKS_DIR/pre-merge-commit"

# Verificar se diretório hooks existe
if [ ! -d "$HOOKS_DIR" ]; then
    mkdir -p "$HOOKS_DIR"
fi

# Criar o hook pre-merge-commit
cat > "$PRE_MERGE_HOOK" << 'EOF'
#!/bin/bash

# Diretório raiz do projeto
ROOT_DIR="$(git rev-parse --show-toplevel)"
UPDATE_SCRIPT="$ROOT_DIR/update-status.sh"

# Verificar se o script existe
if [ -f "$UPDATE_SCRIPT" ]; then
    # Iniciar o aviso de atualização
    "$UPDATE_SCRIPT" start
    echo "Aviso de atualização ativado"
fi

exit 0
EOF

# Criar o hook post-merge
POST_MERGE_HOOK="$HOOKS_DIR/post-merge"
cat > "$POST_MERGE_HOOK" << 'EOF'
#!/bin/bash

# Diretório raiz do projeto
ROOT_DIR="$(git rev-parse --show-toplevel)"
UPDATE_SCRIPT="$ROOT_DIR/update-status.sh"

# Verificar se o script existe
if [ -f "$UPDATE_SCRIPT" ]; then
    # Finalizar o aviso de atualização
    "$UPDATE_SCRIPT" update 90 "Aplicando alterações finais..."
    sleep 2
    "$UPDATE_SCRIPT" finish
    echo "Aviso de atualização desativado"
fi

exit 0
EOF

# Tornar os hooks executáveis
chmod +x "$PRE_MERGE_HOOK"
chmod +x "$POST_MERGE_HOOK"

echo "Hooks de git instalados com sucesso:"
echo "- $PRE_MERGE_HOOK"
echo "- $POST_MERGE_HOOK"
echo ""
echo "Agora, durante o git pull, o aviso de atualização será exibido automaticamente."
