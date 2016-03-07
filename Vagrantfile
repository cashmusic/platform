#
# Basic CASH Music box
#
# 
# Environment settings in the cashmusic-dev-precise64.box:
# cashmusic_platform_settings='{"driver":"sqlite","hostname":"","username":"","password":"","database":"cashmusic_vagrant.sqlite","salt":"this is a very bad salt to choose","debug":"","apilocation":"http://localhost:8888/api/","instancetype":"multi","timezone":"US/Pacific","analytics":"basic","systememail":"CASH Music <dev@cashmusic.org>","smtp":0,"platforminitlocation":"/../framework/cashmusic.php"}'
# 
#
$box = 'precise64'
$box_url = 'http://files.vagrantup.com/precise64.box'
$ram = '256'

Vagrant.configure("2") do |config|
  config.vm.box = $box
  config.vm.box_url = $box_url
  config.vm.network :forwarded_port, guest: 80, host: 8888
  config.vm.network :forwarded_port, guest: 88, host: 8899
  config.vm.synced_folder '.', '/vagrant', 
    owner: 'vagrant', 
    group: 'www-data',
    mount_options: ["dmode=777,fmode=777"]

  config.vm.provider "virtualbox" do |v|
    v.memory = 256
  end

  config.vm.provision "shell", inline: <<-shell
    #!/bin/bash
    sudo apt-get upgrade
    sudo apt-get update
    sudo apt-get -y install curl apache2 php5 libapache2-mod-php5 php5-mcrypt php5-mysql php5-sqlite php5-curl php5-suhosin
    #
    # CHANGE APACHE ENVIRONMENT VARIABLES
    sudo cp -f /vagrant/.vagrant_settings/default /etc/apache2/sites-available/default
    sudo cp -f /vagrant/.vagrant_settings/envvars /etc/apache2/envvars
    #
    # ENABLE MOD REWRITE
    sudo a2enmod rewrite 
    #
    # RESTART APACHE
    sudo /etc/init.d/apache2 restart
    #
    # PERMISSIONS AND JUNK
    sudo chmod 0755 /var/log/apache2/error.log
    sudo chmod 0755 /var/log/apache2
    #
    # CASH MUSIC CHECK/INSTALL
    php /vagrant/.vagrant_settings/vagrant_cashmusic_installer.php
    # 
    # A LITTLE INFO NEVER HURT ANYONE
    echo "\nUSE YOUR BROWSER:\nProduction clone: http://localhost:8888/"
    echo "Developer tools: http://localhost:7777/"
  shell
end
