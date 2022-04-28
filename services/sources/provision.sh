#!/usr/bin/env bash

echo "VERSION=$VERSION" > ./.env

GIT_REPOSITORY_STORE_DIRECTORY="/var/git_repository"

{
  echo "GIT_REPOSITORY_STORE_DIRECTORY=$GIT_REPOSITORY_STORE_DIRECTORY"
  echo "IS_READY=0"
  echo "VERSION=$VERSION"
} >> ./app.env

mkdir -p "$GIT_REPOSITORY_STORE_DIRECTORY"
chown -R www-data:www-data "$GIT_REPOSITORY_STORE_DIRECTORY"

docker-compose up -d
