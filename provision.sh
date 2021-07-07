#!/usr/bin/env bash

sudo apt-get update && apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg-agent \
    software-properties-common

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

sudo add-apt-repository \
   "deb [arch=amd64] https://download.docker.com/linux/ubuntu \
   $(lsb_release -cs) \
   stable"

sudo apt-get update && apt-get install -y \
    docker-ce \
    docker-ce-cli \
    containerd.io

apt-get autoremove -y \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

DOCKER_COMPOSE_BIN=/usr/local/bin/docker-compose
if [ ! -f "$DOCKER_COMPOSE_BIN" ]; then
  sudo curl \
    -L "https://github.com/docker/compose/releases/download/1.27.4/docker-compose-$(uname -s)-$(uname -m)" \
    -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
fi

mkdir -p /var/log
chown -R www-data:www-data /var/log

sudo \
  WORKER_MANAGER_VERSION="$WORKER_MANAGER_VERSION" \
  DIGITALOCEAN_ACCESS_TOKEN="$DIGITALOCEAN_ACCESS_TOKEN" \
  WORKER_IMAGE="$WORKER_IMAGE" \
  docker-compose up -d
sudo docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists
sudo docker-compose exec -T app php bin/console messenger:setup-transports
sudo docker-compose exec -T app php bin/console doctrine:schema:update --force
