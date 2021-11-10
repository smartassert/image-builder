#!/usr/bin/env bash

DOCKER_COMPOSE_CONFIG_OUTPUT=$(docker-compose config 2>&1)
HAS_VARIABLES_NOT_SET=$(grep "variable is not set" <<< "$DOCKER_COMPOSE_CONFIG_OUTPUT" | cat)

if [ "" != "$HAS_VARIABLES_NOT_SET" ]; then
  echo "$HAS_VARIABLES_NOT_SET"
  docker-compose config --no-interpolate
  exit 1
fi
