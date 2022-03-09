#!/usr/bin/env bash

GIT_REPOSITORY_STORE_DIRECTORY="/var/git_repository"

{
  echo "VERSION=$VERSION"
  echo "GIT_REPOSITORY_STORE_DIRECTORY=$GIT_REPOSITORY_STORE_DIRECTORY"
} > ./app.env

mkdir -p "$GIT_REPOSITORY_STORE_DIRECTORY"
chown -R www-data:www-data "$GIT_REPOSITORY_STORE_DIRECTORY"

docker-compose up -d
