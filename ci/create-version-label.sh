#!/usr/bin/env bash

if [[ -z "$RELEASE_TAG_NAME" ]]; then
  echo "master"
  exit 0
fi

echo "$RELEASE_TAG_NAME"
exit 0
