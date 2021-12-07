#!/usr/bin/env bash

echo "VERSION=$VERSION" >> /etc/environment

mkdir -p /var/log
chown -R www-data:www-data /var/log

sudo VERSION="$VERSION" docker-compose up -d
