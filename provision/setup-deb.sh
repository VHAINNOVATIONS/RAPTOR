#!/bin/bash -xi
# set up base box through vagrant file with these commands

echo in setup-deb.sh for debian based os...
sudo apt-get update
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password raptor1!'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password raptor1!'
sudo apt-get -y install mysql-server
sudo apt-get install -y php5-gd php5-curl libssh2-php vim zip unzip wget curl
# sudo service apache2 stop
# cp /vagrant/provision/default-ssl /etc/apache2/sites-available/

# set up database
mysql -u root -praptor1! -h localhost -e "create database drupal"
# mysql -u root -praptor1! -h localhost drupal < /vagrant/provision/drupal.sql
mysql -u root -praptor1! -h localhost -e "create user drupaluser@localhost identified by 'drupal1!'"
mysql -u root -praptor1! -h localhost -e "GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE TEMPORARY TABLES,LOCK TABLES ON drupal.* TO drupaluser@localhost;"

# install nodejs for npm to install bower... yeah it's funny
# but all the cool kids use javascript
curl -sL https://deb.nodesource.com/setup | sudo bash -
sudo apt-get install -y nodejs
sudo npm install -g bower

# sudo mkdir /var/www/html
if ! [ -L /var/www ]; then
  rm -rf /var/www
  ln -fs /vagrant/difr /var/www
fi
# cp /vagrant/provision/test.pl /var/www/html/
# sudo chmod a+x /var/www/html/test.pl
sudo a2enmod ssl
sudo a2ensite default-ssl
sudo service apache2 start
curl -Gk https://localhost/test.pl --insecure
cd /vagrant/
perl provision/install-difr.pl
echo open your browser to https://localhost:8081/app.pl