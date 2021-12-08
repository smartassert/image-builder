#!/usr/bin/env bash

STOPPED_SERVICES="$(docker-compose ps --services --filter "status=stopped")"
PAUSED_SERVICES="$(docker-compose ps --services --filter "status=paused")"
RESTARTING_SERVICES="$(docker-compose ps --services --filter "status=restarting")"

NON_RUNNING_SERVICES=""
if [ "" != "$STOPPED_SERVICES" ]; then
  NON_RUNNING_SERVICES="$STOPPED_SERVICES"
fi

if [ "" != "$PAUSED_SERVICES" ]; then
  if [ "" != "$NON_RUNNING_SERVICES" ]; then
    NON_RUNNING_SERVICES="$NON_RUNNING_SERVICES
"
  fi

  NON_RUNNING_SERVICES="$NON_RUNNING_SERVICES$PAUSED_SERVICES"
fi

if [ "" != "$RESTARTING_SERVICES" ]; then
  if [ "" != "$NON_RUNNING_SERVICES" ]; then
    NON_RUNNING_SERVICES="$NON_RUNNING_SERVICES
"
  fi

  NON_RUNNING_SERVICES="$NON_RUNNING_SERVICES$RESTARTING_SERVICES"
fi

ALL_SERVICES=$(sort <<< "$(docker-compose ps --services)")
RUNNING_SERVICES=$(comm -13 <(sort <<<"$NON_RUNNING_SERVICES") <(sort <<<"$ALL_SERVICES"))

echo "All services:"
echo "$ALL_SERVICES"
echo "Running services:"
echo "$RUNNING_SERVICES"
echo "Stopped services:"
echo "$STOPPED_SERVICES"
echo "Paused services:"
echo "$PAUSED_SERVICES"
echo "Restarting services:"
echo "$RESTARTING_SERVICES"

if [ "$RUNNING_SERVICES" != "$ALL_SERVICES" ]; then
    docker-compose ps

    echo "All services:"
    echo "$ALL_SERVICES"
    echo "Running services:"
    echo "$RUNNING_SERVICES"
    echo "Stopped services:"
    echo "$STOPPED_SERVICES"
    echo "Paused services:"
    echo "$PAUSED_SERVICES"
    echo "Restarting services:"
    echo "$RESTARTING_SERVICES"

    docker-compose logs

    exit 1
fi
