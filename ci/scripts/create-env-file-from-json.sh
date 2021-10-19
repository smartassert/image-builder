#!/usr/bin/env bash

jq -r '. | to_entries | .[] | .key + "=" + (.value)' <<< "$1"
