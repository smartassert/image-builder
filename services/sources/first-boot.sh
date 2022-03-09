#!/usr/bin/env bash

{
  echo "DATABASE_URL=$DATABASE_URL"
  echo "AUTHENTICATION_BASE_URL=$AUTHENTICATION_BASE_URL"
  echo "REMOTE_STORAGE_ENDPOINT=$REMOTE_STORAGE_ENDPOINT"
  echo "REMOTE_STORAGE_KEY_ID=$REMOTE_STORAGE_KEY_ID"
  echo "REMOTE_STORAGE_SECRET=$REMOTE_STORAGE_SECRET"
  echo "REMOTE_STORAGE_FILE_SOURCE_BUCKET=$REMOTE_STORAGE_FILE_SOURCE_BUCKET"
  echo "REMOTE_STORAGE_RUN_SOURCE_BUCKET=$REMOTE_STORAGE_RUN_SOURCE_BUCKET"
} >> ./app.env

{
  echo "DOMAIN=${DOMAIN:-localhost}"
  echo "TLS_INTERNAL="
  echo "IP=$(dig @resolver4.opendns.com myip.opendns.com +short)"
} > ./caddy.env

docker-compose up -d
docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists
docker-compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction

echo "IS_READY=$IS_READY" >> ./app.env
