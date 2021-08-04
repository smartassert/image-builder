#!/usr/bin/env bash

JSON_OUTPUT=$(php app/bin/console app:ip:create)
EXIT_CODE=$?

echo $JSON_OUTPUT | jq "."
exit $EXIT_CODE