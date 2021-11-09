#!/usr/bin/env bash

docker run \
  -e DOMAIN="example.com" \
  -v "$PWD/services/$SERVICE_ID/caddy/Caddyfile:/etc/caddy/Caddyfile" \
  caddy caddy validate --config /etc/caddy/Caddyfile
