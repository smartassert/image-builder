#!/usr/bin/env bash

SET_OUTPUT_LINES=$(jq -r '. | to_entries | .[] | "::set-output name=" + .key + "::" + .value' 2>/dev/null <<< "$1")
[[ "$?" != "0" ]] && exit 1

readarray -t SET_OUTPUT_LINES_ARRAY <<< "$SET_OUTPUT_LINES"
for SET_OUTOUT_LINE in "${SET_OUTPUT_LINES_ARRAY[@]}"
do
   echo "$SET_OUTOUT_LINE"
done
