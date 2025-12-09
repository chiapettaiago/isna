#!/bin/bash

# Instalação de hooks desativada.
# Para evitar criação automática de arquivos de lock e avisos que possam bloquear o site,
# a instalação de hooks foi removida. Se quiser restaurar os hooks manualmente, crie-os
# com cuidado no diretório .git/hooks do repositório.

echo "install-update-hooks está desativado: não serão instalados hooks de atualização."
exit 0
