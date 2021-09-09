#!/usr/bin/env bash

if [ -z "$DURATION" ]; then
  echo "false"
  exit
fi

if [ -z "$MAXIMUM_DURATION" ]; then
  echo "false"
  exit
fi

[ $DURATION -lt $MAXIMUM_DURATION ] || [ $DURATION = $MAXIMUM_DURATION ] && echo "true" || echo "false"
