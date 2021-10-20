#!/usr/bin/env bash

SERVICE_IDS=$(ls empty)

[[ -z "$SERVICE_IDS" ]] && echo "error" && exit 1

#[[ -n "$SERVICE_IDS" ]] || (
#  echo "service_id not set"
#  exit 1
#)


echo "$SERVICE_IDS"
#[[ -n "$SERVICE_ID" ]] || (echo "service_id not set" && exit 1)
