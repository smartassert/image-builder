#!/usr/bin/env bash

EXIT_CODE_COMMAND_ERROR=2
EXIT_CODE_JQ_ERROR=3

COMMAND_OUTPUT=$($COMMAND)
EXIT_CODE="$?"

if [ "0" != "$EXIT_CODE" ]; then
  echo "{
  \"error\": \"command failure\",
  \"command-exit-code\": $EXIT_CODE,
  \"command-output\": \"$COMMAND_OUTPUT\"
}"

  exit "$EXIT_CODE_COMMAND_ERROR"
fi

JQ_STD_ERR="/tmp/jq.log"
echo $COMMAND_OUTPUT | jq "." 2>"$JQ_STD_ERR"
JQ_EXIT_CODE="$?"
if [ "0" != "$JQ_EXIT_CODE" ]; then
  echo "{
  \"error\": \"jq failure\",
  \"jq-exit-code\": $JQ_EXIT_CODE,
  \"jq-output\": \"$(<"$JQ_STD_ERR")\"
}"

  exit "$EXIT_CODE_JQ_ERROR"
fi
