#!/usr/bin/env bash

PUBLIC_IP=$(dig @resolver4.opendns.com myip.opendns.com +short)
sudo VERSION="$VERSION" CADDY_IP="$PUBLIC_IP" docker-compose up -d

sudo docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists
sudo docker-compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction
