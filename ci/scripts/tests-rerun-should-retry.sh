#!/usr/bin/env bash

EXIT_CODE_DURATION_MISSING=3
EXIT_CODE_MAXIMUM_DURATION_MISSING=4

if [ -z "$DURATION" ]; then
  echo "false"
  exit
fi

if [ -z "$MAXIMUM_DURATION" ]; then
  echo "false"
  exit
fi

[ $DURATION -lt $MAXIMUM_DURATION ] || [ $DURATION = $MAXIMUM_DURATION ] && echo "true" || echo "false"
