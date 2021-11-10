#!/usr/bin/env bash

COMPOSE_FILES=$(ls ./docker-compose-config-source)
COMMAND="docker-compose"

for FILE in $COMPOSE_FILES; do
  COMMAND="$COMMAND -f ./docker-compose-config-source/$FILE"
done

COMMAND="$COMMAND config --no-interpolate > docker-compose.yml"

eval "$COMMAND"
