#
# Basic CASH Music box
#
$box = 'precise64'
$box_url = 'http://files.vagrantup.com/precise64.box'
$ram = '256'

Vagrant.configure("2") do |config|
  config.vm.box = $box
  config.vm.box_url = $box_url
  config.vm.network :forwarded_port, guest: 80, host: 8888
  config.vm.synced_folder '.', '/vagrant', 
    owner: 'vagrant', 
    group: 'www-data',
    mount_options: ["dmode=777,fmode=777"]

  config.vm.provider "virtualbox" do |v|
    v.memory = 256
  end

  config.vm.provision "shell", inline: <<-shell
    #!/bin/bash
    sudo apt-get update
    sudo apt-get upgrade
    sudo apt-get -y install curl apache2 php5 libapache2-mod-php5 php5-mcrypt php5-mysql php5-sqlite php5-curl php5-suhosin
    #
    # SET SYSTEM ENVIRONMENT VARIABLES IF NEEDED (REQUIRES BASH)
    # sudo cp -f /vagrant/.vagrant_settings/environment /etc/environment
    # sudo source /etc/environment
    #
    # CHANGE APACHE SETTINGS AND APACHE ENVIRONMENT VARIABLES
    sudo cp -f /vagrant/.vagrant_settings/apache/default /etc/apache2/sites-available/default
    sudo cp -f /vagrant/.vagrant_settings/apache/envvars /etc/apache2/envvars
    #
    # ENABLE MOD REWRITE
    sudo a2enmod rewrite 
    #
    # MODIFY PHP.INI IF NEEDED
    # sudo cp -f /vagrant/.vagrant_settings/apache/php.ini /etc/php5/apache2/php.ini
    #
    # RESTART APACHE
    sudo /etc/init.d/apache2 restart
    #
    # CASH MUSIC CHECK/INSTALL
    php /vagrant/.vagrant_settings/vagrant_cashmusic_installer.php
  shell
end
