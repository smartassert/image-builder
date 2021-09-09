#!/usr/bin/env bash

{ [ -n "$DURATION" ] && [ -n "$MAXIMUM_DURATION" ]; } &&
{ [ "$DURATION" -lt "$MAXIMUM_DURATION" ] || [ "$DURATION" = "$MAXIMUM_DURATION" ]; } &&
echo "true" || echo "false"
