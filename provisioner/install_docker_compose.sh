#!/usr/bin/env bash

function run_command_until_successful () {
  until "$@"
  do
      echo -e "\033[1mRetrying $*\033[0m"
      sleep 1
  done
}

run_command_until_successful sudo apt-get update && sudo apt-get install -y \
  apt-transport-https \
  ca-certificates \
  curl \
  gnupg-agent \
  software-properties-common

# Install haveged https://github.com/jirka-h/haveged
# This increases the entropy of an otherwise-quiet headless VM where entropy generation is slow
# docker-compose operations often require entropy and a lack thereof will cause docker-compose to hang until there is sufficient entropy
sudo apt-cache show haveged
cat /etc/apt/sources.list
run_command_until_successful sudo apt-get install -y \
  haveged

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

sudo add-apt-repository \
   "deb [arch=amd64] https://download.docker.com/linux/ubuntu \
   $(lsb_release -cs) \
   stable"

run_command_until_successful sudo apt-get update
run_command_until_successful sudo apt-get install -y \
  docker-ce \
  docker-ce-cli \
  containerd.io

run_command_until_successful sudo apt-get autoremove -y
rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

sudo curl \
  -L "https://github.com/docker/compose/releases/download/1.27.4/docker-compose-$(uname -s)-$(uname -m)" \
  -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
