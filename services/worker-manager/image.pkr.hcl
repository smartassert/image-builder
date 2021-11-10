
variable "digitalocean_api_token" {
  type      = string
  sensitive = true
  default = env("DIGITALOCEAN_API_TOKEN")
}

variable "snapshot_name" {
  type = string
  default = env("SNAPSHOT_NAME")
}

variable "version" {
  type = string
}

source "digitalocean" "worker_base" {
  api_token     = "${var.digitalocean_api_token}"
  image         = "ubuntu-20-04-x64"
  region        = "lon1"
  size          = "s-1vcpu-1gb"
  snapshot_name = "${var.snapshot_name}"
  ssh_username  = "root"
}

build {
  sources = ["source.digitalocean.worker_base"]

  # Copy system files and provision for use
  provisioner "file" {
    destination = "~/.env"
    source      = "${path.root}/.env"
  }

  provisioner "file" {
    destination = "~/app.env"
    source      = "${path.root}/app.env"
  }

  provisioner "file" {
    destination = "~/docker-compose.yml"
    source      = "${path.root}/docker-compose.yml"
  }

  provisioner "file" {
    destination = "~/docker-compose-caddy.yml"
    source      = "${path.root}/../../docker-compose-common/caddy.yml"
  }

  provisioner "shell" {
    inline = ["mkdir -p ~/caddy"]
  }

  provisioner "file" {
    destination = "~/caddy/Caddyfile"
    source      = "${path.root}/../../caddy-common/Caddyfile"
  }

  provisioner "file" {
    destination = "~/caddy/index.php"
    source      = "${path.root}/../../caddy-common/index.php"
  }

  provisioner "shell" {
    environment_vars = [
      "COMPOSE_FILES=docker-compose.yml caddy.yml",
      "DIGITALOCEAN_API_TOKEN=${var.digitalocean_api_token}",
      "VERSION=${var.version}",
      "CADDY_DOMAIN=localhost"
    ]
    scripts = [
      "${path.root}/../../provisioner/install_docker_compose.sh",
      "${path.root}/../../provisioner/validate-docker-compose-config.sh",
      "${path.root}/provision.sh",
      "${path.root}/../../provisioner/list-non-running-docker-compose-services.sh"
    ]
  }
}
