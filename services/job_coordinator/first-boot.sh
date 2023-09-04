#!/usr/bin/env bash

sed -i 's/IS_READY=0/IS_READY=1/g' ./app.env

{
  echo "DATABASE_URL=$DATABASE_URL"
  echo "AUTHENTICATION_BASE_URL=$AUTHENTICATION_BASE_URL"
  echo "RESULTS_BASE_URL=$RESULTS_BASE_URL"
  echo "WORKER_MANAGER_BASE_URL=$WORKER_MANAGER_BASE_URL"
  echo "SOURCES_BASE_URL=$SOURCES_BASE_URL"
} >> ./app.env

{
  echo "DOMAIN=${DOMAIN:-localhost}"
  echo "TLS_INTERNAL="
  echo "IP=$(dig @resolver4.opendns.com myip.opendns.com +short)"
} > ./caddy.env

docker-compose up -d
docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists
docker-compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
