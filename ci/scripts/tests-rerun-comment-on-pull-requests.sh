#!/usr/bin/env bash

if [ -z "$PULL_REQUESTS" ] || [ -z "$REPO" ] || [ -z "$COMMENT" ]; then
  exit 3
fi

PULL_REQUEST_NUMBERS=$(jq ".[].number" <<< "$PULL_REQUESTS" 2</dev/null)

for PULL_REQUEST_NUMBER in $PULL_REQUEST_NUMBERS; do
  gh pr comment "$PULL_REQUEST_NUMBER" --repo "$REPO" --body "$COMMENT"
done
