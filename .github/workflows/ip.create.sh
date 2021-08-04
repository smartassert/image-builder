#!/usr/bin/env bash

set +e

cd app
JSON_OUTPUT=$(php app/bin/console app:ip:create)
EXIT_CODE=$?

echo $JSON_OUTPUT | jq "."
exit $EXIT_CODE
