#!/usr/bin/env bash

#PAYLOAD='{"ENV1":"value1", "ENV2":"value2", "ENV3":"value3"}'

jq -r '. | to_entries | .[] | .key + "=" + (.value)' <<< "$PAYLOAD"
