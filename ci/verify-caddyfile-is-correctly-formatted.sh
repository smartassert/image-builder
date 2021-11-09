#!/usr/bin/env bash

if [ ! -f "$CADDYFILE_PATH" ]; then
  echo "Caddyfile at path \"$CADDYFILE_PATH\" does not exist"
  exit 1
fi

ORIGINAL=$(< "$CADDYFILE_PATH")
FORMATTED=$(docker run -v "$CADDYFILE_PATH:/etc/caddy/Caddyfile" caddy caddy fmt /etc/caddy/Caddyfile)

[[ "$ORIGINAL" = "$FORMATTED" ]]
