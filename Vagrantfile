# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  # Box Ubuntu 20.04
  config.vm.box         = "ubuntu/focal64"
  config.vm.box_version = "20240821.0.1"

  # Mạng private
  config.vm.network "private_network", ip: "192.168.33.10"

  # Chia sẻ thư mục sources vào /home/vagrant/sources
  config.vm.synced_folder "./sources", "/home/vagrant/sources"

  # VirtualBox config
  config.vm.provider "virtualbox" do |vb|
    vb.gui    = false      # Không cần GUI, tiết kiệm RAM/CPU
    vb.memory = "4096"
    vb.cpus   = 2
  end

  # Cài đặt Docker & Docker Compose mới
  config.vm.provision "shell", inline: <<-SHELL
    apt-get update -y
    apt-get install -y \
      apt-transport-https \
      ca-certificates \
      curl \
      software-properties-common \
      make git net-tools

    # Cài Docker CE
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | apt-key add -
    add-apt-repository \
       "deb [arch=amd64] https://download.docker.com/linux/ubuntu focal stable"
    apt-get update -y
    apt-get install -y docker-ce

    # Thêm user vagrant vào group docker
    usermod -aG docker vagrant

    # Cài Docker Compose bản mới
    curl -L "https://github.com/docker/compose/releases/download/2.29.7/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
  SHELL

  # Tăng thời gian chờ boot
  config.vm.boot_timeout = 600
end
