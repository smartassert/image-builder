#!/usr/bin/env bash

if [ $DURATION -lt $MAXIMUM ] ||  [ $DURATION = $MAXIMUM ]; then
  echo "true"
else
  echo "false"
fi
