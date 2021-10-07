#!/usr/bin/env bash

[[ -n "$SERVICE_ID" ]] || (echo "Event service_id not set" && exit 1)
[[ -n "$IMAGE_ID" ]] || (echo "Event image_id not set" && exit 1)
