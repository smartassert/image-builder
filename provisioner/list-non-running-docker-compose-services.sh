#!/usr/bin/env bash

RUNNING_SERVICES="$(docker-compose ps --services --filter "status=running")"
ALL_SERVICES="$(docker-compose ps --services)"
if [ "$RUNNING_SERVICES" != "$ALL_SERVICES" ]; then
    comm -13 <(sort <<<"$RUNNING_SERVICES") <(sort <<<"$ALL_SERVICES")
    exit 1
fi
