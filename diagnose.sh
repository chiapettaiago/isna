#!/bin/bash

# Script para diagnosticar problemas com o container Docker ISNA

echo "=== DIAGNÓSTICO DO SITE ISNA ==="
echo ""
echo "Verificando se o Docker está em execução..."
if ! docker info > /dev/null 2>&1; then
  echo "❌ ERRO: Docker não está em execução. Por favor, inicie o serviço Docker."
  echo "   Comando: sudo systemctl start docker"
  exit 1
else
  echo "✅ Docker está em execução."
fi

echo ""
echo "Verificando containers em execução..."
CONTAINER_RUNNING=$(docker ps | grep isna-web | wc -l)

if [ "$CONTAINER_RUNNING" -eq "0" ]; then
  echo "❌ ERRO: O container isna-web não está em execução."
  
  # Verificar se o container existe mas está parado
  CONTAINER_EXISTS=$(docker ps -a | grep isna-web | wc -l)
  if [ "$CONTAINER_EXISTS" -gt "0" ]; then
    echo "   O container existe, mas está parado."
    echo "   Verificando logs de erro..."
    docker logs isna-web --tail 20
    
    echo ""
    echo "   Tentando reiniciar o container:"
    docker start isna-web
    sleep 5
    
    # Verificar se o reinício funcionou
    if docker ps | grep -q isna-web; then
      echo "✅ Container reiniciado com sucesso!"
    else
      echo "❌ Falha ao reiniciar o container."
      echo "   Tente recriar o container com: docker-compose up -d --force-recreate"
    fi
  else
    echo "   O container não existe. Tente criar com: docker-compose up -d"
  fi
else
  echo "✅ Container isna-web está em execução."
fi

echo ""
echo "Verificando portas abertas..."
if ! which netstat > /dev/null 2>&1; then
  echo "   Comando netstat não encontrado. Instalando..."
  if command -v apt-get > /dev/null; then
    sudo apt-get install -y net-tools
  elif command -v yum > /dev/null; then
    sudo yum install -y net-tools
  fi
fi

if netstat -tulpn | grep -q ":8080"; then
  echo "✅ Porta 8080 está aberta e em escuta."
else
  echo "❌ ERRO: Porta 8080 não está aberta!"
  echo "   Verificando se outra aplicação está usando a porta..."
  lsof -i :8080 || echo "   Nenhuma aplicação usando a porta 8080."
  echo "   Tente expor uma porta diferente no docker-compose.yml (ex: 8081:8080)"
fi

echo ""
echo "Verificando acesso ao PHP dentro do container..."
docker exec -it isna-web bash -c "php -v" > /dev/null 2>&1
if [ $? -eq 0 ]; then
  echo "✅ PHP está instalado e acessível dentro do container."
  echo "   Versão do PHP:"
  docker exec -it isna-web bash -c "php -v" | head -n 1
else
  echo "❌ ERRO: Não foi possível acessar o PHP dentro do container."
fi

echo ""
echo "Verificando processos dentro do container..."
echo "Processos Apache:"
docker exec isna-web ps aux | grep httpd
echo ""
echo "Processos PHP-FPM:"
docker exec isna-web ps aux | grep php-fpm

echo ""
echo "Verificando logs do Apache..."
docker exec isna-web tail -n 10 /var/log/httpd/error_log || echo "❌ Arquivo de log não encontrado"

echo ""
echo "Verificando status de saúde da aplicação..."
HEALTH=$(docker exec -it isna-web bash -c "curl -s http://localhost:8080/health.php")
if [ $? -eq 0 ]; then
  echo "✅ Aplicação está respondendo: $HEALTH"
else
  echo "❌ ERRO: Aplicação não está respondendo corretamente."
fi

echo ""
echo "=== RECOMENDAÇÕES ==="
echo "Se ainda houver problemas:"
echo "1. Recrie o container: docker-compose down && docker-compose up -d --build"
echo "2. Verifique o diagnóstico no navegador: http://seu-ip:8080/diagnostico.php"
echo "3. Verifique se o PHP-FPM está funcionando corretamente"
echo "4. Se o erro persistir, tente acessar: http://seu-ip:8080/phpinfo.php"

echo ""
echo "=== FIM DO DIAGNÓSTICO ==="
