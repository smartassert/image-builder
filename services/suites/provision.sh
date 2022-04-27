#!/usr/bin/env bash

echo "VERSION=$VERSION" > ./.env

mkdir -p /var/log
chown -R www-data:www-data /var/log

docker-compose up -d
