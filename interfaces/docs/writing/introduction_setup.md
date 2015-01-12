One of our goals is for this to run in as many places as possible, so we've worked 
hard to keep the requirements minimal:

 * PHP 5.4+
 * PDO (a default) and MySQL OR SQLite 
 * mod_rewrite (for admin app)
 * fopen wrappers OR cURL 

For local testing and development all you need to get started is 
[VirtualBox](https://www.virtualbox.org/wiki/Downloads), 
[Vagrant 1.4+](http://www.vagrantup.com/downloads.html), and this repo. Just fork, install
VirtualBox and Vagrant, then open a terminal window and in the repo directory type:

```
vagrant up
```  

Vagrant will fire up a VM, set up Apache, install the platform, and start serving a 
special dev website with tools, docs, and a live instance of the platform — all mapped 
right to **http://localhost:8888**.

![Dev site included in repo](https://b6febe3773eb5c5bc449-6d885a724441c07ff9b675222419a9d2.ssl.cf2.rackcdn.com/special/docs/dev_screenshot.jpg)

If you want to go beyond the basic setup included wth our vagrant scripts, you'll nbeed to
edit the **/framework/settings/cashmusic.ini.php** file. We include a template 
(cashmusic_template.ini.php) and the settings are pretty straightforward. You can change 
database settings, modify default system salt for password security, set timezone and
email settings, and switch between single or multi-user mode.