#!/usr/bin/env bash

ORIGINAL=$(< "./services/$SERVICE_ID/caddy/Caddyfile")
FORMATTED=$(docker run -v "$PWD/services/$SERVICE_ID/caddy/Caddyfile:/etc/caddy/Caddyfile" caddy caddy fmt /etc/caddy/Caddyfile)

[[ "$ORIGINAL" = "$FORMATTED" ]]
