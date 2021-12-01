#!/usr/bin/env bash

mkdir -p /var/log
chown -R www-data:www-data /var/log

PUBLIC_IP=$(dig @resolver4.opendns.com myip.opendns.com +short)
sudo VERSION="$VERSION" CADDY_IP="$PUBLIC_IP" docker-compose up -d
