1. Download & install XAMPP 

https://www.apachefriends.org/
sudo ./xampp-linux-x64-8.0.1-1-installer.run

Checkboxes : Select Components : all
Checkbox : Launch XAMMP 
Manage servers -
Apache should already be running
start MySQL (MySQL actually is MariaDB)

Open browser at http://localhost/
should redirect to - 
http://localhost/dashboard/ - 'Welcome to XAMPP'

(default web server root : /opt/lampp/htdocs/)

2. Download & install Wordpress 

https://bitnami.com/stack/xampp#wordpress
sudo chmod +x bitnami-wordpress-5.6.1-0-module-linux-x64-installer.run
sudo ./bitnami-wordpress-5.6.1-0-module-linux-x64-installer.run

Choose folder : default of /opt/lampp should be the same as XAMPP

Admin account...

user: wordpress
password: password

Configure SMTP - skip

Installation type : Production settings

Deploy Wordpress to the cloud - skip!
Uncheck!

Launch - checked

Open:
http://localhost/wordpress/

should be 'Hello World!'

(WordPress web root is /opt/lampp/apps/wordpress/htdocs)

3. Install Composer (PHP dependency manager)

sudo apt install composer

(or see https://getcomposer.org/download/)


4. Install ARC2

https://github.com/semsol/arc2

mkdir arc2
cd arc2
composer require semsol/arc2:^2


(Ignored : Warning from https://repo.packagist.org: You are using an outdated version of Composer. Composer 2.0 is now available and you should upgrade. See https://getcomposer.org/2)

(5. Install MariaDB client

sudo apt install mariadb-client-core-10.3)

http://localhost/wordpress/wp-login.php

wordpress:password




