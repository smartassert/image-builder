#!/usr/bin/env bash

COMPOSE_FILES=$(ls ./docker-compose-config-source/*.yml)

EXPECTED_SERVICES="${COMPOSE_FILES//".yml"/""}"
EXPECTED_SERVICES="${EXPECTED_SERVICES//"./docker-compose-config-source/"/""}"
EXPECTED_SERVICES="$(sort <<< "$EXPECTED_SERVICES")"

SERVICES="$(sort <<< "$(docker-compose ps --services 2>/dev/null)")"

if [ "$EXPECTED_SERVICES" != "$SERVICES" ]; then
  EXPECTED_SERVICE_COUNT=$(wc -l <<< "$EXPECTED_SERVICES")
  echo "Expected services ($EXPECTED_SERVICE_COUNT):"
  echo "$EXPECTED_SERVICES"

  ACTUAL_SERVICE_COUNT=$(wc -l <<< "$SERVICES")
  if [ "" = "$SERVICES" ]; then
    ACTUAL_SERVICE_COUNT="0"
  fi

  echo "Actual services ($ACTUAL_SERVICE_COUNT):"
  echo "$SERVICES"

  exit 1
fi
