#!/usr/bin/env bash

echo "VERSION=$VERSION" >> /etc/environment

mkdir -p /var/log
chown -R www-data:www-data /var/log

sudo \
  VERSION="$VERSION" \
  DIGITALOCEAN_ACCESS_TOKEN="$DIGITALOCEAN_ACCESS_TOKEN" \
  docker-compose up -d
