#!/bin/bash -xi
# set up base box through vagrant file with these commands

echo in setup-deb.sh for debian based os...
sudo apt-get update

# DATABASE ########################################################################
#

# install mysql 
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password raptor1!'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password raptor1!'
sudo apt-get -y install mysql-server

# set up database for drupal 
mysql -u root -praptor1! -h localhost -e "create database drupal"
mysql -u root -praptor1! -h localhost drupal < /vagrant/provision/drupal.sql
mysql -u root -praptor1! -h localhost -e "create user drupaluser@localhost identified by 'drupal1!'"
mysql -u root -praptor1! -h localhost -e "GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE TEMPORARY TABLES,LOCK TABLES ON drupal.* TO drupaluser@localhost; flush privileges;"

# APACHE ###########################################################################
#

# install apache2 and php5 
sudo apt-get install -y apache2 libapache2-mod-php5 php5-mysql php5-gd php5-curl libssh2-php vim zip unzip wget curl

# copy php.ini from provision folder to prepare for Drupal 7
# 'expose_php' and 'allow_url_fopen' will be set to 'Off'
cp /vagrant/provision/php.ini /etc/php5/apache2/

# copy 000-default and default-ssh that has been updated to allow 'Overrides All'
# ~it also changes the webroot from /var/www to /var/www/html 
sudo cp /vagrant/provision/default /etc/apache2/sites-available/
cp /vagrant/provision/default-ssl /etc/apache2/sites-available/

sudo mkdir /var/www/html
sudo a2enmod ssl
sudo a2enmod rewrite
sudo a2ensite default-ssl
sudo service apache2 restart

## sudo mkdir /var/www/html
#if ! [ -L /var/www ]; then
#  rm -rf /var/www
#  ln -fs /vagrant/difr /var/www
#fi

# DRUPAL 7 #########################################################################
#

wget http://ftp.drupal.org/files/projects/drupal-7.41.tar.gz
tar xzvf drupal*
cd drupal*
sudo rsync -avz . /var/www/html

mkdir /var/www/html/sites/default/files
cp /vagrant/provision/settings.php /var/www/html/sites/default/settings.php
chmod 664 /var/www/html/sites/default/settings.php
sudo chown -R www-data:www-data /var/www

# EWD and EWD Federator ############################################################
# 

# install nodejs for npm to install bower and also for ewd to work
curl -sL https://deb.nodesource.com/setup | sudo bash -
sudo apt-get install -y nodejs
sudo npm -g install npm@latest
sudo npm -g install bower

# cp /vagrant/provision/test.pl /var/www/html/
# sudo chmod a+x /var/www/html/test.pl
#curl -Gk https://localhost/test.pl --insecure
#cd /vagrant/
#perl provision/install-difr.pl
#echo open your browser to https://localhost:8081/app.pl