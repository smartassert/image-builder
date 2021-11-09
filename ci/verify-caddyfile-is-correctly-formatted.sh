#!/usr/bin/env bash

ORIGINAL=$(< "$CADDYFILE_PATH")
FORMATTED=$(docker run -v "$CADDYFILE_PATH:/etc/caddy/Caddyfile" caddy caddy fmt /etc/caddy/Caddyfile)

[[ "$ORIGINAL" = "$FORMATTED" ]]
