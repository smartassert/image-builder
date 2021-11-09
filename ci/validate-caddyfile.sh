#!/usr/bin/env bash

docker run \
  -e DOMAIN="example.com" \
  -v "$CADDYFILE_PATH" \
  caddy caddy validate --config /etc/caddy/Caddyfile
