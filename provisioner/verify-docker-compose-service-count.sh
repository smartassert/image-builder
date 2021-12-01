#!/usr/bin/env bash

EXPECTED_SERVICES="$(sort <<< "$(docker-compose ps --services)")"
EXPECTED_SERVICE_COUNT=$(wc -l <<< "$EXPECTED_SERVICES")

SERVICES="$(sort <<< "$(docker-compose ps --services --filter status=running)")"

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
