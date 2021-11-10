#!/usr/bin/env bash

sudo docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists
sudo docker-compose exec -T app php bin/console messenger:setup-transports
sudo docker-compose exec -T app php bin/console doctrine:schema:update --force
