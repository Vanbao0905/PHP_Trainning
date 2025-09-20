# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  # Hộp máy ảo (box) sử dụng Ubuntu 20.04 (Focal)
  config.vm.box         = "ubuntu/focal64"
  config.vm.box_version = "20240821.0.1"

  # Mạng private: chỉ host truy cập được, IP tĩnh
  config.vm.network "private_network", ip: "192.168.33.10"

  # Mạng public (bridged) để máy ảo xuất hiện như 1 thiết bị cùng mạng LAN
  config.vm.network "public_network", bridge: "Intel(R) Wi-Fi 6 AX"

  # Chia sẻ thư mục sources của host vào /vagrant trong guest
  config.vm.synced_folder "./sources", "/vagrant"

  # Cấu hình VirtualBox
  config.vm.provider "virtualbox" do |vb|
    vb.gui    = true        # Hiển thị GUI khi boot
    vb.memory = "4096"      # RAM 4GB
    vb.cpus   = 2           # 2 CPU
  end

  # Script provision: cài đặt các gói cần thiết
  config.vm.provision "shell", inline: <<-SHELL
    apt-get update -y
    apt-get install -y apache2
    apt-get install -y docker.io docker-compose
    usermod -aG docker vagrant
    apt-get install -y make git net-tools
  SHELL

  # Thời gian chờ boot (giây)
  config.vm.boot_timeout = 600
end
