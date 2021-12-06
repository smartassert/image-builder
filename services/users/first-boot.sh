#!/usr/bin/env bash

base64 -d <<< "$JWT_SECRET_KEY_BASE64_PART1$JWT_SECRET_KEY_BASE64_PART2$JWT_SECRET_KEY_BASE64_PART3" > /root/jwt/private.pem
base64 -d <<< "$JWT_PUBLIC_KEY_BASE64" > /root/jwt/public.pem

PUBLIC_IP=$(dig @resolver4.opendns.com myip.opendns.com +short)
sudo VERSION="$VERSION" CADDY_IP="$PUBLIC_IP" docker-compose up -d

sudo docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists
sudo docker-compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
