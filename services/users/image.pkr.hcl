
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

  provisioner "shell" {
    inline = ["mkdir -p ~/jwt"]
  }

  provisioner "file" {
    destination = "~/jwt/"
    sources = [
      "${path.root}/jwt/private.pem",
      "${path.root}/jwt/public.pem"
    ]
  }

  provisioner "file" {
    destination = "~/"
    sources = [
      "${path.root}/docker-compose.yml",
      "${path.root}/first-boot.sh",
      "${path.root}/caddy.env"
    ]
  }

  provisioner "shell" {
    environment_vars = [
      "VERSION=${var.version}"
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
