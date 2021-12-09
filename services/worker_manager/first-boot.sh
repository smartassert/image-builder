#!/usr/bin/env bash

sudo docker-compose exec -T app php bin/console messenger:setup-transports
