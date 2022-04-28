#!/usr/bin/env bash

sed -i 's/IS_READY=0/IS_READY=1/g' ./app.env

{
  echo "DATABASE_URL=$DATABASE_URL"
  echo "DIGITALOCEAN_API_TOKEN=$DIGITALOCEAN_API_TOKEN"
  echo "MACHINE_NAME_PREFIX=prod"
  echo "DIGITALOCEAN_REGION=lon1"
  echo "DIGITALOCEAN_SIZE=s-1vcpu-1gb"
  echo "DIGITALOCEAN_TAG=worker"
  echo "CREATE_RETRY_LIMIT=3"
  echo "GET_RETRY_LIMIT=10"
  echo "MACHINE_IS_ACTIVE_DISPATCH_DELAY=10000"
  echo "DELETE_RETRY_LIMIT=10"
  echo "FIND_RETRY_LIMIT=3"
} >> ./app.env

sudo docker-compose exec -T app php bin/console messenger:setup-transports
