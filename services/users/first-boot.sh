#!/usr/bin/env bash

sed -i 's/IS_READY=0/IS_READY=1/g' ./app.env

{
  echo "DATABASE_URL=$DATABASE_URL"
  echo "JWT_PASSPHRASE=$JWT_PASSPHRASE"
  echo "PRIMARY_ADMIN_TOKEN=$PRIMARY_ADMIN_TOKEN"
  echo "SECONDARY_ADMIN_TOKEN=$SECONDARY_ADMIN_TOKEN"
} >> ./app.env

{
  echo "DOMAIN=${DOMAIN:-localhost}"
  echo "TLS_INTERNAL="
  echo "IP=$(dig @resolver4.opendns.com myip.opendns.com +short)"
} > ./caddy.env

base64 -d <<< "$JWT_SECRET_KEY_BASE64_PART1$JWT_SECRET_KEY_BASE64_PART2$JWT_SECRET_KEY_BASE64_PART3" > jwt/private.pem
base64 -d <<< "$JWT_PUBLIC_KEY_BASE64" > jwt/public.pem

docker-compose up -d
docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists
docker-compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
