
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

source "digitalocean" "users_base" {
  api_token     = "${var.digitalocean_api_token}"
  image         = "ubuntu-20-04-x64"
  region        = "lon1"
  size          = "s-1vcpu-1gb"
  snapshot_name = "${var.snapshot_name}"
  ssh_username  = "root"
}

build {
  sources = ["source.digitalocean.users_base"]

  # Copy system files and provision for use
  provisioner "file" {
    destination = "~/docker-compose.yml"
    source      = "${path.root}/docker-compose.yml"
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

  provisioner "file" {
    destination = "~/post-create.sh"
    source      = "${path.root}/first-boot.sh"
  }

  provisioner "shell" {
    environment_vars = [
      "VERSION=${var.version}",
    ]
    scripts = ["${path.root}/../../provisioner/install_docker_compose.sh"]
  }

  provisioner "shell" {
    environment_vars = [
      "VERSION=${var.version}",
      "CADDY_DOMAIN=users.smartassert.com",
      "CADDY_TLS_INTERNAL="
    ]
    scripts = [
      "${path.root}/provision.sh",
      "${path.root}/../../provisioner/list-non-running-docker-compose-services.sh"
    ]
  }
}
