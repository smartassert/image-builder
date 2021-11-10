#!/usr/bin/env bash

BASE_COMMAND="docker-compose"

for FILE in $COMPOSE_FILES; do
  BASE_COMMAND="$BASE_COMMAND -f $FILE"
done

BASE_COMMAND="$BASE_COMMAND config"

DOCKER_COMPOSE_CONFIG_OUTPUT=$(eval "$BASE_COMMAND 2>&1")
HAS_VARIABLES_NOT_SET=$(grep "variable is not set" <<< "$DOCKER_COMPOSE_CONFIG_OUTPUT" | cat)

if [ "" != "$HAS_VARIABLES_NOT_SET" ]; then
  echo "$HAS_VARIABLES_NOT_SET"
  eval "$BASE_COMMAND --no-interpolate"
  exit 1
fi
