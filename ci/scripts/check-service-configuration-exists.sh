#!/usr/bin/env bash

SERVICE_ID="$1"
CONFIGURATION_PATH="$2"

if [ "" = "$SERVICE_ID" ]; then
  echo "Argument 1 (service id) not given"
  exit 1
fi

if [ "" = "$CONFIGURATION_PATH" ]; then
  echo "Argument 2 (configuration path) not given"
  exit 2
fi

if [ ! -f "$CONFIGURATION_PATH" ]; then
  echo "Configuration for service $SERVICE_ID not found: $CONFIGURATION_PATH"
  exit 3
fi

echo "$(<"$CONFIGURATION_PATH")"
