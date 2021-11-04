#!/usr/bin/env bash

mkdir -p /var/log
chown -R www-data:www-data /var/log

sudo \
  VERSION="$VERSION" \
  DIGITALOCEAN_ACCESS_TOKEN="$DIGITALOCEAN_ACCESS_TOKEN" \
  docker-compose up -d

sudo docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists
sudo docker-compose exec -T app php bin/console messenger:setup-transports
sudo docker-compose exec -T app php bin/console doctrine:schema:update --force
