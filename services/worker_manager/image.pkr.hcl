
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
  provisioner "shell" {
    inline = ["mkdir -p ~/caddy"]
  }

  provisioner "file" {
    destination = "~/caddy/"
    sources = [
      "${path.root}/caddy/Caddyfile",
      "${path.root}/caddy/index.php"
    ]
  }

  provisioner "file" {
    destination = "~/"
    sources = [
      "${path.root}/docker-compose.yml",
      "${path.root}/app.env",
      "${path.root}/caddy.env",
      "${path.root}/first-boot.sh"
    ]
  }

  provisioner "shell" {
    environment_vars = [
      "DIGITALOCEAN_API_TOKEN=${var.digitalocean_api_token}",
      "VERSION=${var.version}",
      "CADDY_DOMAIN=localhost"
    ]
    scripts = [
      "${path.root}/../../provisioner/install_docker_compose.sh",
      "${path.root}/../../provisioner/validate-docker-compose-config.sh",
      "${path.root}/provision.sh",
      "${path.root}/../../provisioner/list-non-running-docker-compose-services.sh",
      "${path.root}/../../provisioner/verify-docker-compose-service-count.sh"
    ]
  }
}
