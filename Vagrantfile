host_port = ENV["HOST_PORT"] || 8080
digitalocean_access_token = ENV["DIGITALOCEAN_ACCESS_TOKEN"]
worker_image = ENV["WORKER_IMAGE"]
worker_manager_version = ENV["WORKER_MANAGER_VERSION"]

Vagrant.configure("2") do |config|
  config.vm.box = "focal-server-cloudimg-amd64-vagrant"
  config.vm.box_url = "https://cloud-images.ubuntu.com/focal/current/focal-server-cloudimg-amd64-vagrant.box"

  config.vm.network "forwarded_port", guest: 80, host: host_port

  # Copy system files and provision for use
  config.vm.provision "file", source: "./.env", destination: "~/.env"
  config.vm.provision "file", source: "./app.env", destination: "~/app.env"
  config.vm.provision "file", source: "./docker-compose.yml", destination: "~/docker-compose.yml"
  config.vm.provision "file", source: "./nginx/Dockerfile", destination: "~/nginx/Dockerfile"
  config.vm.provision "file", source: "./nginx/site.conf", destination: "~/nginx/site.conf"
  config.vm.provision "shell", path: "provision.sh", env: {
      "DIGITALOCEAN_ACCESS_TOKEN" => digitalocean_access_token,
      "WORKER_IMAGE" => worker_image,
      "WORKER_MANAGER_VERSION" => worker_manager_version
  }
end
