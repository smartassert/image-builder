#!/usr/bin/env bash

EXPECTED_SERVICES=$(docker-compose config --services --no-interpolate)
EXPECTED_SERVICES_EXIT_CODE="$?"

if [ "0" != "$EXPECTED_SERVICES_EXIT_CODE" ]; then
  echo "Unable to build list of expected docker-compose services"
  exit 1
fi

EXPECTED_SERVICES="$(sort <<< "$EXPECTED_SERVICES")"
EXPECTED_SERVICE_COUNT=$(wc -l <<< "$EXPECTED_SERVICES")

SERVICES="$(sort <<< "$(docker ps --format '{{.Names}}' --filter status=running)")"

echo "expected services (raw):"
docker-compose config --services --no-interpolate

echo "actual services (raw):"
docker ps --format '{{.Names}}' --filter status=running

echo "expected services:"
echo "$EXPECTED_SERVICES"
echo "actual services:"
echo "$SERVICES"

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

  exit 2
fi
