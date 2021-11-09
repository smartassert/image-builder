#!/usr/bin/env bash

if [ ! -f "$CADDYFILE_PATH" ]; then
  echo "Caddyfile at path \"$CADDYFILE_PATH\" does not exist"
  exit 1
fi

docker run \
  -e DOMAIN="example.com" \
  -v "$CADDYFILE_PATH" \
  caddy caddy validate --config /etc/caddy/Caddyfile
