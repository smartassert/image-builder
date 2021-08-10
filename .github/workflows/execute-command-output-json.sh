#!/usr/bin/env bash

JSON_OUTPUT=$($COMMAND)
EXIT_CODE=$?

echo $JSON_OUTPUT | jq "."
exit $EXIT_CODE
