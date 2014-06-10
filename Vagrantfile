#
# Basic CASH Music box
#
# 
# Environment settings in the cashmusic-dev-precise64.box:
# cashmusic_platform_settings='{"driver":"sqlite","hostname":"","username":"","password":"","database":"cashmusic_vagrant.sqlite","salt":"this is a very bad salt to choose","debug":"","apilocation":"http://localhost:8888/interfaces/php/api/","instancetype":"multi","timezone":"US/Pacific","analytics":"basic","systememail":"CASH Music <dev@cashmusic.org>","smtp":0,"platforminitlocation":"/framework/php/cashmusic.php"}'
# 
#
$box = 'cashmusic-dev-precise64'
$box_url = 'http://240db4afd17eae5f6498-0ff51d194a25bb350f7d8ba3de2dd7c4.r40.cf2.rackcdn.com/cashmusic-dev-precise64.box'
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
    echo "\nJust visit http://localhost:8888/ for a running install of the CASH Music platform."
  shell
end
