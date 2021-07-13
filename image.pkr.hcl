
variable "digitalocean_api_token" {
  type      = string
  sensitive = true
  default = env("DIGITALOCEAN_API_TOKEN")
}

variable "snapshot_name" {
  type = string
  default = env("SNAPSHOT_NAME")
}

variable "worker_manager_version" {
  type = string
  default = env("WORKER_MANAGER_VERSION")
}

source "digitalocean" "worker_base" {
  api_token     = "${var.digitalocean_api_token}"
  image         = "ubuntu-20-04-x64"
  region        = "lon1"
  size          = "s-1vcpu-1gb"
  snapshot_name = "worker-manager-${var.snapshot_name}"
  ssh_username  = "root"
}

build {
  sources = ["source.digitalocean.worker_base"]

  # Copy system files and provision for use
  provisioner "file" {
    destination = "~/.env"
    source      = ".env"
  }

  provisioner "file" {
    destination = "~/app.env"
    source      = "app.env"
  }

  provisioner "file" {
    destination = "~/docker-compose.yml"
    source      = "docker-compose.yml"
  }

  provisioner "shell" {
    inline = ["mkdir -p ~/nginx"]
  }

  provisioner "file" {
    destination = "~/nginx/Dockerfile"
    source      = "nginx/Dockerfile"
  }

  provisioner "file" {
    destination = "~/nginx/site.conf"
    source      = "nginx/site.conf"
  }

  provisioner "shell" {
    environment_vars = [
      "DIGITALOCEAN_API_TOKEN=${var.digitalocean_api_token}",
      "WORKER_MANAGER_VERSION=${var.worker_manager_version}",
    ]
    scripts = ["./provision.sh"]
  }
}
