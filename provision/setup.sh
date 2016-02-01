#!/bin/bash -xi
# set up base box through vagrant file with these commands

# disable selinux ############################
#
echo disabling ipv4 firewall
echo -----------------------
sudo service iptables save
sudo service iptables stop
sudo chkconfig iptables off
echo checking that it is disabled
/sbin/iptables -L -v -n

echo disabling ipv6 firewall
echo -----------------------
sudo service ip6tables save
sudo service ip6tables stop
sudo chkconfig ip6tables off
echo checking that it is disabled
/sbin/ip6tables -L -v -n

# install EPEL and REMI Repos ##########################
#
echo installing epel-release and remi for CentOS/RHEL 6
echo --------------------------------------------------
sudo rpm -Uvh http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
sudo rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
rpm -Uvh http://dev.mysql.com/get/mysql-community-release-el6-5.noarch.rpm
# sed -i s'/enabled=1/enabled=0/' /etc/yum.repos.d/remi.repo
sudo cp /vagrant/provision/remi.repo /etc/yum.repos.d/

# Install Apache, PHP, and other tidbits ##############
#
echo installing apache, php, and other tidbits
sudo yum -y install vim zip unzip wget drush httpd php php-gd php-mcrypt php-curl
sudo chkconfig httpd on

# copy php.ini from provision folder to prepare for Drupal 7
# 'expose_php' and 'allow_url_fopen' will be set to 'Off'
sudo cp /vagrant/provision/php.ini /etc/

# Change 'AllowOverride None' to 'All' in httpd.conf 
sudo cp /vagrant/provision/httpd.conf /etc/httpd/conf/
sudo service httpd start

#echo update repositories
#echo -------------------
#sudo yum update

# Install MySQL ###############################
#
echo install mysql
echo -------------
wget http://repo.mysql.com/mysql-community-release-el6-5.noarch.rpm
# note:
# maybe better to wget the rpm's instead and put them in /vagrant/provision to install
# that way they will be available for a quicker install upon subsequent builds...
sudo rpm -Uvh mysql-community-release-el6-5.noarch.rpm
sudo yum -y install mysql mysql-server php-mysql php-soap php-mbstring
sudo rpm -qa | grep mysql
sudo chkconfig mysqld on
sudo service mysqld start
export DATABASE_PASS='raptor1!'
mysqladmin -u root password "$DATABASE_PASS"
mysql -u root -p"$DATABASE_PASS" -e "UPDATE mysql.user SET Password=PASSWORD('$DATABASE_PASS') WHERE User='root'"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1')"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.user WHERE User=''"
mysql -u root -p"$DATABASE_PASS" -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%'"
mysql -u root -p"$DATABASE_PASS" -e "FLUSH PRIVILEGES"

# set up database for drupal
mysql -u root -p"$DATABASE_PASS" -h localhost -e "create database drupal"
mysql -u root -p"$DATABASE_PASS" -h localhost drupal < /vagrant/provision/drupal.sql
mysql -u root -p"$DATABASE_PASS" -h localhost -e "create user drupaluser@localhost identified by 'drupal1!'"
mysql -u root -p"$DATABASE_PASS" -h localhost -e "GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE TEMPORARY TABLES,LOCK TABLES ON drupal.* TO drupaluser@localhost; flush privileges;"

# DRUPAL 7 #########################################################################
#

# Install Drush ######

# Download latest stable release using the code below or browse to github.com/drush-ops/drush/releases.
wget http://files.drush.org/drush.phar
# Or use our upcoming release: wget http://files.drush.org/drush-unstable.phar  

# Test your install.
php drush.phar core-status

# Rename to `drush` instead of `php drush.phar`. Destination can be anywhere on $PATH. 
sudo chmod +x drush.phar
sudo mv drush.phar /usr/local/bin/drush

# Enrich the bash startup file with completion and aliases.
drush init

# Get Drupal 7 ######

# wget http://ftp.drupal.org/files/projects/drupal-7.32.tar.gz
wget http://ftp.drupal.org/files/projects/drupal-7.41.tar.gz
tar xzvf drupal*
cd drupal*
sudo mkdir /var/www/html/RAPTOR
sudo rsync -avz . /var/www/html/RAPTOR/

sudo mkdir /var/www/html/RAPTOR/sites/default/files
sudo cp /vagrant/provision/settings.php /var/www/html/RAPTOR/sites/default/settings.php
sudo chmod 664 /var/www/html/RAPTOR/sites/default/settings.php
sudo chown -R apache:apache /var/www

# RAPTOR Application ###############################################################
#
# setup database for RAPTOR (raptor500)
mysql -u root -praptor1! -h localhost -e "create database raptor500"
mysql -u root -praptor1! -h localhost raptor500 < /vagrant/miscellaneous/raptor500.sql
mysql -u root -praptor1! -h localhost -e "create user raptoruser@localhost identified by 'raptor1!'"
mysql -u root -praptor1! -h localhost -e "GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE TEMPORARY TABLES,LOCK TABLES ON raptor500.* TO raptoruser@localhost; flush privileges;"

# copy RAPTOR modules and themes to drupal installation
sudo cp -R /vagrant/modules/* /var/www/html/RAPTOR/sites/all/modules/
sudo cp -R /vagrant/themes/* /var/www/html/RAPTOR/sites/all/themes/

# create tmp folder 
sudo mkdir /var/www/html/RAPTOR/sites/default/files/tmp

# copy Drupal as RSite500 and configure to use the raptor500 database
cd /var/www/html
sudo cp -R RAPTOR RSite500

# configure RSite500 to use raptor500 database
sudo cp /vagrant/provision/settings500.php /var/www/html/RSite500/sites/default/settings.php
sudo chmod 664 /var/www/html/RSite500/sites/default/settings.php

# I'm sure ownership is borked from all the sudo commands...
sudo chown -R apache:apache /var/www

# enable RAPTOR Modules
cd /var/www/html/RSite500/sites/all/modules/
drush -y en raptor_contraindications raptor_graph raptor_workflow raptor_datalayer raptor_imageviewing raptor_ewdvista raptor_mdwsvista simplerulesengine_core raptor_floatingdialog raptor_protocollib simplerulesengine_demo raptor_formulas raptor_reports simplerulesengine_ui raptor_glue raptor_scheduling

# enable and set raptor theme 
cd /var/www/html/RSite500/sites/all/themes/
drush -y -l http://localhost/RSite500/ pm-enable omega
drush -y -l http://localhost/RSite500/ pm-enable raptor_omega
# drush -y -l http://localhost/RSite500/ vset theme_default raptor_omega
# drush -y -l http://localhost/RSite500/ omega-export raptor_omega

# install Nodejs and Development Tools such as gcc & make
sudo yum -y groupinstall 'Development Tools'
sudo yum -y install nodejs npm
